<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KituRare Collections | Cart Details</title>
    <script src="https://cdn.tailwindcss.com"></script>


</head>
<div>
    <button command="show-modal" commandfor="drawer" class="relative p-2">
        Cart
        @if(count($cartItems) > 0)
            <span class="absolute top-0 right-0 bg-red-500 text-white rounded-full px-1.5 text-xs">
                {{ count($cartItems) }}
            </span>
        @endif
    </button>

    <el-dialog>
        <dialog id="drawer" class="...">
            <ul role="list" class="-my-6 divide-y divide-gray-200">
                @forelse($cartItems as $id => $product)
                    <li class="flex py-6">
                        <div class="size-24 shrink-0 overflow-hidden rounded-md border border-gray-200">
                            <img src="{{ $product['image'] ?? asset('storage/product-images/products/' . $product->image) }}" class="size-full object-cover" />
                        </div>

                        <div class="ml-4 flex flex-1 flex-col">
                            <div class="flex justify-between text-base font-medium text-gray-900">
                                <h3>{{ $product['name'] }}</h3>
                                <p class="ml-4">KES{{ number_format($product['price'], 2) }}</p>
                            </div>
                            <div class="flex flex-1 items-end justify-between text-sm">
                                <p class="text-gray-500">Qty {{ $product['quantity'] }}</p>
                                <div class="flex">
                                    <button wire:click="removeItem({{ $id }})" type="button" class="font-medium text-indigo-600 hover:text-indigo-500">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                    <p class="py-6 text-gray-500">Your cart is empty.</p>
                @endforelse
            </ul>

            <div class="border-t border-gray-200 px-4 py-6">
                <div class="flex justify-between text-base font-medium text-gray-900">
                    <p>Subtotal</p>
                    <p>KES{{ number_format($subtotal, 2) }}</p>
                </div>
                <div class="mt-6">
                    <a href="{{ route('cart.checkout') }}" class="flex items-center justify-center rounded-md bg-indigo-600 px-6 py-3 text-white">
                        Checkout
                    </a>
                </div>
            </div>
        </dialog>
    </el-dialog>
</div>

</html>
