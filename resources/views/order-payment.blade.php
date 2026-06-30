@php
    $logoPath = public_path('apple-icon-180x180.png');
    $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
    $logoSrc = 'data:image/png;base64,' . $logoData;
@endphp

<x-app-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }

        .step-line::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 100%;
            transform: translateX(-50%);
            width: 1px;
            height: 20px;
            background: #e5e7eb;
        }

        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(0,0,0,0.12); }
            70%  { box-shadow: 0 0 0 8px rgba(0,0,0,0); }
            100% { box-shadow: 0 0 0 0 rgba(0,0,0,0); }
        }
        .pulse-badge { animation: pulse-ring 2.2s infinite; }
    </style>

    <div class="bg-[#FBFBFB] min-h-screen font-inter antialiased text-[#1A1A1A]">

        {{-- ── Top Bar (matches checkout page exactly) ── --}}
        <nav class="bg-white border-b border-gray-100 py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative flex items-center justify-between">

                    {{-- Left: breadcrumb --}}
                    <div class="w-28">
                        <a href="{{ route('checkout') }}"
                           class="text-[10px] uppercase tracking-widest font-bold text-gray-400 hover:text-gray-700 transition flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Checkout
                        </a>
                    </div>

                    {{-- Centre: Logo + Brand --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <a href="/" class="pointer-events-auto flex items-center gap-4 group">
                            <img src="{{ $logoSrc }}" alt="Logo"
                                 class="w-10 h-10 object-contain rounded-full shadow-sm transition-transform group-hover:scale-105">
                            <span class="text-lg font-bold tracking-[0.3em] uppercase text-gray-900 hidden sm:block">
                                KituRare Collections
                            </span>
                        </a>
                    </div>

                    {{-- Right: lock icon (reassurance) --}}
                    <div class="w-28 flex justify-end">
                        <div class="flex items-center gap-1.5 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span class="text-[10px] uppercase tracking-widest font-bold hidden sm:block">Secure</span>
                        </div>
                    </div>

                </div>
            </div>
        </nav>

        {{-- ── Progress Breadcrumb ── --}}
        <div class="border-b border-gray-100 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-center gap-0 py-3.5 text-[10px] uppercase tracking-[0.15em] font-bold">
                    <a href="{{ route('cart') }}" class="text-gray-400 hover:text-black transition-colors underline underline-offset-2 decoration-gray-300 hover:decoration-black">Bag</a>
                    <svg class="h-3 w-3 mx-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <a href="{{ route('checkout') }}" class="text-gray-400 hover:text-black transition-colors underline underline-offset-2 decoration-gray-300 hover:decoration-black">Checkout</a>
                    <svg class="h-3 w-3 mx-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-black border-b-2 border-black pb-0.5">Payment</span>
                    <svg class="h-3 w-3 mx-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-gray-300 cursor-not-allowed" title="Complete payment first">Confirmed</span>
                </div>
            </div>
        </div>

        {{-- ── Page Body ── --}}
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-12 space-y-6">

            {{-- Order summary pill --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5 flex items-center justify-between shadow-sm">
                <div>
                    <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-0.5">Order Reference</p>
                    <p class="text-sm font-bold text-gray-900 tracking-tight">#{{ $order->order_reference }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $order->first_name }} {{ $order->last_name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-0.5">Amount Due</p>
                    <p class="text-2xl font-bold tracking-tighter text-black">
                        <span class="text-xs text-gray-400 font-semibold mr-1 align-middle">KES</span>{{ number_format($order->total_amount) }}
                    </p>
                </div>
            </div>

            {{-- M-Pesa steps card --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">

                {{-- Card header --}}
                <div class="px-6 py-4 bg-[#1A1A1A] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center pulse-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-bold text-sm leading-none">Send via M-Pesa</p>
                            <p class="text-white/50 text-[10px] uppercase tracking-widest mt-0.5">Step-by-step</p>
                        </div>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-widest text-white/40 border border-white/20 rounded px-2 py-1">Safaricom</span>
                </div>

                {{-- Steps --}}
                <div class="divide-y divide-gray-100">
                    @php
                        $steps = [
                            ['n' => '1', 'title' => 'Open M-Pesa',       'body' => 'Dial *334# or open the M-Pesa app on your Safaricom line.'],
                            ['n' => '2', 'title' => 'Send Money',         'body' => 'Select <strong class="text-black">Send Money</strong> from the menu.'],
                            ['n' => '3', 'title' => 'Enter Number',       'body' => 'Send to <strong class="text-black font-mono tracking-widest">0116 020 420</strong> — ANNE SILOMA.'],
                            ['n' => '4', 'title' => 'Enter Exact Amount', 'body' => 'Type exactly <strong class="text-black">Ksh ' . number_format($order->total_amount) . '</strong> — do not round up or down.'],
                            ['n' => '5', 'title' => 'Confirm with PIN',   'body' => 'Authorise with your M-Pesa PIN. You will receive a confirmation SMS.'],
                        ];
                    @endphp

                    @foreach($steps as $step)
                        <div class="flex items-start gap-4 px-6 py-4">
                            <div class="flex-shrink-0 w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center text-[11px] font-black text-gray-500 mt-0.5">
                                {{ $step['n'] }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 leading-none mb-1">{{ $step['title'] }}</p>
                                <p class="text-xs text-gray-500 leading-relaxed">{!! $step['body'] !!}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Divider with label --}}
            <div class="relative flex items-center gap-4">
                <div class="flex-1 h-px bg-gray-200"></div>
                <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-gray-400 whitespace-nowrap">Then enter your receipt below</p>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="text-xs text-red-600 font-medium flex items-center gap-2">
                            <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif

            {{-- Receipt form --}}
            <form action="{{ route('order.submit-receipt', $order->id) }}" method="POST"
                  class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-5">
                @csrf

                <div class="space-y-1">
                    <label class="text-[10px] uppercase tracking-widest text-gray-400 font-bold ml-1">
                        M-Pesa Number Used
                    </label>
                    <input type="text" name="payment_phone_number"
                           placeholder="e.g. 0712 345 678" required
                           value="{{ old('payment_phone_number', $order->phone) }}"
                           class="w-full p-3.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none font-medium text-sm transition-colors">
                    @error('payment_phone_number')
                    <p class="text-red-500 text-xs font-medium mt-1 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] uppercase tracking-widest text-gray-400 font-bold ml-1">
                        M-Pesa Transaction Code
                    </label>
                    <input type="text" name="mpesa_transaction_id"
                           placeholder="e.g. RQA12BC34X" required maxlength="10"
                           value="{{ old('mpesa_transaction_id') }}"
                           class="w-full p-3.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none font-bold text-sm uppercase tracking-[0.25em] transition-colors"
                           oninput="this.value = this.value.toUpperCase()">
                    <p class="text-[10px] text-gray-400 ml-1 mt-1">The 10-character code from your Safaricom SMS — e.g. <span class="font-mono font-bold text-gray-500">RQA12BC34X</span></p>
                    @error('mpesa_transaction_id')
                    <p class="text-red-500 text-xs font-medium mt-1 ml-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full bg-black text-white font-bold py-4 rounded-xl hover:bg-gray-800 active:scale-[0.99] transition-all text-xs uppercase tracking-[0.25em] shadow-sm mt-1 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Confirm Payment
                </button>
            </form>

            {{-- Important — verification timeline note (moved here from thank-you page) --}}
            <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-5 flex gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <div class="text-xs text-amber-800 space-y-1.5 leading-relaxed">
                    <p class="font-bold uppercase tracking-wider text-[10px]">Important — What Happens Next</p>
                    <p>Once you submit your transaction code above, our team will verify your payment.
                        This usually takes <strong>under 2 hours</strong> during business hours (Mon–Sat, 8am–6pm EAT).</p>
                    <p>A confirmation email will be sent to <strong>{{ $order->email }}</strong> once your payment is verified and your order is being prepared for shipping.</p>
                </div>
            </div>

            {{-- Reassurance footer --}}
            <div class="flex items-center justify-center gap-6 py-2 text-[10px] uppercase tracking-widest font-bold text-gray-300">
                <span class="flex items-center gap-1.5">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Secure
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Verified Orders Only
                </span>
            </div>

        </div>
    </div>
</x-app-layout>
