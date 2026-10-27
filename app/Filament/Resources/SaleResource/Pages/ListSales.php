<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Sale;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSales extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = SaleResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            SaleResource\Widgets\SalesStatsOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Time')
                ->badge(Sale::count()),

            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn(Builder $q) => $q->whereDate('created_at', today()))
                ->badge(Sale::whereDate('created_at', today())->count()),

            'week' => Tab::make('This Week')
                ->modifyQueryUsing(fn(Builder $q) => $q->whereBetween('created_at', [
                    now()->startOfWeek(), now()->endOfWeek()
                ]))
                ->badge(Sale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),

            'month' => Tab::make('This Month')
                ->modifyQueryUsing(fn(Builder $q) => $q
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year))
                ->badge(Sale::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count()),

            'year' => Tab::make('This Year')
                ->modifyQueryUsing(fn(Builder $q) => $q->whereYear('created_at', now()->year))
                ->badge(Sale::whereYear('created_at', now()->year)->count()),
        ];
    }
}
