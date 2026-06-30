<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductImporter
{
    protected array $errors  = [];
    protected int   $imported = 0;
    protected int   $skipped  = 0;

    public function import(string $filePath): void
    {
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            $this->errors[] = 'Could not open the uploaded file.';
            return;
        }

        // Row 1 is the header — read it to build a column map
        $headers = fgetcsv($handle);

        if (!$headers) {
            $this->errors[] = 'The file appears to be empty.';
            fclose($handle);
            return;
        }

        // Normalize headers: lowercase + trim so spacing/case doesn't break things
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip completely blank rows
            if (empty(array_filter($row))) continue;

            // Map headers to values: ['name' => 'Birkin', 'price' => '4500', ...]
            $data = array_combine($headers, array_pad($row, count($headers), ''));

            // Validate required fields
            if (empty(trim($data['name'] ?? ''))) {
                $this->errors[] = "Row {$rowNumber}: 'name' is required — row skipped.";
                $this->skipped++;
                continue;
            }

            if (!is_numeric(str_replace(',', '', $data['price'] ?? ''))) {
                $this->errors[] = "Row {$rowNumber}: 'price' must be a number — row skipped.";
                $this->skipped++;
                continue;
            }

            try {
                Product::create([
                    'name'        => trim($data['name']),
                    'description' => trim($data['description'] ?? ''),
                    'price'       => (float) str_replace(',', '', $data['price']),
                    'stock'       => (int)   ($data['stock'] ?? 0),
                ]);

                $this->imported++;

            } catch (\Throwable $e) {
                $this->errors[] = "Row {$rowNumber}: Failed to save — {$e->getMessage()}";
                $this->skipped++;
            }
        }

        fclose($handle);
    }



    // ── Results ───────────────────────────────────────────────────────────────

    public function getImportedCount(): int { return $this->imported; }
    public function getSkippedCount(): int  { return $this->skipped; }
    public function getErrors(): array      { return $this->errors; }
    public function hasErrors(): bool       { return !empty($this->errors); }
}
