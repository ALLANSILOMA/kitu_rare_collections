<div>
    {{-- Error flash --}}
    <div x-data="{ show: false, message: '' }"
         x-on:show-error.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 3500)"
         x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-end="opacity-0"
         class="mb-6 bg-red-50 border border-red-200 rounded-xl px-5 py-3 text-xs text-red-600 font-medium"
         style="display:none">
        <span x-text="message"></span>
    </div>

    @if(empty($cart))
        {{-- Empty state --}}
        <div class="text-center py-24 space-y-4">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold tracking-tight">Your bag is empty</h2>
            <p class="text-sm text-gray-400">Add some pieces you love and they'll appear here.</p>
            <a href="{{ route('products') }}"
               class="inline-flex items-center gap-2 bg-black text-white px-6 py-3 rounded-xl text-[10px] font-bold uppercase tracking-[0.2em] hover:bg-gray-800 transition mt-2">
                Browse Products
            </a>
        </div>

    @else
        <div class="flex flex-col lg:flex-row gap-12 items-start">

            {{-- LEFT: Cart Items --}}
            <div class="w-full lg:w-[60%] space-y-4">
                @foreach($cart as $key => $item)
                    <div wire:key="{{ $key }}"
                         class="flex items-center gap-5 bg-white border border-gray-100 rounded-2xl p-5 shadow-sm group">

                        {{-- Thumbnail --}}
                        <div class="w-20 h-20 flex-shrink-0 bg-gray-50 border border-gray-100 rounded-xl overflow-hidden">
                            <img src="{{ asset('storage/' . ($item['thumbnail'] ?? '')) }}"
                                 alt="{{ $item['name'] }}"
                                 class="w-full h-full object-cover">
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold uppercase tracking-tight truncate">{{ $item['name'] }}</p>
                            @if(!empty($item['variation_label']))
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-0.5">{{ $item['variation_label'] }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-0.5">
                                KES {{ number_format($item['price']) }} each
                            </p>

                            {{-- Quantity stepper --}}
                            <div class="flex items-center gap-0 mt-3 w-fit border border-gray-200 rounded-lg overflow-hidden bg-white">
                                <button wire:click="decrement('{{ $key }}')"
                                        wire:loading.attr="disabled"
                                        class="px-3 py-2 text-gray-500 hover:bg-gray-50 hover:text-black transition text-sm font-bold disabled:opacity-40">
                                    −
                                </button>
                                <span class="px-4 py-2 text-sm font-bold border-x border-gray-200 min-w-[2.5rem] text-center"
                                      wire:loading.class="opacity-40">
                                    {{ $item['quantity'] }}
                                </span>
                                <button wire:click="increment('{{ $key }}')"
                                        wire:loading.attr="disabled"
                                        class="px-3 py-2 text-gray-500 hover:bg-gray-50 hover:text-black transition text-sm font-bold disabled:opacity-40">
                                    +
                                </button>
                            </div>
                        </div>

                        {{-- Line total + remove --}}
                        <div class="text-right flex-shrink-0 space-y-3">
                            <p class="text-sm font-bold">
                                KES {{ number_format($item['price'] * $item['quantity']) }}
                            </p>
                            <button wire:click="remove('{{ $key }}')"
                                    wire:confirm="Remove this item from your bag?"
                                    class="text-[10px] uppercase tracking-widest text-gray-300 hover:text-red-500 transition font-bold block ml-auto">
                                Remove
                            </button>
                        </div>

                        {{-- Loading overlay per row --}}
                        <div wire:loading wire:target="increment('{{ $key }}'), decrement('{{ $key }}'), remove('{{ $key }}')"
                             class="absolute inset-0 bg-white/60 rounded-2xl flex items-center justify-center">
                            <svg class="animate-spin h-4 w-4 text-black" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- RIGHT: Order Summary --}}
            <div class="w-full lg:w-[40%] lg:sticky lg:top-8">
                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-8 space-y-6">
                    <h2 class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-500">Order Summary</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal ({{ collect($cart)->sum('quantity') }} items)</span>
                            <span class="font-semibold text-gray-900">KES {{ number_format($subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-400 text-xs">
                            <span>Shipping</span>
                            <span>Calculated at checkout</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-5 flex justify-between items-center">
                        <span class="text-xs uppercase tracking-[0.2em] text-gray-400 font-bold">Total</span>
                        <div class="text-right">
                            <span class="text-[10px] text-gray-400 mr-1 align-middle uppercase font-bold">KES</span>
                            <span class="text-2xl font-bold tracking-tighter">{{ number_format($subtotal) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout') }}"
                       class="w-full bg-black text-white font-bold py-4 rounded-xl text-[10px] uppercase tracking-[0.25em] hover:bg-gray-800 transition flex items-center justify-center gap-2 shadow-sm">
                        Proceed to Checkout
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="{{ route('products') }}"
                       class="block text-center text-[10px] uppercase tracking-widest text-gray-400 hover:text-black transition font-bold">
                        ← Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
