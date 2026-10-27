<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

class CartPage extends Component
{
    public array $cart     = [];
    public float $subtotal = 0;

    public function mount(): void
    {
        $this->refreshCart();
    }

    public function increment(int|string $key): void
    {
        $this->changeQuantity($key, 'increment');
    }

    public function decrement(int|string $key): void
    {
        $this->changeQuantity($key, 'decrement');
    }

    public function remove(int|string $key): void
    {
        $cart = session()->get('cart', []);
        unset($cart[$key]);
        session()->put('cart', $cart);
        $this->refreshCart();
        $this->dispatch('cart-updated', count: count($cart));
    }

    private function changeQuantity(int|string $key, string $direction): void
    {
        $cart = session()->get('cart', []);

        if (!isset($cart[$key])) return;

        $current   = (int) $cart[$key]['quantity'];
        $targetQty = $direction === 'increment' ? $current + 1 : $current - 1;

        if ($targetQty < 1) {
            $this->remove($key);
            return;
        }

        // Extract numeric product ID from the cart key (may be "42" or "42_abc123")
        $productId = str_contains((string) $key, '_')
            ? (int) explode('_', (string) $key)[0]
            : (int) $key;

        $product = Product::find($productId);
        if ($product && $targetQty > $product->stock) {
            $this->dispatch('show-error', message: "Only {$product->stock} available.");
            return;
        }

        $cart[$key]['quantity'] = $targetQty;
        session()->put('cart', $cart);
        $this->refreshCart();
        $this->dispatch('cart-updated', count: count($cart));
    }

    private function refreshCart(): void
    {
        // ── Use CartService exactly the same way the controller does ──────────
        // This guarantees the component reads from the same source and gets
        // the same data shape that the rest of the app expects.
        $cartService = app(CartService::class);

        // CartService::get() returns the processed cart array
        $items = $cartService->get();

        // If CartService::get() returns null or false, fall back to raw session
        if (empty($items)) {
            $items = session()->get('cart', []);
        }

        // Refresh stock levels from DB
        foreach ($items as $key => $item) {
            $productId = str_contains((string) $key, '_')
                ? (int) explode('_', (string) $key)[0]
                : (int) $key;

            $dbProduct = Product::find($productId);
            $items[$key]['stock'] = $dbProduct?->stock ?? 0;
        }

        $this->cart     = $items;
        $this->subtotal = collect($items)->sum(fn($i) => $i['price'] * $i['quantity']);
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}
