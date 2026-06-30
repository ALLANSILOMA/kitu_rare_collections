<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShopStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // --- 1. PREPARE THE CALCULATIONS ---

        // Revenue and Orders
        $totalRevenue = \App\Models\Order::sum('total_amount');
        $orderCount = \App\Models\Order::count();
        $averageSale = \App\Models\Order::avg('total_amount') ?? 0;


        // --- 2. RETURN EVERYTHING IN ONE ARRAY ---

        return [
            // Total Revenue Card
            Stat::make('Total Revenue', 'KES ' . number_format($totalRevenue, 2))
                ->description('Grand total of all sales')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color('success'),

            // Total Orders Card
            Stat::make('Total Orders', $orderCount)
                ->description('Number of bags sold')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            // Average Order Value Card
            Stat::make('Average Sale', 'KES ' . number_format($averageSale, 2))
                ->description('Average spent per customer')
                ->color('warning'),


        ];
    }
}
