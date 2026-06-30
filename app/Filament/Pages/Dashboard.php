<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Response;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Shop Dashboard';

    protected static ?string $slug = '/';
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_report')
                ->label('Download Sales Report')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->downloadReport()),
        ];
    }

    public function downloadReport()
    {
        $orders = \App\Models\Order::all();
        $csvFileName = 'kiturare_sales_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Customer', 'Total Amount', 'Date']);

            foreach ($orders as $order) {
                fputcsv($file, [$order->id, $order->user->name ?? 'Guest', $order->total_amount, $order->created_at]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
