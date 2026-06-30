<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class CustomerGrowthChart extends ChartWidget
{
    protected static ?string $heading = 'Customer Growth Chart';

    protected function getData(): array
    {
        // 1. Get counts for each month (Jan to Dec)
        $data = [];
        for ($month = 1; $month <= 12; $month++) {
            $data[] = \App\Models\User::query()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', $month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Customers',
                    'data' => $data,
                    'fill' => 'start', // This gives it that nice shaded area under the line
                    'borderColor' => '#10b981', // Emerald Green
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
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

    protected static ?int $sort = 2;
}
