<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartCounter extends Component
{
    protected $listeners = ['cart:updated' => '$refresh'];

    public function render()
    {
        $cart = session()->get('cart', []);
        $count = collect(CartService::get())->sum('quantity');

        return view('livewire.cart-counter', ['count' => $count]);
    }
}
