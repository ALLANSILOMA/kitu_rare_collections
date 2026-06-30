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
    <title>KituRare Collections | Our Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- High-Legibility Inter Font --}}
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

            {{-- Left: Home Link (Symmetry) --}}
            <div class="w-12">
                <a href="/" class="text-xs uppercase tracking-widest font-bold hover:text-gray-500 transition">Home</a>
            </div>

            {{-- Centered Branding --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="flex items-center gap-3">
                    <img src="{{ $logoSrc }}" class="w-10 h-10 object-contain rounded-full shadow-sm">
                    <a href="/" class="pointer-events-auto text-lg font-bold tracking-[0.3em] uppercase hidden sm:block">
                        KituRare Collections
                    </a>
                </div>
            </div>

            {{-- Right: Cart --}}
            <div class="flex items-center">
                <a href="{{ route('cart') }}" class="relative group p-2 text-gray-700 hover:text-black transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    @if($cartCount > 0)
                        <span class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-black text-[10px] font-bold text-white ring-2 ring-white">
                                {{ $cartCount }}
                            </span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 py-12">

    {{-- Search Header --}}
    <div class="flex flex-col md:flex-row items-center justify-between gap-8 mb-16">
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight">The Collection</h1>
            <p class="text-gray-500 text-sm italic">Curated luxury handbags for every occasion.</p>
        </div>

        <form action="{{ route('products') }}" method="GET" class="flex w-full max-w-md shadow-sm rounded-xl overflow-hidden border border-gray-200 bg-white focus-within:ring-1 focus-within:ring-black transition-all">
            <input
                type="text"
                name="search"
                placeholder="Search our collection..."
                value="{{ request('search') }}"
                class="w-full px-5 py-3.5 focus:outline-none text-sm"
            >
            <button type="submit" class="bg-black text-white px-6 hover:bg-gray-800 transition">
                <i class="fas fa-search text-sm"></i>
            </button>
        </form>
    </div>

    @if(request('search'))
        <div class="mb-10 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Showing results for <span class="font-bold text-black italic">"{{ request('search') }}"</span>
            </p>
            <a href="{{ route('products') }}" class="text-xs uppercase tracking-tighter font-bold border-b border-black pb-1">
                Clear Search
            </a>
        </div>
    @endif

    {{-- Product Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-y-12 gap-x-8">
        @forelse($products as $product)
            <div class="group relative flex flex-col bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500">

                {{-- Image Container --}}
                <div class="relative h-80 overflow-hidden bg-[#F3F3F3]">
                    @php
                        $imgs  = is_array($product->images) ? $product->images : [];
                        $thumb = !empty($imgs)
                            ? asset('storage/' . $imgs[0])
                            : asset('images/placeholder.jpg');
                    @endphp
                    <img
                        src="{{ $thumb }}"
                        alt="{{ $product->name }}"
                        class="w-full h-full object-cover transform group-hover:scale-105 transition duration-1000"
                    >

                    @if($product->stock <= 0)
                        <div class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center">
                                <span class="bg-black text-white px-5 py-2 text-[10px] font-bold uppercase tracking-[0.2em] shadow-xl">
                                    Sold Out
                                </span>
                        </div>
                    @endif
                </div>

                {{-- Product Info --}}
                <div class="p-6 flex-1 flex flex-col text-center">
                    <h3 class="text-sm font-semibold text-gray-900 tracking-tight uppercase">{{ $product->name }}</h3>
                    <p class="text-gray-400 mt-2 text-sm font-medium">KES {{ number_format($product->price) }}</p>

                    <div class="mt-6 pt-4 flex flex-col gap-2">
                        <a href="{{ route('products.show', $product->id) }}"
                           class="py-3.5 rounded-lg border border-gray-200 text-[10px] font-bold uppercase tracking-widest hover:bg-gray-50 transition">
                            View Piece
                        </a>

                        @if($product->stock > 0)
                            <form action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit"
                                        class="w-full bg-black text-white py-3.5 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-gray-800 transition shadow-lg">
                                    Add to Bag
                                </button>
                            </form>
                        @else
                            <button disabled class="w-full bg-gray-50 text-gray-300 py-3.5 rounded-lg text-[10px] font-bold uppercase tracking-widest cursor-not-allowed border border-gray-100">
                                Unavailable
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <i class="fas fa-box-open text-gray-200 text-5xl mb-4"></i>
                <p class="text-gray-400 font-light">We couldn't find any bags matching your search.</p>
            </div>
        @endforelse
    </div>
</main>

</body>
</html>
