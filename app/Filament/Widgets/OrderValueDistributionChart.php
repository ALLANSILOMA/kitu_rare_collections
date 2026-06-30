<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class OrderValueDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Order Value Distribution';

    protected function getData(): array
    {
        // 1. Define your price brackets
        $ranges = [
            '0 - 2k' => [0, 2000],
            '2k - 5k' => [2001, 5000],
            '5k - 10k' => [5001, 10000],
            '10k - 20k' => [10001, 20000],
            '20k+' => [20001, 1000000], // Adjust these numbers to fit KituRare prices
        ];

        $counts = [];

        // 2. Loop through each range and count the orders
        foreach ($ranges as $label => $limits) {
            $counts[] = \App\Models\Order::whereBetween('total_amount', [$limits[0], $limits[1]])->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Orders',
                    'data' => $counts,
                    'backgroundColor' => '#f59e0b', // Amber/Gold color
                ],
            ],
            'labels' => array_keys($ranges),
        ];
    }
    protected function getType(): string
    {
        return 'bar';
    }

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 5;
}
