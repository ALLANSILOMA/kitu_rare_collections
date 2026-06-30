@php
    $logoPath = public_path('apple-icon-180x180.png');
    $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
    $logoSrc  = 'data:image/png;base64,' . $logoData;
    $whatsappNum  = '+254116020420';
@endphp
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KituRare Collections | Order Confirmed</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(34,197,94,0.25); }
            70%  { box-shadow: 0 0 0 10px rgba(34,197,94,0); }
            100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
        }
        .pulse-check { animation: pulse-ring 2.4s infinite; }
    </style>
</head>
<body class="bg-[#FBFBFB] antialiased text-[#1A1A1A]">

{{-- ── Top Bar (matches checkout & payment pages) ── --}}
<nav class="bg-white border-b border-gray-100 py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between">

            <div class="w-28">
                <a href="{{ route('products') }}"
                   class="text-[10px] uppercase tracking-widest font-bold text-gray-400 hover:text-gray-700 transition flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Shop
                </a>
            </div>

            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <a href="/" class="pointer-events-auto flex items-center gap-4 group">
                    <img src="{{ $logoSrc }}" alt="Logo"
                         class="w-10 h-10 object-contain rounded-full shadow-sm transition-transform group-hover:scale-105">
                    <span class="text-lg font-bold tracking-[0.3em] uppercase text-gray-900 hidden sm:block">
                        KituRare Collections
                    </span>
                </a>
            </div>



        </div>
    </div>
</nav>

{{-- ── Progress breadcrumb ── --}}
<div class="border-b border-gray-100 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-center gap-0 py-3.5 text-[10px] uppercase tracking-[0.15em] font-bold">
            <span class="text-gray-300">Bag</span>
            <svg class="h-3 w-3 mx-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-300">Checkout</span>
            <svg class="h-3 w-3 mx-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-300">Payment</span>
            <svg class="h-3 w-3 mx-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-black border-b-2 border-black pb-0.5">Confirmed</span>
        </div>
    </div>
</div>

<main class="max-w-xl mx-auto px-4 py-16 space-y-8">

    {{-- ── Confirmation header ── --}}
    <div class="text-center space-y-4">
        <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto pulse-check">
            <i class="fas fa-check text-green-500 text-3xl"></i>
        </div>
        <div class="space-y-2">
            <h1 class="text-2xl font-bold tracking-tight">Order Confirmed!</h1>
            <p class="text-gray-500 text-sm max-w-sm mx-auto leading-relaxed">
                Thank you <strong class="text-gray-800">{{ $order['customer_name'] }}</strong> — we've received your
                payment confirmation and your order is now being prepared for shipping.
            </p>
        </div>
        <div class="inline-block bg-black text-white px-6 py-2.5 rounded-full
                    text-xs font-bold uppercase tracking-[0.25em]">
            {{ $order['order_reference'] }}
        </div>
    </div>

    {{-- ── Status timeline ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-6">
        <div class="flex items-center justify-between text-center">
            <div class="flex-1 space-y-2">
                <div class="w-9 h-9 bg-green-500 rounded-full flex items-center justify-center mx-auto">
                    <i class="fas fa-check text-white text-xs"></i>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-700">Order Placed</p>
            </div>
            <div class="flex-1 h-px bg-gray-200 -mt-6"></div>
            <div class="flex-1 space-y-2">
                <div class="w-9 h-9 bg-amber-400 rounded-full flex items-center justify-center mx-auto">
                    <i class="fas fa-clock text-white text-xs"></i>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-700">Verifying Payment</p>
            </div>
            <div class="flex-1 h-px bg-gray-200 -mt-6"></div>
            <div class="flex-1 space-y-2">
                <div class="w-9 h-9 bg-gray-200 rounded-full flex items-center justify-center mx-auto">
                    <i class="fas fa-box text-gray-400 text-xs"></i>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Shipped</p>
            </div>
        </div>
        <p class="text-[11px] text-gray-400 text-center mt-5">
            We're verifying your M-Pesa payment now — this usually takes under 2 hours during business hours.
            You'll get an email once it's confirmed and your order ships.
        </p>
    </div>

    {{-- ── Order summary ── --}}
    @if(!empty($order['items']))
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-bold uppercase tracking-widest text-[10px] text-gray-500">Order Summary</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($order['items'] as $item)
                    <div class="px-6 py-4 flex justify-between items-start">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-tight">{{ $item['name'] }}</p>
                            @if(!empty($item['variation_label']))
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $item['variation_label'] }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-0.5">Qty: {{ $item['quantity'] }}</p>
                        </div>
                        <p class="text-sm font-bold">KES {{ number_format($item['price'] * $item['quantity']) }}</p>
                    </div>
                @endforeach
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-between font-bold text-base bg-gray-50">
                <span>Total Paid</span>
                <span class="text-green-700">KES {{ number_format($order['total']) }}</span>
            </div>
        </div>
    @endif

    {{-- ── Delivery details ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
        <h2 class="font-bold uppercase tracking-widest text-[10px] text-gray-500 mb-4">Shipping To</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-gray-400 mb-1">Name</p>
                <p class="font-semibold">{{ $order['customer_name'] }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-gray-400 mb-1">Phone</p>
                <p class="font-semibold">{{ $order['phone'] }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-gray-400 mb-1">Email</p>
                <p class="font-semibold text-xs break-all">{{ $order['email'] }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-widest text-gray-400 mb-1">Shipping Method</p>
                <p class="font-semibold text-xs">{{ $order['shipping_method'] }}</p>
            </div>
        </div>
    </div>

    {{-- ── CTA ── --}}
    <div class="text-center space-y-3 pb-8">
        <a href="{{ route('products') }}"
           class="inline-flex items-center gap-2 bg-black text-white px-8 py-4
                  rounded-xl text-[10px] font-bold uppercase tracking-[0.2em]
                  hover:bg-gray-800 transition shadow-lg">
            <i class="fas fa-bag-shopping text-xs"></i>
            Continue Shopping
        </a>
        <p class="text-[11px] text-gray-400">
            Questions about your order?
            <a href="https://wa.me/{{ $whatsappNum }}" class="text-green-600 font-semibold hover:underline">
                Chat with us on WhatsApp
            </a>
        </p>
    </div>

</main>
</body>
</html>
