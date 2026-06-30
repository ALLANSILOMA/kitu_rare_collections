<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use Illuminate\Support\Facades\DB; // <--- This was missing!

class ProductStats extends BaseWidget
{
    // Force the cards to stay on one line (4 columns)
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Products', Product::count())
                ->icon('heroicon-m-shopping-bag'),

            Stat::make('Average Price', 'KES ' . number_format(Product::avg('price'), 2))
                ->icon('heroicon-m-presentation-chart-line'),

            Stat::make('Total Stock Value', 'KES ' . number_format(Product::sum(DB::raw('stock * price'))))
                ->description('Potential revenue')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
