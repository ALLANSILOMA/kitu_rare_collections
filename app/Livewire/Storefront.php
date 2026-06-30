<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

class Storefront extends Component
{
    public function addToCart(Product $productId)
    {
        $product = Product::findOrFail($productId);
        CartService::add($product);

        $this->dispatch('cart:updated');

        session()->flash('success', 'Added to cart successfully!');
    }

    public function render()
    {
        return view('livewire.storefront', [
            'products' => Product::all()
        ]);

    }
}
