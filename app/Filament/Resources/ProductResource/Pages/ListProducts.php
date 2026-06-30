<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Widgets\ProductInventoryStats;
use Filament\Actions; // This handles Actions\Action and Actions\CreateAction
use Filament\Forms;   // This handles Forms\Components
use Filament\Resources\Pages\ListRecords;
use App\Services\ProductImporter;
use Illuminate\Support\Facades\Storage;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // ── Download CSV Template ──
            // Note: Changed from Tables\Actions to Actions\Action
            Actions\Action::make('download_csv_template')
                ->label('Download CSV Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $rows = [
                        ['name', 'description', 'price', 'stock'],
                        ['Birkin Tote', 'Premium genuine leather tote bag', '4500', '20'],
                    ];
                    $headers = [
                        'Content-Type'        => 'text/csv',
                        'Content-Disposition' => 'attachment; filename="kiturarecollections-product-template.csv"',
                    ];
                    $callback = function () use ($rows) {
                        $handle = fopen('php://output', 'w');
                        foreach ($rows as $row) fputcsv($handle, $row);
                        fclose($handle);
                    };
                    return response()->stream($callback, 200, $headers);
                }),

            // ── Import Products ──
            // Note: Changed from Tables\Actions to Actions\Action
            Actions\Action::make('import_csv')
                ->label('Import Products')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('csv_file')
                        ->label('Select your filled CSV file')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                        ->maxSize(5120)->required()
                        ->disk('local')->directory('imports')->storeFiles(),
                    Forms\Components\Placeholder::make('import_note')
                        ->label('')
                        ->content('Required: name, price, stock. Delete the example row before uploading.'),
                ])
                ->action(function (array $data): void {
                    // Logic to process the CSV
                    $filePath = Storage::disk('local')->path($data['csv_file']);
                    $importer = new ProductImporter();
                    $importer->import($filePath);

                    @unlink($filePath);

                    $imported = $importer->getImportedCount();
                    $skipped  = $importer->getSkippedCount();
                    $errors   = $importer->getErrors();

                    if ($skipped === 0) {
                        \Filament\Notifications\Notification::make()
                            ->title("{$imported} product(s) imported successfully")
                            ->success()->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title("{$imported} imported, {$skipped} skipped")
                            ->body(collect($errors)->take(5)->join("\n"))
                            ->warning()->persistent()->send();
                    }
                })
                ->modalWidth('lg'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductResource\Widgets\ProductStats::class,
            ProductInventoryStats::class,
        ];
    }
}
