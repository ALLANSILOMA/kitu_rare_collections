<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class TopProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Top Products by Revenue';

    protected function getData(): array
    {
        // 1. Fetch top 5 products based on the sum of their sales
        // This assumes you have a 'price' or 'total' in your orders/order_items
        $products = \App\Models\Product::withSum('orders', 'total_amount') // Sums the total_amount column from orders
        ->orderBy('orders_sum_total_amount', 'desc')
            ->take(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (KES)',
                    'data' => $products->pluck('orders_sum_total_amount')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe', '#dbeafe'
                    ],
                ],
            ],
            'labels' => $products->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // This is the magic line that flips the chart!
            'scales' => [
                'x' => [
                    'display' => true,
                ],
                'y' => [
                    'display' => true,
                ],
            ],
        ];
    }

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 4;
}
