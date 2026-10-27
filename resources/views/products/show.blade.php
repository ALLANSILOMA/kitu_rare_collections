@php
    $gallery        = is_array($product->images)     ? $product->images     : [];
    $videos         = is_array($product->videos)     ? array_filter($product->videos) : [];
    $variations     = is_array($product->variations) ? $product->variations : [];
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
        .font-inter  { font-family: 'Inter', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .active-ring { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="bg-[#FBFBFB] font-inter antialiased text-[#1A1A1A]">

{{-- ── AJAX toast (shown after add-to-cart without page reload) ── --}}
<div id="cart-toast"
     class="fixed top-0 left-0 w-full z-[100] transform -translate-y-full transition-transform duration-300 ease-in-out">
    <div class="bg-black text-white py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center gap-6 relative">
            <span id="cart-toast-message" class="text-[10px] uppercase tracking-[0.3em]"></span>
            <a href="{{ route('checkout') }}" id="cart-toast-checkout"
               class="hidden inline-flex items-center gap-2 bg-white text-black px-4 py-1.5
                      rounded-full text-[10px] font-bold tracking-[0.2em] hover:bg-gray-100 transition-colors">
                Proceed to Checkout
                <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <button onclick="hideToast()"
                    class="absolute right-4 sm:right-6 lg:right-8 text-white/50 hover:text-white transition-colors text-lg leading-none">
                &times;
            </button>
        </div>
    </div>
</div>

{{-- ── Fallback server-side flash toast (for non-AJAX paths e.g. Buy Now redirect back) ── --}}
@if(session('success'))
    <div id="success-toast"
         class="fixed top-0 left-0 w-full bg-black text-white py-4 text-[10px] uppercase tracking-[0.3em] z-[100] shadow-xl">
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
        setTimeout(() => { document.getElementById('success-toast')?.remove(); }, 6000);
    </script>
@endif

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Breadcrumbs --}}
    <nav class="flex text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-10">
        <a href="{{ route('home') }}"     class="hover:text-black transition">Home</a>
        <span class="mx-3">/</span>
        <a href="{{ route('products') }}" class="hover:text-black transition">Shop</a>
        <span class="mx-3">/</span>
        <span class="text-black">{{ $product->name }}</span>
    </nav>

    {{-- ── 2-column grid ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">

        {{-- ════════════════════════════════════════════════════════════════
             LEFT COLUMN — images then videos, all inside the same column
             ════════════════════════════════════════════════════════════════ --}}
        <div class="space-y-6">

            {{-- Image gallery --}}
            <div class="flex flex-col md:flex-row-reverse gap-4">

                {{-- Main image --}}
                <div class="flex-1 bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
                    <img id="mainImage"
                         src="{{ !empty($gallery) ? asset('storage/' . $gallery[0]) : asset('images/placeholder.jpg') }}"
                         alt="{{ $product->name }}"
                         class="w-full h-auto min-h-[500px] object-cover transition duration-700 hover:scale-105">
                </div>

                {{-- Thumbnails --}}
                @if(count($gallery) > 1)
                    <div class="flex md:flex-col gap-3 overflow-x-auto no-scrollbar md:overflow-visible">
                        @foreach($gallery as $img)
                            <button onclick="changeImage('{{ asset('storage/' . $img) }}')"
                                    class="w-20 h-24 flex-shrink-0 border border-gray-100 rounded-lg overflow-hidden hover:border-black transition-all">
                                <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Videos — inside the left column, below the image gallery --}}
            @if(!empty($videos))
                <div class="space-y-3">
                    <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-400">Product Videos</p>
                    <div class="flex flex-col gap-4">
                        @foreach($videos as $video)
                            <div class="rounded-2xl overflow-hidden border border-gray-100 shadow-sm bg-black">
                                <video controls preload="metadata" playsinline
                                       class="w-full max-h-[480px] object-contain">
                                    <source src="{{ asset('storage/' . $video) }}" type="video/mp4">
                                    <source src="{{ asset('storage/' . $video) }}" type="video/webm">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>{{-- end left column --}}

        {{-- ════════════════════════════════════════════════════════════════
             RIGHT COLUMN — product details
             ════════════════════════════════════════════════════════════════ --}}
        <div class="lg:sticky lg:top-10">

            {{-- Name + Price --}}
            <div class="border-b border-gray-100 pb-8">
                <h1 class="text-4xl font-bold tracking-tight mb-4 uppercase leading-tight">
                    {{ $product->name }}
                </h1>
                <div class="text-2xl font-light text-gray-900">
                    KES {{ number_format($product->price) }}
                </div>
            </div>

            {{-- Description --}}
            <div class="py-8 prose prose-neutral prose-sm max-w-none text-gray-600 leading-relaxed">
                {!! $product->description !!}
            </div>

            <div class="space-y-8">

                {{-- Variations (Color) --}}
                @if($colorVariation && !empty($colorVariation['options']))
                    <div class="space-y-4 py-6 border-t border-gray-100">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-400">Select Color</span>
                            <span id="selected-color-label" class="text-[10px] uppercase tracking-[0.2em] font-bold text-black"></span>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @foreach($colorVariation['options'] as $option)
                                @php $hasHex = !empty(trim($option['hex'] ?? '')); @endphp
                                <button type="button"
                                        class="variation-btn group relative"
                                        data-label="{{ $option['label'] }}"
                                        data-image="{{ !empty($option['image']) ? asset('storage/' . $option['image']) : '' }}"
                                        onclick="selectColor(this, '{{ $option['label'] }}')">
                                    @if($hasHex)
                                        <span class="active-ring w-10 h-10 rounded-full border-2 border-transparent group-hover:border-gray-300 flex items-center justify-center transition-all">
                                            <span class="w-8 h-8 rounded-full border border-gray-200 shadow-inner"
                                                  style="background-color: {{ trim($option['hex']) }};"></span>
                                        </span>
                                    @else
                                        <span class="active-ring inline-flex items-center px-3 py-1.5 rounded-full border-2 border-gray-200 text-[10px] font-bold uppercase tracking-widest text-gray-600 bg-white group-hover:border-gray-400 transition-all whitespace-nowrap">
                                            {{ $option['label'] }}
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        <p id="variation-error" class="text-[9px] uppercase tracking-widest text-red-500 font-bold hidden">
                            <i class="fas fa-exclamation-circle mr-1"></i> Please select a color before proceeding
                        </p>
                    </div>
                @endif

                {{-- Quantity --}}
                <div>
                    <span class="text-[10px] uppercase tracking-widest font-bold text-gray-400">Quantity</span>
                    <div class="flex items-center rounded-xl border border-gray-200 w-fit mt-3 bg-white shadow-sm overflow-hidden">
                        <button type="button" onclick="decreaseQty()" class="px-5 py-3 hover:bg-gray-50 transition">−</button>
                        <input id="qty" value="1" readonly class="w-12 text-center text-sm font-bold bg-transparent">
                        <button type="button" onclick="increaseQty()" class="px-5 py-3 hover:bg-gray-50 transition">+</button>
                    </div>
                </div>

                {{-- Action forms --}}
                <div class="grid grid-cols-1 gap-4">

                    {{-- Add to Bag — AJAX intercepted via id="add-to-bag-form" --}}
                    <form id="add-to-bag-form"
                          action="{{ route('cart.add') }}"
                          method="POST"
                          class="m-0"
                          data-has-colors="{{ $colorVariation ? 'true' : 'false' }}">
                        @csrf
                        <input type="hidden" name="product_id"       value="{{ $product->id }}">
                        <input type="hidden" name="quantity"          class="qty-input" value="1">
                        <input type="hidden" name="color_selection"   class="color-selection-input">
                        <button type="submit"
                                class="w-full bg-black text-white py-5 rounded-xl text-[10px] uppercase tracking-[0.3em] font-bold hover:bg-gray-800 transition-all shadow-lg">
                            Add to Bag
                        </button>
                    </form>

                    {{-- Buy It Now — regular full-page submit, no AJAX --}}
                    <form action="{{ route('buy.now') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="product_id"     value="{{ $product->id }}">
                        <input type="hidden" name="quantity"        class="qty-input" value="1">
                        <input type="hidden" name="color_selection" class="color-selection-input">
                        <button type="submit"
                                class="w-full border-2 border-black py-5 rounded-xl text-[10px] uppercase tracking-[0.3em] font-bold hover:bg-black hover:text-white transition-all">
                            <i class="fas fa-credit-card text-xs mr-2"></i> Buy it Now
                        </button>
                    </form>
                </div>

                {{-- Value props --}}
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

                {{-- Shipping accordion --}}
                <details class="group py-4 border-b border-gray-100">
                    <summary class="flex justify-between items-center cursor-pointer list-none font-bold uppercase tracking-widest text-[11px]">
                        Shipping & Returns
                        <i class="fas fa-chevron-down text-[9px] transition-transform duration-300 group-open:rotate-180"></i>
                    </summary>
                    <div class="mt-6 space-y-6 text-sm text-gray-600 pr-4">
                        <section>
                            <h4 class="text-black font-bold uppercase text-[10px] tracking-widest mb-2">Delivery Turnaround</h4>
                            <p>All orders within Nairobi and its environs will be delivered within 24–48 hours. Orders outside Nairobi but within Kenya within 2–3 business days. International orders within 3–5 business days. We will be in touch before dispatch.</p>
                        </section>
                        <section>
                            <h4 class="text-black font-bold uppercase text-[10px] tracking-widest mb-2">Rates & Fees</h4>
                            <p>Calculated based on your shipping zone — final totals shown at checkout.</p>
                        </section>
                        <section>
                            <h4 class="text-black font-bold uppercase text-[10px] tracking-widest mb-2">Back Orders</h4>
                            <p>Available items ship immediately. If an item is on back order we ship the in-stock portion first; the remainder follows at no extra shipping cost.</p>
                        </section>
                    </div>
                </details>

            </div>
        </div>{{-- end right column --}}

    </div>{{-- end grid --}}
</div>

<script>
    // ── Toast helpers ─────────────────────────────────────────────────────────
    let toastTimer;
    function showToast(message, showCheckout = false) {
        const toast    = document.getElementById('cart-toast');
        const msg      = document.getElementById('cart-toast-message');
        const checkout = document.getElementById('cart-toast-checkout');
        msg.textContent = message;
        checkout.classList.toggle('hidden', !showCheckout);
        toast.classList.remove('-translate-y-full');
        toast.classList.add('translate-y-0');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(hideToast, showCheckout ? 6000 : 3500);
    }
    function hideToast() {
        const toast = document.getElementById('cart-toast');
        toast.classList.remove('translate-y-0');
        toast.classList.add('-translate-y-full');
    }

    // ── Cart badge ────────────────────────────────────────────────────────────
    function updateCartBadge(count) {
        let badge = document.getElementById('cart-badge');
        if (!badge) {
            const cartLink = document.querySelector('a[href*="cart"]');
            if (cartLink) {
                cartLink.style.position = 'relative';
                badge = document.createElement('span');
                badge.id = 'cart-badge';
                badge.className = 'absolute top-1 right-1 block h-2.5 w-2.5 rounded-full bg-black ring-2 ring-white';
                cartLink.appendChild(badge);
            }
        }
        if (badge) badge.style.display = count > 0 ? 'block' : 'none';
    }

    // ── Gallery ───────────────────────────────────────────────────────────────
    function changeImage(src) {
        const main = document.getElementById('mainImage');
        main.style.opacity = 0;
        setTimeout(() => { main.src = src; main.style.opacity = 1; }, 200);
    }

    // ── Quantity ──────────────────────────────────────────────────────────────
    function updateForms() {
        const val = document.getElementById('qty').value;
        document.querySelectorAll('.qty-input').forEach(i => i.value = val);
    }
    function increaseQty() {
        const qty = document.getElementById('qty');
        qty.value = parseInt(qty.value) + 1;
        updateForms();
    }
    function decreaseQty() {
        const qty = document.getElementById('qty');
        if (parseInt(qty.value) > 1) { qty.value = parseInt(qty.value) - 1; updateForms(); }
    }

    // ── Color selection ───────────────────────────────────────────────────────
    function selectColor(element, label) {
        document.getElementById('selected-color-label').innerText = label;
        document.querySelectorAll('.color-selection-input').forEach(i => i.value = label);
        document.querySelectorAll('.active-ring').forEach(r => {
            r.classList.remove('border-black');
            r.classList.add('border-transparent');
        });
        element.querySelector('.active-ring').classList.replace('border-transparent', 'border-black');
        const img = element.getAttribute('data-image');
        if (img) changeImage(img);
        document.getElementById('variation-error')?.classList.add('hidden');
    }

    // ── AJAX Add to Bag ───────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('add-to-bag-form');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const hasColors = form.dataset.hasColors === 'true';
            const selection = form.querySelector('.color-selection-input')?.value;

            if (hasColors && !selection) {
                const err = document.getElementById('variation-error');
                err?.classList.remove('hidden');
                err?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            const btn          = form.querySelector('button[type="submit"]');
            const originalHTML = btn.innerHTML;
            btn.disabled       = true;
            btn.innerHTML      = '<span class="opacity-50">Adding…</span>';

            try {
                const res  = await fetch('{{ route("cart.ajax.add") }}', {
                    method:  'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body:    new FormData(form),
                });
                const data = await res.json();
                showToast(data.message, data.success);
                if (data.success) updateCartBadge(data.cart_count);
            } catch {
                showToast('Something went wrong. Please try again.');
            } finally {
                btn.disabled  = false;
                btn.innerHTML = originalHTML;
            }
        });
    });
</script>

</body>
</html>
