<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;

class ProductStats extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $lowStockCount    = Product::where('stock', '<=', 5)->where('stock', '>', 0)->count();
        $outOfStockCount  = Product::where('stock', 0)->count();

        // Total retail value of all stock on hand (selling price × qty in stock)
        $inventoryValue   = Product::selectRaw('SUM(price * stock) as total')
            ->value('total') ?? 0;

        // Total cost value of stock on hand (buying price × qty) — what you've invested
        $inventoryCost    = Product::selectRaw('SUM(buying_price * stock) as total')
            ->value('total') ?? 0;

        $lowStockDescription = $outOfStockCount > 0
            ? "{$outOfStockCount} out of stock · {$lowStockCount} critically low"
            : ($lowStockCount > 0 ? "{$lowStockCount} items running low (≤5 units)" : 'All items well stocked');

        return [
            Stat::make('Total Products', Product::count())
                ->description(Product::where('stock', '>', 0)->count() . ' currently in stock')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary')
                ->icon('heroicon-m-shopping-bag'),

            Stat::make('Average Selling Price', 'KES ' . number_format(Product::avg('price') ?? 0, 0))
                ->description('Avg cost: KES ' . number_format(Product::avg('buying_price') ?? 0, 0))
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color('info')
                ->icon('heroicon-m-presentation-chart-line'),

            Stat::make('Low / Out of Stock', $lowStockCount + $outOfStockCount)
                ->description($lowStockDescription)
                ->descriptionIcon($outOfStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($outOfStockCount > 0 ? 'danger' : ($lowStockCount > 0 ? 'warning' : 'success'))
                ->icon('heroicon-m-archive-box'),

            Stat::make('Total Inventory Value', 'KES ' . number_format($inventoryValue, 0))
                ->description('At cost: KES ' . number_format($inventoryCost, 0))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->icon('heroicon-m-banknotes'),
        ];
    }
}
