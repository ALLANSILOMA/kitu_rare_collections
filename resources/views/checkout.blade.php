@php
    $logoPath = public_path('apple-icon-180x180.png');
    $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
    $logoSrc = 'data:image/png;base64,' . $logoData;
@endphp

<x-app-layout>
    {{-- High-Legibility Inter Font --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    </style>

    <div class="bg-[#FBFBFB] min-h-screen font-inter antialiased text-[#1A1A1A]">

        {{-- Horizontal Top Bar --}}
        <nav class="bg-white border-b border-gray-100 py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative flex items-center justify-between">

                    {{-- Left Link --}}
                    <div class="w-20">
                        <a href="{{ route('cart') }}" class="text-[10px] uppercase tracking-widest font-bold hover:text-gray-500 transition">Back to Bag</a>
                    </div>

                    {{-- Centered Shop Logo & Name Lockup --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <a href="/" class="pointer-events-auto flex items-center gap-4 group">
                            <img src="{{ $logoSrc }}" alt="Logo" class="w-10 h-10 object-contain rounded-full shadow-sm transition-transform group-hover:scale-105">
                            <span class="text-lg font-bold tracking-[0.3em] uppercase text-gray-900 hidden sm:block">
                                KituRare Collections
                            </span>
                        </a>
                    </div>

                    {{-- Cart/Bag Icon (Right) --}}
                    <div class="w-20 text-right">
                        <a href="{{ route('cart') }}" class="relative inline-block p-2 text-gray-700 hover:text-black transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            @if(count($cart) > 0)
                                <span class="absolute top-1 right-1 block h-2.5 w-2.5 rounded-full bg-black ring-2 ring-white"></span>
                            @endif
                        </a>
                    </div>

                </div>
            </div>
        </nav>

        {{-- Main Checkout Content --}}
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            {{-- Single form wrapping everything --}}
            <form action="{{ route('order.place') }}" method="POST" id="checkout-form">
                @csrf

                {{-- Validation Error Banner --}}
                @if ($errors->any())
                    <div class="mb-8 bg-red-50 border border-red-200 rounded-xl p-5 text-sm text-red-700 space-y-1">
                        <p class="font-bold uppercase tracking-widest text-[10px] text-red-500 mb-2">Please fix the following:</p>
                        @foreach ($errors->all() as $error)
                            <p>&#x2022; {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="flex flex-col lg:flex-row gap-16 items-start">

                    {{-- LEFT COLUMN: Details & Logistics --}}
                    <div class="w-full lg:w-[60%] space-y-10">

                        {{-- Delivery Section --}}
                        <div class="space-y-4">
                            <h2 class="text-lg font-bold uppercase tracking-tight">Delivery Details</h2>

                            <div class="space-y-1">
                                <label class="text-[10px] uppercase tracking-widest text-gray-400 font-bold ml-1">Country/Region</label>
                                <select name="country" class="w-full p-3.5 border border-gray-300 rounded-lg bg-white focus:ring-1 focus:ring-black focus:border-black outline-none appearance-none font-medium">
                                    <option value="Kenya" selected>Kenya</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="first_name" placeholder="First name" required class="p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white font-medium">
                                <input type="text" name="last_name" placeholder="Last name" required class="p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white font-medium">
                            </div>

                            <input type="email" name="email" placeholder="Email address (for order confirmation)" required
                                   class="w-full p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white font-medium">

                            <input type="text" name="pickup_agent_details" placeholder="Pick Up Mtaani location & agent (Pick Up Mtaani only)"
                                   class="w-full p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white italic text-sm text-gray-600">

                            <input type="text" name="shipping_address" required placeholder="Physical Address / Apartment / Estate"
                                   class="w-full p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white font-medium">

                            <input type="text" name="preferred_courier" placeholder="Town & preferred courier (Outside Nairobi only)"
                                   class="w-full p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white italic text-sm text-gray-600">

                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="city" placeholder="City" value="Nairobi" class="p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white font-medium">
                                <input type="text" name="postal_code" placeholder="Postal code (optional)" class="p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white font-medium">
                            </div>

                            <input type="text" name="phone" placeholder="Phone (For PesaPal/M-Pesa)" required
                                   class="w-full p-3.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-black outline-none bg-white font-medium">
                        </div>

                        {{-- Shipping Method Section --}}
                        <div class="space-y-4">
                            <h2 class="text-lg font-bold uppercase tracking-tight">Shipping Method</h2>
                            <div class="border border-gray-200 rounded-xl bg-white shadow-sm overflow-hidden divide-y divide-gray-100">
                                @foreach($zones as $zone)
                                    <label class="flex items-center p-5 cursor-pointer hover:bg-gray-50 transition-colors group">
                                        <div class="flex items-center">
                                            <input type="radio" name="shipping_method_name" value="{{ $zone->name }}"
                                                   data-price="{{ $zone->price }}"
                                                   {{ $loop->first ? 'checked' : '' }}
                                                   class="w-4 h-4 text-black focus:ring-black border-gray-300">
                                        </div>

                                        <div class="flex-1 ml-4">
                                            <span class="text-sm font-semibold text-gray-700">{{ $zone->name }}</span>
                                        </div>

                                        <div class="text-right">
                                            <span class="text-sm font-bold tabular-nums text-gray-900">
                                                @if($zone->price > 0)
                                                    <span class="text-[10px] text-gray-400 font-normal mr-1">Ksh</span>{{ number_format($zone->price) }}
                                                @else
                                                    <span class="text-green-600 font-bold uppercase text-[11px]">Free</span>
                                                @endif
                                            </span>
                                        </div>
                                        <input type="hidden" name="shipping_cost" value="{{ $zone->price }}" {{ $loop->first ? '' : 'disabled' }} class="zone-price-input">
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Payment Module --}}
                        <div class="space-y-4">
                            <h2 class="text-lg font-bold uppercase tracking-tight">Payment</h2>
                            <div class="border border-gray-200 rounded-xl bg-white overflow-hidden shadow-sm">
                                <div class="p-4 bg-gray-50 flex justify-between items-center border-b border-gray-100">
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 rounded-full border-4 border-black mr-3"></div>
                                        <span class="text-sm font-bold">Mpesa (Secure Checkout)</span>
                                    </div>
                                    <div class="flex gap-2 text-gray-400">
                                        <i class="fab fa-cc-visa text-lg"></i>
                                        <i class="fab fa-cc-mastercard text-lg"></i>
                                        <span class="text-[9px] font-bold border border-gray-400 px-1.5 py-0.5 rounded-md uppercase">M-Pesa</span>
                                    </div>
                                </div>
                                <div class="p-10 text-center text-sm text-gray-500">
                                    <p class="max-w-xs mx-auto leading-relaxed">Secure checkout via M-Pesa, Card or Bank Transfer.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Pay Now Button --}}
                        <button type="submit" class="w-full bg-black text-white font-bold py-4 rounded-xl text-sm uppercase tracking-widest hover:bg-gray-900 transition-colors">
                            Pay Now
                        </button>

                    </div>

                    {{-- RIGHT COLUMN: Sticky Order Summary --}}
                    <div class="w-full lg:w-[40%] bg-gray-50 p-8 rounded-2xl lg:bg-transparent lg:sticky lg:top-8 border border-gray-100 lg:border-none">
                        <div class="lg:border-l lg:border-gray-200 lg:pl-10 space-y-8">
                            <h2 class="text-lg font-bold uppercase tracking-tight">Order Summary</h2>

                            {{-- pt-3 px-3 give the absolute-positioned badges room; overflow-y-auto silently clips overflow-x otherwise --}}
                            <div class="space-y-6 max-h-[50vh] overflow-y-auto pt-3 px-3 custom-scrollbar">
                                @foreach($cart as $id => $item)
                                    <div class="flex items-center">
                                        {{-- Image + Badge Container --}}
                                        <div class="relative flex-shrink-0">
                                            <div class="w-20 h-20 bg-white border border-gray-200 rounded-xl shadow-sm">
                                                <img src="{{ asset('storage/' . $item['thumbnail']) }}"
                                                     alt="{{ $item['name'] }}"
                                                     class="w-full h-full object-cover rounded-xl overflow-hidden">
                                            </div>
                                            <span class="absolute -top-2 -right-2 bg-black text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold ring-2 ring-white">
                                                {{ $item['quantity'] }}
                                            </span>
                                        </div>

                                        <div class="ml-4 flex-1">
                                            <p class="text-sm font-bold text-gray-800 leading-tight uppercase tracking-tighter">{{ $item['name'] }}</p>
                                        </div>
                                        <p class="text-sm font-bold whitespace-nowrap text-gray-900">Ksh {{ number_format($item['price'] * $item['quantity']) }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="border-t border-gray-200 pt-8 space-y-4 text-sm">
                                <div class="flex justify-between text-gray-500 font-medium">
                                    <span>Subtotal</span>
                                    <span class="text-gray-900">Ksh {{ number_format($total) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-500 font-medium">
                                    <span>Shipping</span>
                                    <span id="shipping-display" class="text-gray-900">Select method</span>
                                </div>
                                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                                    <span class="text-xs uppercase tracking-[0.2em] text-gray-400 font-bold">Total</span>
                                    <div class="text-right">
                                        <span class="text-[10px] text-gray-400 mr-1 align-middle uppercase font-bold">KES</span>
                                        <span id="grand-total" class="text-2xl font-bold tracking-tighter">Ksh {{ number_format($total) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
            {{-- End single form --}}

        </div>
    </div>

    <script>
        const subtotal = {{ $total }};
        const radios = document.querySelectorAll('input[name="shipping_method_name"]');
        const shippingDisplay = document.getElementById('shipping-display');
        const grandTotalDisplay = document.getElementById('grand-total');

        function updateTotals(radio) {
            const price = parseFloat(radio.dataset.price);
            shippingDisplay.innerText = price > 0 ? 'Ksh ' + price.toLocaleString() : 'FREE';
            grandTotalDisplay.innerText = 'Ksh ' + (subtotal + price).toLocaleString();

            document.querySelectorAll('.zone-price-input').forEach(input => input.disabled = true);
            radio.closest('label').querySelector('.zone-price-input').disabled = false;
        }

        radios.forEach(radio => {
            radio.addEventListener('change', () => updateTotals(radio));
            if (radio.checked) updateTotals(radio);
        });
    </script>
</x-app-layout>
