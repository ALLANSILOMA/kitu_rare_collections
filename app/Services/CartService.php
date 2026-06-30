<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get all items in the cart from the session.
     */
    public function get()
    {
        return Session::get('cart', []);
    }

    /**
     * Get the total number of items (quantities summed up).
     */
    public function count(): int
    {
        $cart = $this->get();

        if (empty($cart)) {
            return 0;
        }

        // Sums up the 'quantity' column of the cart array
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Add a handbag to the cart.
     */
    public function add($product, $quantity = 1)
    {
        // 1. Get the current cart from session
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            // 2. If it exists, just increase the quantity
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            // 3. If new, grab the first image from your gallery array
            $images = is_array($product->images) ? $product->images : [];

            // Pick the first image or a placeholder
            $thumbnail = !empty($images) ? $images[0] : 'default.jpg';

            $cart[$product->id] = [
                'id'       => $product->id,
                'name'     => $product->name,
                'price'    => $product->price,
                'images'   => $images, // Store the whole array just in case
                'thumbnail'=> $thumbnail, // Store the specific string for the thumbnail
                'quantity' => $quantity,
            ];
        }

        // 4. Put it back in the session
        session()->put('cart', $cart);
    }
    /**
     * Remove an item entirely.
     */
    public function remove($productId)
    {
        $cart = $this->get();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
        }

        return $cart;
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        Session::forget('cart');
    }
}
