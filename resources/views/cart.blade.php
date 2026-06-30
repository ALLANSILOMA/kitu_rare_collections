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
    <title>KituRare Collections | Shopping Cart</title>
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

{{-- Sticky Horizontal Top Bar --}}
<nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 py-5 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between">

            <div class="w-12">
                <a href="{{ route('products') }}" class="text-xs uppercase tracking-widest font-bold hover:text-gray-500 transition">Shop</a>
            </div>

            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="flex items-center gap-3">
                    <img src="{{ $logoSrc }}" class="w-10 h-10 object-contain rounded-full shadow-sm">
                    <a href="/" class="pointer-events-auto text-lg font-bold tracking-[0.3em] uppercase hidden sm:block">
                        KituRare Collections
                    </a>
                </div>
            </div>

            <div class="w-12 text-right">
                <i class="fa-solid fa-bag-shopping text-xl text-gray-400"></i>
            </div>
        </div>
    </div>
</nav>

<main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Your Bag</h1>

    <div class="mt-12 lg:grid lg:grid-cols-12 lg:items-start lg:gap-x-12">

        {{-- LEFT COLUMN: Product List --}}
        <section class="lg:col-span-7">
            <ul role="list" class="divide-y divide-gray-100 border-b border-t border-gray-100">
                @forelse($products as $id => $product)
                    <li class="flex py-8 sm:py-10">
                        <div class="shrink-0">
                            @php
                                $images = $product['images'] ?? null;
                                $thumb  = is_array($images) ? ($images[0] ?? null) : ($images ?? null);
                            @endphp
                            <img src="{{ $thumb ? asset('storage/' . $thumb) : asset('images/placeholder.jpg') }}"
                                 alt="{{ $product['name'] }}"
                                 class="size-32 rounded-xl object-cover object-center sm:size-48 bg-gray-100 shadow-sm">
                        </div>

                        <div class="ml-6 flex flex-1 flex-col justify-between">
                            <div class="relative pr-9 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:pr-0">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-tight">
                                        {{ $product['name'] }}
                                    </h3>

                                    @if(!empty($product['variation_label']))
                                        <p class="mt-1 text-[11px] text-gray-400 uppercase tracking-widest">
                                            {{ $product['variation_label'] }}
                                        </p>
                                    @endif

                                    <p class="mt-2 text-sm font-semibold text-gray-500">
                                        KES {{ number_format($product['price']) }}
                                    </p>
                                </div>

                                <div class="mt-4 sm:mt-0">
                                    {{-- QUANTITY CONTROLS --}}
                                    <div class="flex items-center rounded-lg border border-gray-200 w-fit bg-white">
                                        <form action="{{ route('cart.update', $id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="decrement">
                                            <button type="submit" class="px-3 py-1 text-gray-400 hover:text-black transition">-</button>
                                        </form>
                                        <span class="px-3 py-1 text-xs font-bold w-10 text-center">{{ $product['quantity'] }}</span>
                                        <form action="{{ route('cart.update', $id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="increment">
                                            <button type="submit" class="px-3 py-1 text-gray-400 hover:text-black transition">+</button>
                                        </form>
                                    </div>

                                    {{-- REMOVE BUTTON --}}
                                    <div class="absolute right-0 top-0">
                                        <form action="{{ route('cart.remove', $id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-gray-300 hover:text-red-500 transition">
                                                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <p class="mt-4 flex text-sm text-gray-700 font-medium">
                                <i class="fa-solid fa-check text-green-500 mr-2"></i> In stock & ready to ship
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="py-20 text-center">
                        <i class="fa-solid fa-bag-shopping text-gray-200 text-5xl mb-4"></i>
                        <p class="text-gray-500 font-medium">Your bag is empty.</p>
                        <a href="{{ route('products') }}" class="mt-6 inline-block text-black font-bold uppercase tracking-widest text-xs border-b-2 border-black pb-1">Start Shopping</a>
                    </li>
                @endforelse
            </ul>
        </section>

        {{-- RIGHT COLUMN: Order Summary --}}
        <section class="mt-16 rounded-2xl bg-white px-6 py-8 shadow-sm border border-gray-100 lg:col-span-5 lg:mt-0">
            <h2 class="text-lg font-bold text-gray-900 uppercase tracking-tight">Summary</h2>

            <div class="mt-6 space-y-4">
                <div class="flex items-center justify-between border-t border-gray-50 pt-6">
                    <dt class="text-sm text-gray-500">Subtotal</dt>
                    <dd class="text-base font-bold text-gray-900">KES {{ number_format($subtotal) }}</dd>
                </div>
                <p class="text-[11px] text-gray-400 italic">Shipping and regional taxes calculated at checkout.</p>
            </div>

            <div class="mt-10 space-y-6">
                <a href="{{ route('checkout') }}"
                   class="w-full flex items-center justify-center gap-3 rounded-xl bg-black px-4 py-5 text-xs font-bold text-white uppercase tracking-[0.2em] shadow-xl hover:bg-gray-800 transition-all">
                    <i class="fas fa-cash-register text-[10px]"></i>
                    <span>Proceed to Checkout</span>
                </a>

                <div class="relative flex items-center justify-center">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-100"></div>
                    </div>
                    <span class="relative bg-white px-4 text-[10px] uppercase tracking-widest text-gray-300 font-bold">or</span>
                </div>

                <div class="text-center">
                    <a href="{{ route('products') }}"
                       class="group inline-flex items-center gap-2 text-xs font-bold text-gray-900 uppercase tracking-widest hover:text-gray-500 transition-colors">
                        <i class="fas fa-arrow-left text-[9px] transform group-hover:-translate-x-1 transition-transform"></i>
                        <span>Continue Shopping</span>
                    </a>
                </div>
            </div>
        </section>
    </div>
</main>

</body>
</html>
