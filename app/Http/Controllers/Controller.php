<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

class Controller
{
    public function index() {
        return view('home');
    }

    public function products(Request $request)
    {
        $search = $request->input('search');
        $query = Product::query();

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $products = $query->get();
        $cartCount = app(CartService::class)->count();

        return view('products.index', compact('products', 'search', 'cartCount'));
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Add to Cart with Inventory Validation
     */
    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $quantity = (int)$request->input('quantity', 1);

        $variationLabel = $request->input('color_selection');

        if ($quantity > $product->stock) {
            return back()->with('error', "Only {$product->stock} available for this item.");
        }

        $cart = session()->get('cart', []);
        $cartKey = $product->id .($variationLabel ? '_' .md5($variationLabel) : '');

        if(isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;

            $newTotal = $cart[$product->id]['quantity'] + $quantity;

            if ($newTotal > $product->stock) {
                return back()->with('error', "You already have {$cart[$product->id]['quantity']} in your bag. Only {$product->stock} total available.");
            }

            $cart[$product->id]['quantity'] = $newTotal;
        } else {
            $images = is_array($product->images) ? $product->images : [];
            $thumb = !empty($images) ? $images[0] : 'default.jpg';

            $cart[$product->id] = [
                "name"     => $product->name,
                "quantity" => $quantity,
                "price"    => $product->price,
                "images"   => $images,
                "thumbnail"=> $thumb,
                'variation_label' => $variationLabel,
                "stock"    => $product->stock
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', $product->name . ' added to bag!');
    }

    public function cart() {
        $products = app(CartService::class)->get();

        $subtotal = 0;
        if (!empty($products)) {
            foreach ($products as $id => $item) {
                $dbProduct = Product::find($id);
                $products[$id]['stock'] = $dbProduct ? $dbProduct->stock : 0;

                $subtotal += $item['price'] * $item['quantity'];
            }
        }
        return view('cart', compact('products', 'subtotal'));
    }

    public function updateQuantity(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        $product = Product::findOrFail($id);

        if (isset($cart[$id])) {
            $action = $request->input('action');
            $manualQty = $request->input('quantity');

            if ($manualQty !== null) {
                $targetQty = (int)$manualQty;
            } else {
                $targetQty = ($action === 'increment')
                    ? $cart[$id]['quantity'] + 1
                    : $cart[$id]['quantity'] - 1;
            }

            if ($targetQty < 1) {
                return $this->removeItem($id);
            }

            if ($targetQty > $product->stock) {
                return back()->with('error', "Cannot exceed available stock ({$product->stock}).");
            }

            $cart[$id]['quantity'] = $targetQty;
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Bag updated!');
    }

    public function removeItem($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Item removed!');
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart')->with('info', 'Your cart is empty.');
        }

        $zones = ShippingZone::all();
        $total = 0;
        foreach($cart as $details) {
            $total += $details['price'] * $details['quantity'];
        }

        return view('checkout', compact('cart', 'total','zones'));
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required',
            'shipping_address' => 'required',
            'shipping_method_name' => 'required',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart')->with('info', 'Your cart is empty.');
        }
        $subtotal = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
        $shippingCost = (float)$request->input('shipping_cost', 0);

        $orderRef = 'KRC-' . date('Y') . '-' . str_pad(
                \App\Models\Order::whereYear('created_at', date('Y'))->count() + 1,
                5, '0', STR_PAD_LEFT
            );

        $firstItemKey   = array_key_first($cart);
        $firstItem      = $firstItemKey ? $cart[$firstItemKey] : null;
        $variationLabel = $firstItem['variation_label'] ?? null;

        $productId = $firstItemKey;
        if ($productId && str_contains((string)$productId, '_')) {
            $productId = (int)explode('_', $productId)[0];
        }

        $order = \App\Models\Order::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'shipping_address' => $request->shipping_address,
            'city' => $request->city ?? 'Nairobi',
            'shipping_method_name' => $request->shipping_method_name,
            'shipping_cost' => $shippingCost,
            'total_amount' => $subtotal + $shippingCost,
            'status' => 'new',
            'payment_status' => 'pending',
            'payment_method' => 'M-Pesa',
            'pickup_agent_details' => $request->pickup_agent_details,
            'product_id' => $productId ?? null,
            'order_reference' => $orderRef,
            'variation_label' => $variationLabel, // ← FIX
        ]);

        return redirect()->route('order.payment', $order->id);
    }

    public function showPaymentPage(Order $order)
    {
        if (in_array($order->payment_status, ['completed', 'awaiting_verification'])) {
            return redirect()->route('products')->with('info', 'This payment is already processing.');
        }

        return view('order-payment', compact('order'));
    }
    /**
     * Process the M-Pesa Code Submission Form
     */
    public function submitReceipt(Request $request, Order $order)
    {
        $request->validate([
            'payment_phone_number' => 'required|string|min:10|max:15',
            'mpesa_transaction_id' => 'required|string|size:10|unique:orders,mpesa_transaction_id',
        ], [
            'mpesa_transaction_id.unique' => 'This M-Pesa Code has already been submitted for evaluation.',
            'mpesa_transaction_id.size' => 'M-Pesa confirmation codes must be exactly 10 characters.'
        ]);

        $cleanMpesaCode = strtoupper(trim($request->mpesa_transaction_id));

        $order->update([
            'mpesa_transaction_id' => $cleanMpesaCode,
            'payment_phone_number' => $request->payment_phone_number,
            'payment_status'       => 'awaiting_verification',
        ]);

        // Capture cart items BEFORE the session is cleared
        $cartItems = session()->get('cart', []);

        $itemsSnapshot = collect($cartItems)->map(fn($item) => [
            'name'            => $item['name'],
            'quantity'        => $item['quantity'],
            'price'           => $item['price'],
            'variation_label' => $item['variation_label'] ?? null,
        ])->values()->toArray();

        session()->put('order_complete', [
            'order_id'        => $order->id,
            'order_reference' => $order->order_reference,
            'customer_name'   => $order->first_name . ' ' . $order->last_name,
            'email'           => $order->email,
            'phone'           => $order->phone,
            'total'           => $order->total_amount,
            'shipping_method' => $order->shipping_method_name,
            'items'           => $itemsSnapshot,
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($order->email)->send(new \App\Mail\OrderConfirmation($order));
        } catch (\Throwable $e) {
            // Log code can go here so mail hitches don't lock customer out
        }

        session()->forget('cart');

        return redirect()->route('order.thankyou')->with('success', 'Receipt submitted successfully!');
    }

    public function orderThankyou()
    {
        $sessionOrder = session('order_complete');

        if (!$sessionOrder) {
            return redirect()->route('products')->with('info', 'No recent order records found.');
        }

        // Fetch the live order row so status is always current
        $liveOrder = \App\Models\Order::find($sessionOrder['order_id']);

        // Merge live status into the session data so the blade has everything it needs
        $order = array_merge($sessionOrder, [
            'status'         => $liveOrder?->status ?? 'new',
            'payment_status' => $liveOrder?->payment_status ?? 'pending',
        ]);

        return view('order-thankyou', compact('order'));
    }
    // Express Checkout
    public function buyNow(Request $request)
    {
        $product  = Product::findOrFail($request->product_id);
        $quantity = $request->input('quantity', 1);

        session()->put('buy_now', [
            'product_id' => $product->id,
            'name'       => $product->name,
            'price'      => $product->price,
            'quantity'   => $quantity,
            'images'     => is_array($product->images) ? $product->images : [],
            'thumbnail'  => is_array($product->images) && !empty($product->images)
                ? $product->images[0]
                : null,
            'variation_label' => $request->input('color_selection'),
        ]);

        return redirect()->route('buy.now.checkout');
    }

    public function buyNowCheckout()
    {
        $item = session('buy_now');

        if (!$item) {
            return redirect()->route('products')
                ->with('info', 'Please select a product first.');
        }

        $zones = ShippingZone::all();
        $total = $item['price'] * $item['quantity'];

        return view('buy-now-checkout', compact('item', 'total', 'zones'));
    }

    public function buyNowPlaceOrder(Request $request)
    {
        $request->validate([
            'first_name'           => 'required|string|max:255',
            'last_name'            => 'required|string|max:255',
            'email'                => 'required|email',
            'phone'                => 'required',
            'shipping_address'     => 'required',
            'shipping_method_name' => 'required',
        ]);

        $item         = session('buy_now');
        $shippingCost = (float) $request->input('shipping_cost', 0);
        $total        = ($item['price'] * $item['quantity']) + $shippingCost;

        $orderRef = 'KRC-' . date('Y') . '-' . str_pad(
                \App\Models\Order::whereYear('created_at', date('Y'))->count() + 1,
                5, '0', STR_PAD_LEFT
            );

        $order = \App\Models\Order::create([
            'order_reference'      => $orderRef,
            'product_id'           => $item['product_id'] ?? null,
            'first_name'           => $request->first_name,
            'last_name'            => $request->last_name,
            'email'                => $request->email,
            'phone'                => $request->phone,
            'shipping_address'     => $request->shipping_address,
            'city'                 => $request->city ?? 'Nairobi',
            'shipping_method_name' => $request->shipping_method_name,
            'shipping_cost'        => $shippingCost,
            'total_amount'         => $total,
            'status'               => 'new',
            'payment_status'       => 'pending',
            'payment_method'       => 'M-Pesa',
            'pickup_agent_details' => $request->pickup_agent_details,
            'variation_label'      => $item['variation_label'] ?? null,
        ]);

        // Wrap mail so a failure never blocks the customer reaching the payment page
        try {
            \Illuminate\Support\Facades\Mail::to($order->email)
                ->send(new \App\Mail\OrderConfirmation($order));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Buy Now order confirmation email failed: ' . $e->getMessage());
        }

        // Clear ONLY the buy_now session — regular cart stays untouched
        session()->forget('buy_now');

        // ← FIX: was redirect()->route('home'), customer never reached payment page
        return redirect()->route('order.payment', $order->id);
    }
}
