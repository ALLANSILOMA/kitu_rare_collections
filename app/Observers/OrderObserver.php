<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;   // ← FIX 1: was missing
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Fires when any order is created — storefront or back-office.
     * Deducts stock and sends a low-stock Filament notification if needed.
     */
    public function created(Order $order): void
    {
        $productId = $order->getAttribute('product_id');
        $quantity  = (int) $order->getAttribute('quantity') ?: 1;

        if (!$productId) return;

        $product = Product::find($productId);
        if (!$product) return;

        $currentStock = (int) $product->getAttribute('stock');
        $newStock     = max(0, $currentStock - $quantity);

        $product->update(['stock' => $newStock]);

        if ($newStock <= 5) {
            $this->sendLowStockNotification($product->getAttribute('name'), $newStock);
        }
    }

    /**
     * Fires after an order row is saved.
     * Handles two independent status-change events:
     *   1. status → 'shipped'   : sends shipping notification email to customer
     *   2. status → 'cancelled' : restores stock and notifies admin
     *
     */
    public function updated(Order $order): void
    {
        // ── Guard: only act when 'status' actually changed ────────────────
        if (!$order->wasChanged('status')) return;

        $newStatus = $order->getAttribute('status');

        // ── 1. Shipping notification ───────────────────────────────────────
        if ($newStatus === 'shipped') {
            try {
                Mail::send(
                    'emails.order-confirmation',
                    ['order' => $order],
                    function ($message) use ($order) {
                        $message
                            ->to($order->email, $order->first_name . ' ' . $order->last_name)  // ← FIX 4: space added
                            ->subject("Your KituRare order {$order->order_reference} has shipped! 🎉");  // ← FIX 4: space added
                    }
                );
            } catch (\Throwable $e) {
                Log::error('Shipping email failed: ' . $e->getMessage());
            }
        }

        // ── 2. Cancellation — restore stock ───────────────────────────────
        if ($newStatus === 'cancelled') {
            $productId = $order->getAttribute('product_id');
            $quantity  = (int) $order->getAttribute('quantity') ?: 1;

            if (!$productId) return;

            $product = Product::find($productId);

            if ($product) {
                $product->increment('stock', $quantity);

                $this->sendStockRestoredNotification(
                    $product->getAttribute('name'),
                    $quantity
                );
            }
        }
    }

    /**
     * Fires when an order is hard-deleted.
     * Restores stock so inventory stays accurate.
     */
    public function deleted(Order $order): void
    {
        $productId = $order->getAttribute('product_id');
        $quantity  = (int) $order->getAttribute('quantity') ?: 1;

        if (!$productId) return;

        $product = Product::find($productId);

        if ($product) {
            $product->increment('stock', $quantity);
        }
    }

    public function restored(Order $order): void
    {
        $productId = $order->getAttribute('product_id');
        $quantity  = (int) $order->getAttribute('quantity') ?: 1;

        if (!$productId) return;

        $product = Product::find($productId);

        if ($product) {
            $currentStock = (int) $product->getAttribute('stock');
            $product->update(['stock' => max(0, $currentStock - $quantity)]);
        }
    }

    public function forceDeleted(Order $order): void
    {
        $this->deleted($order);
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function sendLowStockNotification(string $productName, int $remaining): void
    {
        try {
            $adminUser = \App\Models\User::first();
            if (!$adminUser) return;

            \Filament\Notifications\Notification::make()
                ->title('Low Stock Alert')
                ->body("{$productName} has only {$remaining} unit(s) left.")
                ->warning()
                ->sendToDatabase($adminUser);
        } catch (\Throwable $e) {
            // Silently skip if Filament notifications are not set up
        }
    }

    private function sendStockRestoredNotification(string $productName, int $quantity): void
    {
        try {
            $adminUser = \App\Models\User::first();
            if (!$adminUser) return;

            \Filament\Notifications\Notification::make()
                ->title('Stock Restored')
                ->body("{$productName} +{$quantity} units restored after cancellation.")
                ->info()
                ->sendToDatabase($adminUser);
        } catch (\Throwable $e) {
            // Silently skip if Filament notifications are not set up
        }
    }
}
