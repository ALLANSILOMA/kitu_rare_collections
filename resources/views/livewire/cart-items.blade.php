{{-- resources/views/livewire/cart-items.blade.php --}}
<div>
    <ul role="list" class="divide-y divide-gray-100 border-t border-b border-gray-100">
        @forelse($this->cart as $cartKey => $item)
            <li class="flex py-8 sm:py-10" wire:key="{{ $cartKey }}">

                <div class="shrink-0">
                    <div class="size-32 sm:size-48 rounded-xl overflow-hidden bg-gray-100 shadow-sm border border-gray-50">
                        @if(!empty($item['thumbnail']))
                            <img src="{{ asset('storage/' . $item['thumbnail']) }}"
                                 alt="{{ $item['name'] }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-image text-gray-200 text-2xl"></i>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ml-6 flex flex-1 flex-col justify-between">
                    <div class="relative pr-9 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:pr-0">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-tight">
                                {{ $item['name'] }}
                            </h3>
                            @if(!empty($item['variation_label']))
                                <p class="mt-1 text-[11px] text-gray-400 uppercase tracking-widest">
                                    {{ $item['variation_label'] }}
                                </p>
                            @endif
                            <p class="mt-2 text-sm font-semibold text-gray-500">
                                KES {{ number_format($item['price']) }}
                            </p>
                        </div>

                        {{-- Quantity controls — wire:click, no page reload --}}
                        <div class="mt-4 sm:mt-0">
                            <div class="flex items-center rounded-lg border border-gray-200 w-fit bg-white overflow-hidden shadow-sm">
                                <button wire:click="updateQuantity('{{ $cartKey }}', {{ $item['quantity'] - 1 }})"
                                        wire:loading.attr="disabled"
                                        class="px-3 py-1.5 text-gray-400 hover:text-black transition">−</button>
                                <span class="px-3 py-1.5 text-xs font-bold w-10 text-center border-x border-gray-100">
                                    {{ $item['quantity'] }}
                                </span>
                                <button wire:click="updateQuantity('{{ $cartKey }}', {{ $item['quantity'] + 1 }})"
                                        wire:loading.attr="disabled"
                                        class="px-3 py-1.5 text-gray-400 hover:text-black transition">+</button>
                            </div>

                            <div class="absolute right-0 top-0">
                                <button wire:click="removeFromCart('{{ $cartKey }}')"
                                        class="text-gray-300 hover:text-red-500 transition p-1">
                                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    @php $stock = $item['stock'] ?? 99; $qty = $item['quantity']; @endphp
                    <p class="mt-4 flex items-center text-sm font-medium">
                        @if($qty < $stock)
                            <i class="fa-solid fa-check text-green-500 mr-2"></i>
                            <span class="text-gray-700">In stock & ready to ship</span>
                        @elseif($qty == $stock)
                            <i class="fa-solid fa-circle-exclamation text-amber-500 mr-2"></i>
                            <span class="text-amber-700 text-xs uppercase font-semibold">Last items</span>
                        @else
                            <i class="fa-solid fa-triangle-exclamation text-red-500 mr-2"></i>
                            <span class="text-red-600 text-xs uppercase font-bold">Only {{ $stock }} available</span>
                        @endif
                    </p>
                </div>
            </li>
        @empty
            <li class="py-20 text-center">
                <i class="fa-solid fa-bag-shopping text-gray-200 text-5xl mb-4 block"></i>
                <p class="text-gray-500 font-medium">Your bag is empty.</p>
                <a href="{{ route('products') }}"
                   class="mt-6 inline-block text-black font-bold uppercase tracking-widest
                          text-[10px] border-b-2 border-black pb-1">
                    Start Shopping
                </a>
            </li>
        @endforelse
    </ul>

    {{-- Live subtotal --}}
    @if(count($this->cart) > 0)
        <div class="mt-6 flex justify-between items-center">
            <span class="text-sm text-gray-500">Subtotal</span>
            <span class="text-base font-bold">KES {{ number_format($this->subtotal) }}</span>
        </div>
    @endif
</div>
