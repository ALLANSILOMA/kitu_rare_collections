<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class SalesStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $now   = now();
        $month = Sale::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
        $week  = Sale::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
        $today = Sale::whereDate('created_at', today());

        // Month-on-month profit comparison
        $thisMonthProfit  = (clone $month)->sum('profit');
        $lastMonthProfit  = Sale::whereMonth('created_at', $now->copy()->subMonth()->month)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->sum('profit');
        $profitTrend = $lastMonthProfit > 0
            ? round((($thisMonthProfit - $lastMonthProfit) / $lastMonthProfit) * 100, 1)
            : 0;

        $totalRevenue      = Sale::sum('total_revenue');
        $totalCost         = Sale::sum('total_cost');
        $totalProfit       = Sale::sum('profit');
        $monthRevenue      = (clone $month)->sum('total_revenue');
        $monthProfit       = $thisMonthProfit;
        $weekRevenue       = (clone $week)->sum('total_revenue');
        $weekProfit        = (clone $week)->sum('profit');
        $todayRevenue      = (clone $today)->sum('total_revenue');
        $todayProfit       = (clone $today)->sum('profit');
        $totalOrders       = Sale::count();
        $monthOrders       = (clone $month)->count();
        $avgOrderValue     = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $margin            = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0;

        return [
            // ── Row 1: Snapshot ──────────────────────────────────────────────
            Stat::make('Total Revenue (All Time)', 'KES ' . number_format($totalRevenue))
                ->description('Gross sales across all verified orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Net Profit (All Time)', 'KES ' . number_format($totalProfit))
                ->description('Revenue minus buying cost · ' . $margin . '% margin')
                ->descriptionIcon($totalProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($totalProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Total Cost (All Time)', 'KES ' . number_format($totalCost))
                ->description('Sum of buying prices across all sales')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            // ── Row 2: This Month ─────────────────────────────────────────────
            Stat::make('This Month — Revenue', 'KES ' . number_format($monthRevenue))
                ->description($monthOrders . ' orders this month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('This Month — Profit', 'KES ' . number_format($monthProfit))
                ->description(
                    ($profitTrend >= 0 ? '↑ ' : '↓ ') . abs($profitTrend) . '% vs last month'
                )
                ->descriptionIcon($profitTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Avg Order Value', 'KES ' . number_format($avgOrderValue))
                ->description('Across ' . $totalOrders . ' total orders')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('gray'),

            // ── Row 3: Short-window ───────────────────────────────────────────
            Stat::make('This Week — Revenue', 'KES ' . number_format($weekRevenue))
                ->description('Profit: KES ' . number_format($weekProfit))
                ->descriptionIcon('heroicon-m-clock')
                ->color($weekProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Today — Revenue', 'KES ' . number_format($todayRevenue))
                ->description('Profit today: KES ' . number_format($todayProfit))
                ->descriptionIcon('heroicon-m-sun')
                ->color($todayProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Total Orders', (string) $totalOrders)
                ->description('All verified and completed sales')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),
        ];
    }
}
