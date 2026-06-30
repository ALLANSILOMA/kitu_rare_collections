<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\CartService;

namespace App\Livewire;

class CartDrawer extends Component
{
    // Listen for the event we created earlier
    protected $listeners = ['cart-updated' => '$refresh'];

    public function removeFromCart($productId)
    {
        CartService::remove($productId);
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $cartItems = CartService::get();

        // Calculate subtotal
        $subtotal = collect($cartItems)->sum(fn($item) => $item['price'] * $item['quantity']);

        return view('livewire.cart-drawer', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal
        ]);
    }
}
