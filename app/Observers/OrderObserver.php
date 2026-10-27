<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
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

    public function updated(Order $order): void
    {
        if (!$order->wasChanged('status') && !$order->wasChanged('payment_status')) return;

        $status        = $order->getAttribute('status');
        $paymentStatus = $order->getAttribute('payment_status');

        // ── Auto-create a Sale record when payment is verified ───────────────
        if ($order->wasChanged('payment_status') && $paymentStatus === 'completed') {
            $this->createSaleFromOrder($order);
        }

        // ── Shipping notification via WhatsApp (handled in OrderResource UI)
        //    but also send email as fallback via observer ─────────────────────
        if ($order->wasChanged('status') && $status === 'shipped') {
            try {
                Mail::send(
                    'emails.order-shipped',
                    ['order' => $order],
                    function ($message) use ($order) {
                        $message
                            ->to($order->email, $order->first_name . ' ' . $order->last_name)
                            ->subject("Your KituRare order {$order->order_reference} has shipped! 🎉");
                    }
                );
            } catch (\Throwable $e) {
                Log::error('Shipping email failed: ' . $e->getMessage());
            }
        }

        // ── Stock restore on cancellation ────────────────────────────────────
        if ($order->wasChanged('status') && $status === 'cancelled') {
            $productId = $order->getAttribute('product_id');
            $quantity  = (int) $order->getAttribute('quantity') ?: 1;

            if (!$productId) return;

            $product = Product::find($productId);
            if ($product) {
                $product->increment('stock', $quantity);
                $this->sendStockRestoredNotification($product->getAttribute('name'), $quantity);
            }
        }
    }

    public function deleted(Order $order): void
    {
        $productId = $order->getAttribute('product_id');
        $quantity  = (int) $order->getAttribute('quantity') ?: 1;
        if (!$productId) return;
        $product = Product::find($productId);
        if ($product) $product->increment('stock', $quantity);
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

    private function createSaleFromOrder(Order $order): void
    {
        // Avoid duplicate Sale records if observer fires twice
        if (Sale::where('order_id', $order->id)->exists()) return;

        $product     = $order->product;
        $quantity    = (int) $order->getAttribute('quantity') ?: 1;
        $sellingPrice = (float) $order->getAttribute('total_amount') - (float) $order->getAttribute('shipping_cost');
        $buyingPrice  = $product ? (float) $product->getAttribute('buying_price') : 0;
        $shippingCost = (float) $order->getAttribute('shipping_cost');

        $totalRevenue = $sellingPrice + $shippingCost; // what the customer paid
        $totalCost    = $buyingPrice * $quantity;
        $profit       = $totalRevenue - $totalCost;

        Sale::create([
            'order_id'        => $order->id,
            'product_id'      => $order->getAttribute('product_id'),
            'order_reference' => $order->getAttribute('order_reference'),
            'customer_name'   => $order->first_name . ' ' . $order->last_name,
            'variation_label' => $order->getAttribute('variation_label'),
            'quantity'        => $quantity,
            'selling_price'   => $sellingPrice,
            'buying_price'    => $buyingPrice,
            'shipping_cost'   => $shippingCost,
            'total_revenue'   => $totalRevenue,
            'total_cost'      => $totalCost,
            'profit'          => $profit,
            'payment_method'  => $order->getAttribute('payment_method'),
            'shipping_method' => $order->getAttribute('shipping_method_name'),
        ]);
    }

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
        } catch (\Throwable $e) {}
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
        } catch (\Throwable $e) {}
    }
}
