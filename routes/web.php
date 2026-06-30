<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

// ── Storefront ────────────────────────────────────────────────────────────────
Route::get('/',          [Controller::class, 'index'])->name('home');
Route::get('/products',  [Controller::class, 'products'])->name('products');
Route::get('/products/{product}', [Controller::class, 'show'])->name('products.show');

// ── Cart ──────────────────────────────────────────────────────────────────────
Route::get('/cart',                [Controller::class, 'cart'])->name('cart');
Route::post('/cart/add',           [Controller::class, 'addToCart'])->name('cart.add');
Route::post('/cart/remove/{id}',   [Controller::class, 'removeItem'])->name('cart.remove');
Route::post('/cart/update/{id}',   [Controller::class, 'updateQuantity'])->name('cart.update');

// ── Checkout ──────────────────────────────────────────────────────────────────
Route::get('/cart/checkout',       [Controller::class, 'checkout'])->name('checkout');
// Single canonical POST route for placing an order — duplicate removed
Route::post('/cart/place-order',   [Controller::class, 'placeOrder'])->name('order.place');

// ── Payment Pipeline ──────────────────────────────────────────────────────────
Route::get('/order/payment/{order}',              [Controller::class, 'showPaymentPage'])->name('order.payment');
Route::post('/order/payment/{order}/submit',      [Controller::class, 'submitReceipt'])->name('order.submit-receipt');
Route::get('/order/thank-you',                    [Controller::class, 'orderThankyou'])->name('order.thankyou');

// ── Buy Now (Express Checkout) ────────────────────────────────────────────────
Route::post('/buy-now',             [Controller::class, 'buyNow'])->name('buy.now');
Route::get('/buy-now/checkout',     [Controller::class, 'buyNowCheckout'])->name('buy.now.checkout');
Route::post('/buy-now/place-order', [Controller::class, 'buyNowPlaceOrder'])->name('buy.now.order');
