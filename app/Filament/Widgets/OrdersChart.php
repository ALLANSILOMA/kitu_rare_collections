<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Orders Year-over-Year Chart';

    protected function getData(): array
    {
        $now = now();
        $thisYear = [];
        $lastYear = [];

        for ($month = 1; $month <= 12; $month++) {
            // Count orders for THIS year
            $thisYear[] = \App\Models\Order::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $month)
                ->count();

            // Count orders for LAST year
            $lastYear[] = \App\Models\Order::whereYear('created_at', $now->year - 1)
                ->whereMonth('created_at', $month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'This Year',
                    'data' => $thisYear,
                    'borderColor' => '#3b82f6', // Blue
                ],
                [
                    'label' => 'Last Year',
                    'data' => $lastYear,
                    'borderColor' => '#94a3b8', // Gray (dashed look)
                    'borderDash' => [5, 5],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 3;
}
