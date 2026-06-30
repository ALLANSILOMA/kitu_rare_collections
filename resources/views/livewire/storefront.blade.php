<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} | KituRare Collections</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900">

<nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">

            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" class="text-2xl font-bold tracking-[0.2em] uppercase">
                    KituRare
                </a>
            </div>

            <div class="flex items-center space-x-8">
                <a href="{{ route('products') }}" class="text-xs uppercase tracking-widest font-semibold hover:text-gray-500 transition">Shop</a>

                <a href="{{ route('cart') }}" class="group relative p-2">
                    <i class="fa-solid fa-bag-shopping text-xl transition group-hover:scale-110"></i>

                    @php
                        // Counts unique items in the session 'cart' array
                        $displayCount = session()->has('cart') ? count(session('cart')) : 0;
                    @endphp

                    @if($displayCount > 0)
                        <span class="absolute top-0 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-black text-[9px] font-bold text-white ring-2 ring-white">
                                {{ $displayCount }}
                            </span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 py-12">
    <nav class="text-xs uppercase tracking-widest text-gray-400 mb-10">
        <a href="{{ route('home') }}" class="hover:text-black">Home</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products') }}" class="hover:text-black">Shop</a>
        <span class="mx-2">/</span>
        <span class="text-black font-bold">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">

        <div class="flex flex-col md:flex-row gap-4">
            @php $images = is_array($product->images) ? $product->images : []; @endphp

            <div class="flex md:flex-col gap-3 order-2 md:order-1">
                @foreach($images as $img)
                    <div class="w-20 h-20 border border-gray-100 overflow-hidden cursor-pointer hover:border-black transition">
                        <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover" onclick="changeImage('{{ asset('storage/' . $img) }}')">
                    </div>
                @endforeach
            </div>

            <div class="flex-1 bg-gray-50 order-1 md:order-2 overflow-hidden aspect-[4/5]">
                <img id="mainImage" src="{{ !empty($gallery) ? asset('storage/' . $gallery[0]) : asset('images/placeholder.jpg') }}" class="w-full h-full object-cover transition duration-700">
            </div>
        </div>

        <div class="flex flex-col">
            <div class="border-b border-gray-100 pb-6 mb-8">
                <h1 class="text-4xl font-light tracking-tight mb-4 uppercase">{{ $product->name }}</h1>
                <p class="text-2xl font-medium text-gray-900">KES {{ number_format($product->price) }}</p>
            </div>

            <div class="prose prose-sm text-gray-500 mb-10">{!! $product->description !!}</div>

            <div class="space-y-6">
                <form action="{{ route('cart.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" id="cartQty" value="1">

                    <div class="mb-6">
                        <label class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-400 block mb-3">Quantity</label>
                        <div class="flex items-center border border-gray-200 w-fit">
                            <button type="button" onclick="decreaseQty()" class="px-5 py-3 hover:bg-gray-50">-</button>
                            <input id="qty" type="number" value="1" class="w-14 text-center border-x border-gray-200 focus:outline-none" onchange="syncQty()">
                            <button type="button" onclick="increaseQty()" class="px-5 py-3 hover:bg-gray-50">+</button>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-black text-white py-5 uppercase text-xs tracking-[0.3em] font-bold hover:bg-gray-900 transition shadow-xl">
                        Add to Bag
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    function changeImage(src) { document.getElementById('mainImage').src = src; }
    function increaseQty() { let q = document.getElementById('qty'); q.value = parseInt(q.value) + 1; syncQty(); }
    function decreaseQty() { let q = document.getElementById('qty'); if (parseInt(q.value) > 1) { q.value = parseInt(q.value) - 1; syncQty(); } }
    function syncQty() { document.getElementById('cartQty').value = document.getElementById('qty').value; }
    window.onload = syncQty;
</script>
</body>
</html>
