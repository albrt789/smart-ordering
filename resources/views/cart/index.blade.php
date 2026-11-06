@extends('layouts.app')

@section('content')
<div class="py-2 pb-24 md:pb-12 max-w-5xl mx-auto space-y-5">

    <!-- Top Navigation Bar -->
    <div class="py-1 flex items-center justify-between border-b border-zinc-200/80 pb-3">
        <a href="{{ route('menu') }}" class="flex items-center gap-1.5 text-xs sm:text-sm font-bold text-zinc-800 hover:text-zinc-600 transition-all">
            <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            <span>Kembali ke Menu</span>
        </a>
        <div class="flex items-center gap-1.5 bg-zinc-50 border border-zinc-200/80 rounded-full px-3 py-1 text-xs font-bold text-zinc-800">
            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3m0-7v3m4 0h3"/></svg>
            <span>{{ $table }}</span>
        </div>
    </div>

    <!-- Responsive Layout: 1 col on mobile, 12 cols on desktop/tablet -->
    <div class="md:grid md:grid-cols-12 md:gap-8 items-start space-y-6 md:space-y-0">

        <!-- Left Column: Items List (8 cols) -->
        <div class="md:col-span-7 lg:col-span-8 space-y-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight">
                    Sudah pas seleramu?
                </h2>
                <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                    Cek pilihan pesanan sebelum lanjut ke pembayaran.
                </p>
            </div>

            <!-- Restaurant Header Card -->
            <div class="bg-[#141416] text-white rounded-3xl p-4 sm:p-5 flex items-center justify-between border border-zinc-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-2xl bg-zinc-800 border border-zinc-700 flex items-center justify-center text-[#E84E1B]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16 2-4 4 4 4M2 16l4-4 4 4M2 2l20 20"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold tracking-tight">
                            Suap, Cikini · {{ $table }}
                        </h4>
                        <p class="text-[11px] sm:text-xs text-zinc-400">
                            Makan di tempat · Pesanan kami antar ke mejamu
                        </p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            </div>

            <!-- Items Container -->
            @if(empty($cart))
                <div class="text-center py-16 space-y-3 bg-white rounded-3xl border border-zinc-200/80 p-8 shadow-sm">
                    <svg class="w-12 h-12 mx-auto text-zinc-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <p class="text-sm font-semibold text-zinc-600">Keranjangmu masih kosong nih.</p>
                    <a href="{{ route('menu') }}" class="inline-block bg-[#E84E1B] text-white text-xs sm:text-sm font-bold py-2.5 px-6 rounded-xl shadow-sm">
                        Pilih Menu Sekarang
                    </a>
                </div>
            @else
                <div class="bg-white rounded-3xl border border-zinc-200/80 p-5 divide-y divide-zinc-100 shadow-sm space-y-4">
                    @php
                        $hasStockIssue = false;
                    @endphp
                    @foreach($cart as $itemKey => $item)
                        @php
                            $prod = \App\Models\Product::find($item['product_id'] ?? null);
                            $isItemOutOfStock = $prod && $prod->isOutOfStock();
                            $isExceeded = $prod && $prod->track_stock && ($item['quantity'] > $prod->stock);
                            if ($isItemOutOfStock || $isExceeded) {
                                $hasStockIssue = true;
                            }
                        @endphp
                        <div class="pt-4 first:pt-0 space-y-2">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3.5">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover bg-zinc-100 shrink-0 border border-zinc-200/60 shadow-2xs {{ $isItemOutOfStock ? 'grayscale opacity-60' : '' }}">
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="text-xs sm:text-base font-bold text-zinc-900 leading-snug">
                                                {{ $item['name'] }}
                                            </h4>
                                            @if($isItemOutOfStock)
                                                <span class="text-[9px] font-black text-white bg-red-600 px-2 py-0.5 rounded-md uppercase">
                                                    Stok Habis
                                                </span>
                                            @elseif($isExceeded)
                                                <span class="text-[9px] font-bold text-amber-900 bg-amber-100 border border-amber-300 px-2 py-0.5 rounded-md">
                                                    Sisa {{ $prod->stock }} porsi
                                                </span>
                                            @elseif(!empty($item['is_ai_pick']))
                                                <span class="text-[9px] sm:text-[10px] font-bold text-[#E84E1B] bg-orange-50 border border-orange-200/60 px-2 py-0.5 rounded-md flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                                                    <span>Pilihan Suap AI</span>
                                                </span>
                                            @endif
                                        </div>

                                        @if(!empty($item['options_text']))
                                            <p class="text-[11px] sm:text-xs text-zinc-500 mt-1">
                                                {{ $item['options_text'] }}
                                            </p>
                                        @elseif(!empty($item['options']))
                                            <p class="text-[11px] sm:text-xs text-zinc-500 mt-1">
                                                {{ is_array($item['options']) ? implode(', ', $item['options']) : $item['options'] }}
                                            </p>
                                        @endif

                                        <div class="text-xs sm:text-sm font-extrabold text-zinc-900 mt-1.5">
                                            Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Stepper Quantity -->
                                <div class="flex items-center gap-2 bg-zinc-100 rounded-xl px-2.5 py-1.5 shrink-0 border border-zinc-200/50">
                                    <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $itemKey }}">
                                        <input type="hidden" name="delta" value="-1">
                                        <button type="submit" class="text-zinc-600 hover:text-zinc-950 font-bold px-1.5 text-xs sm:text-sm">-</button>
                                    </form>
                                    <span class="text-xs sm:text-sm font-bold text-zinc-900 min-w-4 text-center">{{ $item['quantity'] }}</span>
                                    <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $itemKey }}">
                                        <input type="hidden" name="delta" value="1">
                                        <button type="submit" class="text-zinc-600 hover:text-zinc-950 font-bold px-1.5 text-xs sm:text-sm">+</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Note -->
                            @if(!empty($item['note']))
                                <div class="bg-zinc-50 border border-zinc-200/60 rounded-xl px-3 py-1.5 text-[11px] sm:text-xs text-zinc-600">
                                    <span class="font-semibold text-zinc-700">Catatan untuk dapur: </span>
                                    {{ $item['note'] }}
                                </div>
                            @endif

                            @if($isItemOutOfStock || $isExceeded)
                                <div class="bg-red-50 border border-red-200 rounded-xl px-3 py-1.5 text-[11px] text-red-700 font-medium">
                                    {{ $isItemOutOfStock ? 'Menu ini sudah habis terjual. Silakan kurangi hingga 0 untuk menghapus.' : "Stok hanya tersedia {$prod->stock} porsi. Mohon kurangi jumlah pesanan Anda." }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Tambah Menu Lain -->
                <a href="{{ route('menu') }}" class="block w-full py-3 border border-dashed border-zinc-300 hover:border-zinc-400 bg-white rounded-2xl text-xs sm:text-sm font-bold text-zinc-700 text-center transition-all shadow-2xs">
                    + Tambah menu lain ke keranjang
                </a>
            @endif
        </div>

        <!-- Right Column: Cost Breakdown & Checkout Action (5 cols) -->
        @if(!empty($cart))
            <div class="md:col-span-5 lg:col-span-4 space-y-4 md:sticky md:top-24">
                <div class="bg-white border border-zinc-200/80 rounded-3xl p-5 sm:p-6 space-y-4 shadow-sm">
                    <h4 class="text-sm font-extrabold text-zinc-900">
                        Ringkasan pembayaran
                    </h4>

                    <div class="space-y-2 text-xs sm:text-sm">
                        <div class="flex justify-between text-zinc-600">
                            <span>Subtotal ({{ $totalItems }} menu)</span>
                            <span class="font-medium text-zinc-800">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-zinc-600">
                            <span>Biaya layanan (5%)</span>
                            <span class="font-medium text-zinc-800">Rp{{ number_format($serviceFee, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-zinc-600">
                            <span>PBJT (10% dari subtotal)</span>
                            <span class="font-medium text-zinc-800">Rp{{ number_format($pbjt, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-zinc-100 flex justify-between items-baseline">
                        <span class="text-xs sm:text-sm font-bold text-zinc-900">Total pembayaran</span>
                        <span class="text-lg sm:text-xl font-black text-[#E84E1B]">Rp{{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="bg-zinc-50 border border-zinc-200/70 rounded-xl p-3 text-[11px] text-zinc-500 leading-relaxed">
                        Pesanan baru dikirim ke dapur setelah pembayaran berhasil. Masih bisa ubah pilihanmu sekarang.
                    </div>

                    @if($hasStockIssue)
                        <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-xs text-red-800 font-semibold text-center leading-snug">
                            ⚠️ Sebagian menu di keranjang melebihi stok hari ini. Mohon kurangi atau hapus menu tersebut untuk melanjutkan.
                        </div>
                        <button type="button" disabled class="w-full bg-zinc-200 text-zinc-400 py-3.5 px-4 rounded-xl font-bold text-xs sm:text-sm cursor-not-allowed">
                            Lanjut ke Pembayaran
                        </button>
                    @else
                        <a href="{{ route('checkout') }}" class="w-full bg-[#E84E1B] hover:bg-[#d84414] active:scale-[0.99] text-white py-3.5 px-4 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-md transition-all">
                            <span>Lanjut ke Pembayaran (QRIS / Tunai)</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    @endif

                    <p class="text-center text-[10px] text-zinc-400">
                        Tanpa daftar akun · Langsung dari mejamu
                    </p>
                </div>
            </div>
        @endif

    </div>

</div>
@endsection
