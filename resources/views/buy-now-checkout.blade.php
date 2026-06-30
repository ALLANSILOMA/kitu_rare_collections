@php
    $logoPath = public_path('apple-icon-180x180.png');
    $logoData = base64_encode(file_get_contents($logoPath));
    $logoSrc = 'data:image/png;base64,' . $logoData;
@endphp

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KituRare Collections | Express Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#FBFBFB] font-inter antialiased text-[#1A1A1A]">

{{-- Nav --}}
<nav class="bg-white border-b border-gray-100 py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between">

            <div class="w-10"></div>

            {{-- Centered Logo --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <a href="/" class="pointer-events-auto flex items-center gap-4 group">
                    <img src="{{ $logoSrc }}" alt="Logo" class="w-12 h-12 object-contain rounded-full transition-transform group-hover:scale-105">
                    <span class="text-xl font-bold tracking-[0.3em] uppercase text-gray-900 hidden sm:block">
                            KituRare Collections
                        </span>
                </a>
            </div>

            {{-- Cart link — cart is unaffected by Buy Now --}}
            <a href="{{ route('cart') }}" class="relative group p-2 text-gray-700 hover:text-black transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                @php $cartCount = count(session('cart', [])); @endphp
                @if($cartCount > 0)
                    <span class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-black ring-2 ring-white"></span>
                @endif
            </a>

        </div>
    </div>
</nav>

{{-- Express Checkout Badge --}}
<div class="bg-black text-white text-center py-2">
    <p class="text-[10px] uppercase tracking-[0.3em] font-bold">
        <i class="fas fa-bolt mr-2 text-yellow-400"></i>Express Checkout — This item is separate from your shopping bag
    </p>
</div>

{{-- Main Content --}}
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <form action="{{ route('buy.now.order') }}" method="POST" id="checkout-form">
        @csrf
        <div class="flex flex-col lg:flex-row gap-16 items-start">

            {{-- LEFT COLUMN: Delivery & Shipping & Payment --}}
            <div class="w-full lg:w-[60%] space-y-10">

                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <ul class="text-sm text-red-600 space-y-1">
                            @foreach($errors->all() as $error)
                                <li><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Delivery Details --}}
                <div class="space-y-4">
                    <h2 class="text-lg font-medium">Delivery Details</h2>

                    <div class="space-y-1">
                        <label class="text-[10px] uppercase tracking-widest text-gray-400 font-bold ml-1">Country/Region</label>
                        <select name="country" class="w-full p-3.5 border border-gray-300 rounded-lg bg-white focus:ring-1 focus:ring-black focus:border-black outline-none appearance-none">
                            <option value="Kenya" selected>Kenya</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="first_name" placeholder="First name" required
                               value="{{ old('first_name') }}"
                               class="p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white">
                        <input type="text" name="last_name" placeholder="Last name" required
                               value="{{ old('last_name') }}"
                               class="p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white">
                    </div>

                    <input type="text" name="pickup_agent_details"
                           placeholder="Pick Up Mtaani location & agent (Pick Up Mtaani only)"
                           value="{{ old('pickup_agent_details') }}"
                           class="w-full p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white italic text-sm text-gray-600">

                    <input type="text" name="shipping_address"
                           placeholder="Physical Address / Apartment / Estate" required
                           value="{{ old('shipping_address') }}"
                           class="w-full p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white">

                    <input type="text" name="preferred_courier"
                           placeholder="Town & preferred courier (Outside Nairobi only)"
                           value="{{ old('preferred_courier') }}"
                           class="w-full p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white italic text-sm text-gray-600">

                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="city" placeholder="City" value="{{ old('city', 'Nairobi') }}" required
                               class="p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white">
                        <input type="text" name="postal_code" placeholder="Postal code (optional)"
                               value="{{ old('postal_code') }}"
                               class="p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white">
                    </div>

                    <input type="text" name="phone" placeholder="Phone (For PesaPal/M-Pesa)" required
                           value="{{ old('phone') }}"
                           class="w-full p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white">

                    <input type="email" name="email" placeholder="Email address" required
                           value="{{ old('email') }}"
                           class="w-full p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white">
                </div>

                {{-- Shipping Method --}}
                <div class="space-y-4">
                    <h2 class="text-lg font-medium">Shipping Method</h2>
                    <div class="border border-gray-200 rounded-xl bg-white shadow-sm overflow-hidden divide-y divide-gray-100">
                        @foreach($zones as $zone)
                            <label class="flex items-center p-5 cursor-pointer hover:bg-gray-50 transition-colors group">
                                <input type="radio"
                                       name="shipping_method_name"
                                       value="{{ $zone->name }}"
                                       data-price="{{ $zone->price }}"
                                       {{ $loop->first ? 'checked' : '' }}
                                       class="w-4 h-4 text-black focus:ring-black border-gray-300">

                                <div class="flex-1 ml-4">
                                    <span class="text-sm font-medium text-gray-700">{{ $zone->name }}</span>
                                </div>

                                <div class="text-right">
                                        <span class="text-sm font-bold tabular-nums text-gray-900">
                                            @if($zone->price > 0)
                                                <span class="text-[10px] text-gray-400 font-normal mr-1">Ksh</span>{{ number_format($zone->price) }}
                                            @else
                                                <span class="text-green-600 font-bold tracking-tight uppercase text-[11px]">Free</span>
                                            @endif
                                        </span>
                                </div>

                                <input type="hidden"
                                       name="shipping_cost"
                                       value="{{ $zone->price }}"
                                       {{ $loop->first ? '' : 'disabled' }}
                                       class="zone-price-input">
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Payment --}}
                <div class="space-y-4">
                    <h2 class="text-lg font-medium">Payment</h2>
                    <div class="border border-gray-200 rounded-xl bg-white overflow-hidden shadow-sm">
                        <div class="p-4 bg-gray-50 flex justify-between items-center border-b border-gray-100">
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full border-4 border-black mr-3"></div>
                                <span class="text-sm font-semibold">M-pesa (Secure Checkout)</span>
                            </div>
                            <div class="flex gap-2 text-gray-400">
                                <span class="text-[9px] font-bold border border-gray-400 px-1.5 py-0.5 rounded-md uppercase">M-Pesa</span>
                            </div>
                        </div>
                        <div class="p-12 text-center text-sm text-gray-500">
                            <p class="max-w-xs mx-auto text-balance">
                                After clicking "Pay now", you will be redirected to Pay to complete your purchase securely via M-Pesa.
                            </p>
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-black text-white font-bold py-5 rounded-lg hover:bg-gray-900 transition-all shadow-xl text-sm uppercase tracking-[0.2em]">
                    <i class="fas fa-bolt mr-2 text-yellow-400"></i>Pay now
                </button>

            </div>

            {{-- RIGHT COLUMN: Order Summary (single item) --}}
            <div class="w-full lg:w-[40%] lg:sticky lg:top-8">
                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-8 space-y-8">

                    <h2 class="text-lg font-medium">Order Summary</h2>

                    {{-- The single Buy Now product --}}
                    <div class="flex items-center gap-4">
                        <div class="relative flex-shrink-0">
                            <div class="w-20 h-20 bg-white border border-gray-200 rounded-xl shadow-sm">
                                @if($item['thumbnail'])
                                    <img src="{{ asset('storage/' . $item['thumbnail']) }}"
                                         alt="{{ $item['name'] }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                        <i class="fas fa-image text-gray-300 text-xl"></i>
                                    </div>
                                @endif
                            </div>
                            {{-- Quantity badge --}}
                            <span class="absolute -top-2 -right-2 bg-black text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold ring-2 ring-white">
                                    {{ $item['quantity'] }}
                                </span>
                        </div>

                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-800 uppercase tracking-tight leading-tight">
                                {{ $item['name'] }}
                            </p>
                            @if(!empty($item['variation_label']))
                                <p class="text-[11px] text-gray-400 uppercase tracking-widest mt-0.5">{{ $item['variation_label'] }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">Qty: {{ $item['quantity'] }}</p>
                        </div>

                        <p class="text-sm font-bold whitespace-nowrap">
                            Ksh {{ number_format($item['price'] * $item['quantity']) }}
                        </p>
                    </div>

                    {{-- Totals --}}
                    <div class="border-t border-gray-200 pt-6 space-y-4 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal</span>
                            <span class="font-medium text-gray-900">Ksh {{ number_format($total) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Shipping</span>
                            <span id="shipping-display" class="font-medium text-gray-900">Select method</span>
                        </div>
                        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                            <span class="text-xs uppercase tracking-widest text-gray-400">Total</span>
                            <div class="text-right">
                                <span class="text-[10px] text-gray-400 mr-1 align-middle uppercase">KES</span>
                                <span id="grand-total" class="text-2xl font-bold tracking-tighter">
                                        {{ number_format($total) }}
                                    </span>
                            </div>
                        </div>
                    </div>

                    {{-- Reassurance: cart is safe --}}
                    <div class="flex items-start gap-3 bg-white border border-gray-100 rounded-xl p-4">
                        <i class="fas fa-shield-alt text-green-500 mt-0.5 flex-shrink-0"></i>
                        <p class="text-[11px] text-gray-400 leading-relaxed">
                            This is an express order for this item only. Your existing bag is untouched and waiting for you.
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </form>
</div>

{{-- Shipping Calculation Script --}}
<script>
    const subtotal = {{ $total }};
    const radios = document.querySelectorAll('input[name="shipping_method_name"]');
    const shippingDisplay = document.getElementById('shipping-display');
    const grandTotalDisplay = document.getElementById('grand-total');

    function updateTotals(radio) {
        const price = parseFloat(radio.dataset.price);
        shippingDisplay.innerText = price > 0 ? 'Ksh ' + price.toLocaleString() : 'FREE';
        grandTotalDisplay.innerText = (subtotal + price).toLocaleString();

        document.querySelectorAll('.zone-price-input').forEach(input => input.disabled = true);
        radio.closest('label').querySelector('.zone-price-input').disabled = false;
    }

    radios.forEach(radio => {
        radio.addEventListener('change', () => updateTotals(radio));
        if (radio.checked) updateTotals(radio);
    });
</script>

</body>
</html>
