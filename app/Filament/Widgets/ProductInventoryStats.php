<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductInventoryStats extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '15s';
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $totalStock  = Product::sum('stock');
        $lowStock    = Product::where('stock', '>', 0)->where('stock', '<=', 5)->count();
        $outOfStock  = Product::where('stock', '<=', 0)->count();
        $ordersToday = Order::whereDate('created_at', today())->count();
        $unitsSold   = Order::whereNotIn('status', ['cancelled'])
            ->sum('quantity');

        return [
            Stat::make('Bags In Stock', number_format($totalStock))
                ->description('Total units remaining across all products')
                ->icon('heroicon-o-archive-box')
                ->color('success'),

            Stat::make('Units Sold', number_format($unitsSold))
                ->description('All time, excluding cancelled orders')
                ->icon('heroicon-o-shopping-bag')
                ->color('info'),

            Stat::make('Low Stock', $lowStock)
                ->description($lowStock > 0 ? "{$lowStock} product(s) need restocking soon" : 'All products well stocked')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($lowStock > 0 ? 'warning' : 'success'),

            Stat::make('Out of Stock', $outOfStock)
                ->description($outOfStock > 0 ? "{$outOfStock} product(s) unavailable" : 'Nothing out of stock')
                ->icon('heroicon-o-x-circle')
                ->color($outOfStock > 0 ? 'danger' : 'success'),

            Stat::make('Orders Today', $ordersToday)
                ->description('New orders placed today')
                ->icon('heroicon-o-clock')
                ->color('gray'),
        ];
    }
}
