<?php

namespace App\Services;

use App\Models\Product;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImporter
{
    protected array $errors   = [];
    protected int   $imported = 0;
    protected int   $skipped  = 0;

    public function import(string $filePath): void
    {
        $ext  = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $rows = $ext === 'csv'
            ? $this->readCsv($filePath)
            : $this->readExcel($filePath);

        if (empty($rows)) {
            $this->errors[] = 'File is empty or could not be read.';
            return;
        }

        $this->processRows($rows);
    }

    // ── File readers ──────────────────────────────────────────────────────────

    private function readCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            $this->errors[] = 'Could not open the CSV file.';
            return [];
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    private function readExcel(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (\Throwable $e) {
            $this->errors[] = 'Could not open Excel file: ' . $e->getMessage();
            return [];
        }

        // Try "Products" sheet first, fall back to first sheet
        $sheet = $spreadsheet->sheetNameExists('Products')
            ? $spreadsheet->getSheetByName('Products')
            : $spreadsheet->getActiveSheet();

        return $sheet->toArray(null, true, true, false);
    }

    // ── Row processing ────────────────────────────────────────────────────────

    private function processRows(array $rows): void
    {
        // ── Find the header row ────────────────────────────────────────────────
        // Scan until we find a row containing 'name' — handles title rows above headers
        $headerIndex = null;
        $headers     = [];

        foreach ($rows as $i => $row) {
            $normalized = array_map(fn($v) => strtolower(trim((string) $v)), $row);
            if (in_array('name', $normalized, true)) {
                $headerIndex = $i;
                $headers     = $normalized;
                break;
            }
        }

        if ($headerIndex === null) {
            $this->errors[] = 'Could not find a header row containing "name". Check your file format.';
            return;
        }

        // ── Normalise column names ────────────────────────────────────────────
        // Map both the old CSV column names AND the Excel template column names
        // so either file format works.
        $colMap = [
            'name'                 => 'name',
            'description'         => 'description',
            'price'               => 'price',
            'buying_price'        => 'buying_price',
            'supplier'            => 'supplier',
            'stock'               => 'stock',
            // Excel template names:
            'variation_type'      => 'variation_type',
            'variation_options'   => 'variation_options',
            'variation_hex'       => 'variation_hex',
            // CSV column names (mapped to same keys):
            'variations'          => 'variation_type',
            'variation description' => 'variation_options',
            'variation_description' => 'variation_options',
        ];

        $headerMap = []; // position → normalised key
        foreach ($headers as $pos => $raw) {
            $key = $colMap[$raw] ?? $raw;
            $headerMap[$pos] = $key;
        }

        // ── Build associative rows ─────────────────────────────────────────────
        $dataRows = array_slice($rows, $headerIndex + 1);

        $assocRows = [];
        foreach ($dataRows as $row) {
            $row = array_pad(array_values($row), count($headerMap), '');
            $assoc = [];
            foreach ($headerMap as $pos => $key) {
                $assoc[$key] = trim((string) ($row[$pos] ?? ''));
            }
            $assocRows[] = $assoc;
        }

        // ── Group rows into products ───────────────────────────────────────────
        // A row with a non-empty 'name' starts a new product.
        // Subsequent rows with an empty 'name' are additional variation options
        // for the most recently seen product.
        $products = [];
        $current  = null;

        foreach ($assocRows as $assoc) {
            // Skip rows that are entirely blank
            if (empty(array_filter($assoc))) continue;

            // Skip template artefact rows like [name], [price], NOTES: …
            $nameVal = $assoc['name'] ?? '';
            if (str_starts_with($nameVal, '[')) continue;
            if (stripos($nameVal, 'note') === 0 && str_word_count($nameVal) > 6) continue;

            if ($nameVal !== '') {
                // Save the previous product before starting a new one
                if ($current !== null) {
                    $products[] = $current;
                }
                $current = $assoc;
                // Initialise the variations list with this row's variation (if any)
                $current['_variation_rows'] = [];
                if (!empty($assoc['variation_type']) || !empty($assoc['variation_options'])) {
                    $current['_variation_rows'][] = [
                        'type'  => $assoc['variation_type'],
                        'label' => $assoc['variation_options'],
                        'hex'   => $assoc['variation_hex'] ?? '',
                    ];
                }
            } else {
                // Continuation row — add variation to current product
                if ($current !== null && (
                        !empty($assoc['variation_type']) || !empty($assoc['variation_options'])
                    )) {
                    $current['_variation_rows'][] = [
                        'type'  => $assoc['variation_type'],
                        'label' => $assoc['variation_options'],
                        'hex'   => $assoc['variation_hex'] ?? '',
                    ];
                }
            }
        }

        // Push the last product
        if ($current !== null) {
            $products[] = $current;
        }

        // ── Import each grouped product ────────────────────────────────────────
        foreach ($products as $index => $data) {
            $rowLabel = "Product \"" . ($data['name'] ?? "row " . ($index + 1)) . "\"";

            // Required: name
            if (empty(trim($data['name'] ?? ''))) {
                $this->errors[] = "{$rowLabel}: 'name' is required — skipped.";
                $this->skipped++;
                continue;
            }

            // Required: price
            $price = str_replace(',', '', $data['price'] ?? '');
            if (!is_numeric($price)) {
                $this->errors[] = "{$rowLabel}: 'price' must be a number — skipped.";
                $this->skipped++;
                continue;
            }

            // Buying price — default 0, warn but don't skip
            $buyingPrice = str_replace(',', '', $data['buying_price'] ?? '');
            if (!is_numeric($buyingPrice)) {
                $this->errors[] = "{$rowLabel}: 'buying_price' missing or non-numeric — defaulted to 0.";
                $buyingPrice = 0;
            }

            // Build variations JSON from the collected variation rows
            $variations = $this->buildVariations($data['_variation_rows'] ?? []);

            try {
                Product::create([
                    'name'         => trim($data['name']),
                    'description'  => trim($data['description'] ?? ''),
                    'price'        => (float) $price,
                    'buying_price' => (float) $buyingPrice,
                    'supplier'     => trim($data['supplier'] ?? '') ?: null,
                    'stock'        => (int) ($data['stock'] ?? 0),
                    'variations'   => !empty($variations) ? $variations : null,
                    'images'       => [],
                ]);

                $this->imported++;

            } catch (\Throwable $e) {
                $this->errors[] = "{$rowLabel}: Failed to save — {$e->getMessage()}";
                $this->skipped++;
            }
        }
    }

    // ── Variation builder ─────────────────────────────────────────────────────
    //
    // Converts an array of per-row variation data into the nested JSON the
    // product show blade and Filament repeater expect:
    //
    // [
    //   {
    //     "type": "Color",
    //     "options": [
    //       { "label": "Brown",     "hex": "",        "image": "" },
    //       { "label": "Chocolate", "hex": "",        "image": "" }
    //     ]
    //   }
    // ]
    //
    // Multiple types (Color + Size) on one product are grouped correctly.

    private function buildVariations(array $variationRows): array
    {
        if (empty($variationRows)) return [];

        // Group by type (Color, Size, Material …)
        $grouped = [];
        foreach ($variationRows as $row) {
            $type  = ucfirst(strtolower(trim($row['type']  ?? 'Other')));
            $label = trim($row['label'] ?? '');
            $hex   = trim($row['hex']   ?? '');

            if (empty($label)) continue;

            // If the label itself is comma-separated (Excel template style), expand it
            if (str_contains($label, ',')) {
                $labels = array_map('trim', explode(',', $label));
                $hexes  = array_map('trim', explode(',', $hex));
                foreach ($labels as $i => $lbl) {
                    if (empty($lbl)) continue;
                    $h = $hexes[$i] ?? '';
                    if (!empty($h) && !preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $h)) {
                        $h = '';
                    }
                    $grouped[$type][] = ['label' => $lbl, 'hex' => $h, 'image' => ''];
                }
            } else {
                // Single label per row (CSV style)
                if (!empty($hex) && !preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $hex)) {
                    $hex = '';
                }
                $grouped[$type][] = ['label' => $label, 'hex' => $hex, 'image' => ''];
            }
        }

        // Reshape into the expected array structure
        $result = [];
        foreach ($grouped as $type => $options) {
            $result[] = ['type' => $type, 'options' => $options];
        }

        return $result;
    }

    // ── Results ────────────────────────────────────────────────────────────────

    public function getImportedCount(): int { return $this->imported; }
    public function getSkippedCount(): int  { return $this->skipped; }
    public function getErrors(): array      { return $this->errors; }
    public function hasErrors(): bool       { return !empty($this->errors); }
}
