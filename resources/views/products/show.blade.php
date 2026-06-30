@php
    // Gallery processing
    $gallery = is_array($product->images) ? $product->images : [];

    // Variations processing (from your Filament JSON Repeater)
    $variations = is_array($product->variations) ? $product->variations : [];

    // Filter specifically for the "Color" variation type
    $colorVariation = collect($variations)->firstWhere('type', 'Color');
@endphp

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KituRare Collections | {{ $product->name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .active-ring { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>

<body class="bg-[#FBFBFB] font-inter antialiased text-[#1A1A1A]">

{{-- Notification Toast --}}
@if(session('success'))
    <div id="success-toast" class="fixed top-0 left-0 w-full bg-black text-white py-4 text-[10px] uppercase tracking-[0.3em] z-[100] shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center gap-6 relative">
            <span>{{ session('success') }}</span>

            @if(str_contains(strtolower(session('success')), 'added to bag'))
                <a href="{{ route('checkout') }}"
                   class="inline-flex items-center gap-2 bg-white text-black px-4 py-1.5 rounded-full font-bold tracking-[0.2em] hover:bg-gray-100 transition-colors">
                    Proceed to Checkout
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endif

            <button onclick="document.getElementById('success-toast').style.display='none'"
                    class="absolute right-4 sm:right-6 lg:right-8 text-white/50 hover:text-white transition-colors normal-case tracking-normal text-sm">
                &times;
            </button>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('success-toast');
            if (toast) toast.style.display = 'none';
        }, 6000);
    </script>
@endif

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Breadcrumbs --}}
    <nav class="flex text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-10">
        <a href="{{ route('home') }}" class="hover:text-black transition">Home</a>
        <span class="mx-3">/</span>
        <a href="{{ route('products') }}" class="hover:text-black transition">Shop</a>
        <span class="mx-3">/</span>
        <span class="text-black">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">

        {{-- Left: Media Gallery --}}
        <div class="flex flex-col md:flex-row-reverse gap-4">
            <div class="flex-1 bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
                <img id="mainImage"
                     src="{{ !empty($gallery) ? asset('storage/' . $gallery[0]) : asset('images/placeholder.jpg') }}"
                     alt="{{ $product->name }}"
                     class="w-full h-auto min-h-[500px] object-cover transition duration-700 hover:scale-105">
            </div>

            <div class="flex md:flex-col gap-3 overflow-x-auto no-scrollbar md:overflow-visible">
                @foreach($gallery as $img)
                    <button onclick="changeImage('{{ asset('storage/' . $img) }}')"
                            class="w-20 h-24 flex-shrink-0 border border-gray-100 rounded-lg overflow-hidden hover:border-black transition-all">
                        <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover">
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Right: Product Details --}}
        <div class="lg:sticky lg:top-10">
            <div class="border-b border-gray-100 pb-8">
                <h1 class="text-4xl font-bold tracking-tight mb-4 uppercase leading-tight">
                    {{ $product->name }}
                </h1>
                <div class="text-2xl font-light text-gray-900">
                    KES {{ number_format($product->price) }}
                </div>
            </div>

            <div class="py-8 prose prose-neutral prose-sm max-w-none text-gray-600 leading-relaxed">
                {!! $product->description !!}
            </div>

            <div class="space-y-8">

                {{-- Variation Section (Color Only) --}}
                @if($colorVariation && isset($colorVariation['options']))
                    <div class="space-y-6 py-6 border-t border-gray-100">
                        <div class="flex justify-between items-center">
                        <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-400">
                            Select Color
                        </span>
                            <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-black" id="selected-color-label">
                            {{-- JS Injected --}}
                        </span>
                        </div>

                        <div class="flex flex-wrap gap-4">
                            @foreach($colorVariation['options'] as $option)
                                <button
                                    type="button"
                                    class="variation-btn group relative"
                                    data-label="{{ $option['label'] }}"
                                    data-image="{{ !empty($option['image']) ? asset('storage/' . $option['image']) : '' }}"
                                    onclick="selectColor(this, '{{ $option['label'] }}')"
                                >
                                <span class="active-ring w-10 h-10 rounded-full border-2 border-transparent group-hover:border-gray-200 flex items-center justify-center">
                                   <span class="w-8 h-8 rounded-full border border-gray-100 shadow-inner"
                                         style="background-color: {{ $option['hex'] ?? '#E5E7EB' }};">
                                   </span>
                                </span>
                                </button>
                            @endforeach
                        </div>
                        <p id="variation-error" class="text-[9px] uppercase tracking-widest text-red-500 font-bold hidden">
                            <i class="fas fa-exclamation-circle mr-1"></i> Please select a color before proceeding
                        </p>
                    </div>
                @endif

                {{-- Quantity Selector --}}
                <div>
                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400">Quantity</span>
                    <div class="flex items-center rounded-xl border border-gray-200 w-fit mt-3 bg-white shadow-sm overflow-hidden">
                        <button type="button" onclick="decreaseQty()" class="px-5 py-3 hover:bg-gray-50 transition">-</button>
                        <input id="qty" value="1" readonly class="w-12 text-center text-sm font-bold bg-transparent">
                        <button type="button" onclick="increaseQty()" class="px-5 py-3 hover:bg-gray-50 transition">+</button>
                    </div>
                </div>

                {{-- Action Forms --}}
                <div class="grid grid-cols-1 gap-4">
                    <form action="{{ route('cart.add') }}" method="POST" class="m-0 product-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" class="qty-input" value="1">
                        <input type="hidden" name="color_selection" class="color-selection-input">

                        <button type="submit" class="w-full bg-black text-white py-5 rounded-xl text-[10px] uppercase tracking-[0.3em] font-bold hover:bg-gray-800 transition-all shadow-lg">
                            Add to Bag
                        </button>
                    </form>

                    <form action="{{ route('buy.now') }}" method="POST" class="m-0 product-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" class="qty-input" value="1">
                        <input type="hidden" name="color_selection" class="color-selection-input">

                        <button type="submit" class="w-full border-2 border-black py-5 rounded-xl text-[10px] uppercase tracking-[0.3em] font-bold hover:bg-black hover:text-white transition-all">
                            <i class="fas fa-credit-card text-xs mr-2"></i> Buy it Now
                        </button>
                    </form>
                </div>

                {{-- Value Props --}}
                <div class="grid grid-cols-3 py-6 border-y border-gray-50 text-center">
                    <div class="text-[10px] uppercase tracking-widest font-bold text-gray-500">
                        <i class="fa-solid fa-truck-fast block text-lg text-black mb-2"></i> Express Shipping
                    </div>
                    <div class="text-[10px] uppercase tracking-widest font-bold text-gray-500">
                        <i class="fa-solid fa-rotate-left block text-lg text-black mb-2"></i> Easy Returns
                    </div>
                    <div class="text-[10px] uppercase tracking-widest font-bold text-gray-500">
                        <i class="fa-solid fa-shield-halved block text-lg text-black mb-2"></i> Secure Pay
                    </div>
                </div>

                <details class="group py-4 border-b border-gray-100">
                    <summary class="flex justify-between items-center cursor-pointer list-none font-bold uppercase tracking-widest text-[11px]">
                        Shipping & Returns
                        <i class="fas fa-chevron-down text-[9px] transition-transform duration-300"></i>
                    </summary>
                    <div class="mt-6 space-y-6 text-sm text-gray-600 pr-4">
                        <section>
                            <h4 class="text-black font-bold uppercase text-[10px] tracking-widest mb-2">Delivery Turnaround</h4>
                            <p>All orders within Nairobi and it's environs will be delivered within 24-48 hours. Orders outside Nairobi but within Kenya will be delivered within 2-3 business days. International orders are shipped within 3-5 business days. You will receive official communication from us before dispatch regarding your package </p>
                        </section>
                        <section>
                            <h4 class="text-black font-bold uppercase text-[10px] tracking-widest mb-2">Rates & Fees</h4>
                            <p>Calculated based on your specific shipping zone. View final totals at the secure checkout page.</p>
                        </section>
                        <section>
                            <h4 class="text-black font-bold uppercase text-[10px] tracking-widest mb-2">Back Orders</h4>
                            <p>Available items ship immediately; If an item goes on back order we will ship you the part of your order that is in stock. When the item becomes available we will ship you the rest of your order. You will not be charged any additional shipping and handling for the second shipment.</p>
                        </section>
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Gallery Logic
    function changeImage(src) {
        const main = document.getElementById('mainImage');
        main.style.opacity = 0;
        setTimeout(() => {
            main.src = src;
            main.style.opacity = 1;
        }, 200);
    }

    // 2. Quantity Logic
    function updateForms() {
        const currentQty = document.getElementById('qty').value;
        document.querySelectorAll('.qty-input').forEach(input => input.value = currentQty);
    }

    function increaseQty() {
        let qty = document.getElementById('qty');
        qty.value = parseInt(qty.value) + 1;
        updateForms();
    }

    function decreaseQty() {
        let qty = document.getElementById('qty');
        if (parseInt(qty.value) > 1) {
            qty.value = parseInt(qty.value) - 1;
            updateForms();
        }
    }

    // 3. Color Selection Logic
    function selectColor(element, label) {
        // Update visual label
        document.getElementById('selected-color-label').innerText = label;

        // Update hidden inputs in both forms
        document.querySelectorAll('.color-selection-input').forEach(input => input.value = label);

        // Update UI Rings
        document.querySelectorAll('.active-ring').forEach(ring => {
            ring.classList.remove('border-black');
            ring.classList.add('border-transparent');
        });
        element.querySelector('.active-ring').classList.replace('border-transparent', 'border-black');

        // Optional: Swap image if variation has one
        const variationImg = element.getAttribute('data-image');
        if (variationImg) changeImage(variationImg);

        // Hide error
        document.getElementById('variation-error').classList.add('hidden');
    }

    // 4. Form Validation
    document.querySelectorAll('.product-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const hasColors = {{ ($colorVariation) ? 'true' : 'false' }};
            const selection = form.querySelector('.color-selection-input').value;

            if (hasColors && !selection) {
                e.preventDefault();
                const error = document.getElementById('variation-error');
                error.classList.remove('hidden');
                error.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
</script>
</body>
</html>
