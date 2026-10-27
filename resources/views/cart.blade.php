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
    <title>KituRare Collections | Shopping Bag</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
    </style>

    {{-- ① Livewire styles — required when not using x-app-layout --}}
    @livewireStyles
</head>
<body class="bg-[#FBFBFB] font-inter antialiased text-[#1A1A1A]">

{{-- Sticky Top Bar --}}
<nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 py-5 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between">

            <div class="w-12">
                <a href="{{ route('products') }}"
                   class="text-xs uppercase tracking-widest font-bold hover:text-gray-500 transition">
                    Shop
                </a>
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
    <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-10">Your Bag</h1>

    {{-- ② Drop the Livewire component in place of the old items + summary HTML --}}
    <livewire:cart-page />
</main>

{{-- ③ Livewire scripts — must be last before </body> --}}
@livewireScripts

</body>
</html>
