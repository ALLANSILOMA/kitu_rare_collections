<?php

namespace App\Livewire;

use Livewire\Component;

class CartItems extends Component
{
    protected $listeners = ['cartUpdated' => '$refresh'];

    public function updateQuantity(string $cartKey, int $quantity): void
    {
        if ($quantity < 1) {
            $this->removeFromCart($cartKey);
            return;
        }
        $cart = session()->get('cart', []);
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = $quantity;
            session()->put('cart', $cart);
        }
        $this->dispatch('cartUpdated');
    }

    public function removeFromCart(string $cartKey): void
    {
        $cart = session()->get('cart', []);
        unset($cart[$cartKey]);
        session()->put('cart', $cart);
        $this->dispatch('cartUpdated');
    }

    public function getCartProperty(): array
    {
        return session()->get('cart', []);
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->cart)
            ->sum(fn ($i) => ($i['price'] ?? 0) * ($i['quantity'] ?? 1));
    }

    public function render()
    {
        return view('livewire.cart-items');
    }
}
