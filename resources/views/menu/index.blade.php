@extends('layouts.app')

@section('content')
<div class="py-2 pb-24 md:pb-12">

    <!-- Responsive Desktop/Tablet Split Grid -->
    <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-start">

        <!-- Main Menu Column (Left 8 cols on desktop, Full width on mobile/tablet) -->
        <div class="lg:col-span-8 space-y-5">

            <!-- Hero Banner -->
            <div class="relative overflow-hidden rounded-[26px] bg-[#141416] text-white p-5 sm:p-7 shadow-lg border border-zinc-800">
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-[#E84E1B]/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[10px] uppercase tracking-widest font-extrabold text-[#E84E1B] bg-orange-950/80 px-2.5 py-0.5 rounded-md border border-orange-800/60">
                                ARTISAN ROASTERY & COFFEE BAR
                            </span>
                            <span class="hidden sm:inline text-xs text-zinc-400">· {{ $setting->name ?? 'Fountain Hide' }}</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight mb-2">
                            Artisan Coffee,<br class="sm:hidden">
                            Crafted Daily.
                        </h2>
                        <p class="text-xs sm:text-sm text-zinc-400">
                            Pilih racikan kopi dan minuman favoritmu, bayar via QRIS, pesanan kami antar ke <span class="text-white font-bold">{{ strtolower($table) }}</span>.
                        </p>
                    </div>
                    <div class="hidden sm:flex flex-col items-end shrink-0 text-right">
                        <div class="w-12 h-12 rounded-2xl bg-zinc-900 border border-zinc-700/80 flex items-center justify-center p-2 shadow-inner mb-2">
                            <img src="{{ asset('images/logo-white.png') }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <span class="text-[11px] text-zinc-400 font-semibold">Estimasi 3–7 mnt</span>
                    </div>
                </div>
            </div>

            <!-- Search Bar & Filters -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <!-- Search Form -->
                <form action="{{ route('menu') }}" method="GET" class="relative flex-1">
                    @php $activeCategory = $selectedCategory ?? request('category', 'Semua'); @endphp
                    @if($activeCategory && $activeCategory !== 'Semua')
                        <input type="hidden" name="category" value="{{ $activeCategory }}">
                    @endif
                    <svg class="w-4 h-4 text-zinc-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/></svg>
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ $search ?? '' }}" 
                        placeholder="Lagi ingin minum apa? Cari kopi, mocktail, teh..." 
                        class="w-full bg-white border border-zinc-200/90 rounded-2xl py-2.5 pl-10 pr-4 text-xs sm:text-sm placeholder:text-zinc-400 text-zinc-800 focus:outline-none focus:border-zinc-400 focus:ring-1 focus:ring-zinc-400 transition-all shadow-sm"
                    >
                    @if(!empty($search))
                        <a href="{{ route('menu', ['category' => $activeCategory]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-zinc-400 hover:text-zinc-600">✕</a>
                    @endif
                </form>

                <!-- Category Pills -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    @foreach($categories as $cat)
                        @php $isActive = ($activeCategory === $cat); @endphp
                        <a 
                            href="{{ route('menu', ['category' => $cat, 'q' => $search ?? '']) }}" 
                            class="px-4 py-2 rounded-full text-xs font-semibold transition-all shrink-0 {{ $isActive ? 'bg-zinc-950 text-white shadow-sm' : 'bg-white text-zinc-600 hover:bg-zinc-100 border border-zinc-200/80' }}"
                        >
                            {{ $cat }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Food Grid Section -->
            <div>
                <div class="flex items-center justify-between mb-3.5">
                    <h3 class="text-base sm:text-lg font-extrabold text-zinc-900 tracking-tight">
                        Pilihan buat kamu
                    </h3>
                    <span class="text-xs text-zinc-400 font-medium">
                        {{ $menuItems->count() }} menu tersedia
                    </span>
                </div>

                <!-- Responsive Grid: 2 cols on mobile, 3 cols on tablet & desktop -->
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-3.5 sm:gap-4 lg:gap-5">
                    @foreach($menuItems as $dish)
                        @php
                            $qtyInCart = 0;
                            $cartItemId = null;
                            foreach ($cart as $k => $c) {
                                if (($c['product_id'] ?? null) == $dish->id || ($c['slug'] ?? null) == $dish->slug) {
                                    $qtyInCart += $c['quantity'];
                                    $cartItemId = $k;
                                }
                            }
                        @endphp
                        <div class="bg-white rounded-2xl p-3 border border-zinc-200/70 shadow-sm flex flex-col justify-between hover:border-zinc-300 hover:shadow-md transition-all group {{ $dish->isOutOfStock() ? 'opacity-75' : '' }}">
                            <!-- Clickable Card Content -->
                            <a href="{{ route('menu.show', $dish->slug) }}" class="block">
                                <div class="relative aspect-square w-full rounded-xl overflow-hidden bg-zinc-100 mb-2.5">
                                    <img src="{{ $dish->image }}" alt="{{ $dish->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 {{ $dish->isOutOfStock() ? 'grayscale opacity-60' : '' }}" loading="lazy">
                                    @if($dish->isOutOfStock())
                                        <span class="absolute top-2 left-2 text-[9px] font-black tracking-wider px-2 py-0.5 rounded-md uppercase bg-zinc-950 text-white shadow-md border border-zinc-700">
                                            STOK HABIS
                                        </span>
                                    @elseif($dish->badge)
                                        <span class="absolute top-2 left-2 text-[9px] font-extrabold tracking-wider px-2 py-0.5 rounded-md uppercase bg-white/95 text-zinc-900 shadow-sm border border-zinc-200/50">
                                            {{ $dish->badge }}
                                        </span>
                                    @elseif($dish->is_signature)
                                        <span class="absolute top-2 left-2 text-[9px] font-extrabold tracking-wider px-2 py-0.5 rounded-md uppercase bg-rose-600 text-white shadow-sm">
                                            SIGNATURE
                                        </span>
                                    @endif

                                    @if(!$dish->isOutOfStock() && $dish->track_stock && $dish->stock <= 5)
                                        <span class="absolute bottom-2 right-2 text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-500 text-white shadow-sm">
                                            Sisa {{ $dish->stock }}
                                        </span>
                                    @endif
                                </div>
                                <h4 class="font-bold text-xs sm:text-sm text-zinc-900 line-clamp-1 group-hover:text-[#E84E1B] transition-colors">
                                    {{ $dish->name }}
                                </h4>
                                <p class="text-[11px] sm:text-xs text-zinc-400 line-clamp-1 mt-0.5 font-normal">
                                    {{ $dish->subtitle ?? $dish->description }}
                                </p>
                            </a>

                            <!-- Price & Actions -->
                            <div class="mt-3 pt-2.5 border-t border-zinc-100 flex items-center justify-between">
                                <div>
                                    <span class="text-xs sm:text-sm font-extrabold text-zinc-900 block">
                                        Rp{{ number_format($dish->price, 0, ',', '.') }}
                                    </span>
                                    @if(!$dish->isOutOfStock() && $dish->track_stock)
                                        <span class="text-[10px] text-zinc-400 font-medium">Stok: {{ $dish->stock }}</span>
                                    @endif
                                </div>

                                @if($dish->isOutOfStock())
                                    <span class="px-2.5 py-1 rounded-lg bg-zinc-100 border border-zinc-200 text-zinc-400 font-bold text-[10px] uppercase tracking-wide cursor-not-allowed">
                                        Habis
                                    </span>
                                @elseif($qtyInCart > 0)
                                    <div class="flex items-center gap-1.5 bg-orange-50 border border-orange-200/60 rounded-lg px-2 py-1">
                                        <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $cartItemId }}">
                                            <input type="hidden" name="delta" value="-1">
                                            <button type="submit" class="text-xs text-[#E84E1B] font-bold px-1 hover:opacity-75">-</button>
                                        </form>
                                        <span class="text-xs font-bold text-zinc-800 min-w-3 text-center">{{ $qtyInCart }}</span>
                                        <a href="{{ route('menu.show', $dish->slug) }}" class="text-xs text-[#E84E1B] font-bold px-1 hover:opacity-75">+</a>
                                    </div>
                                @else
                                    <a href="{{ route('menu.show', $dish->slug) }}" class="w-7 h-7 rounded-lg bg-[#E84E1B] hover:bg-[#d84414] active:scale-95 text-white flex items-center justify-center shadow-sm transition-all" title="Pilih & tambah ke keranjang">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- AI Recommendation Banner (Visible on mobile/tablet) -->
            <a href="{{ route('ai.chat') }}" class="block lg:hidden bg-white border border-orange-200/80 rounded-2xl p-4 hover:border-orange-300 hover:shadow-sm transition-all group">
                <div class="flex items-center justify-between">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center text-[#E84E1B] shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-zinc-900 group-hover:text-[#E84E1B] transition-colors">
                                Bingung pilih kopi? Tanya Fountain Hide AI.
                            </h4>
                            <p class="text-[11px] text-zinc-500 mt-0.5">
                                Profil rasa biji kopi, oat milk, sampai rekomendasi pairing pastry.
                            </p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-zinc-400 group-hover:text-[#E84E1B] group-hover:translate-x-0.5 transition-all shrink-0 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

        </div>

        <!-- Desktop Sidebar Column (Right 4 cols on desktop: Sticky Cart & Fountain AI) -->
        <div class="hidden lg:block lg:col-span-4 space-y-5 sticky top-20">

            <!-- Desktop Sticky Live Cart Panel -->
            <div class="bg-white border border-zinc-200/80 rounded-3xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                    <div>
                        <h3 class="text-sm font-extrabold text-zinc-950">Pesanan {{ $table }}</h3>
                        <p class="text-[11px] text-zinc-400">Makan di tempat · Diantar ke meja</p>
                    </div>
                    <span class="text-xs font-black text-[#E84E1B] bg-orange-50 border border-orange-200/60 px-2.5 py-1 rounded-full">
                        {{ $cartCount }} Menu
                    </span>
                </div>

                @if(empty($cart))
                    <div class="text-center py-8 text-zinc-400">
                        <svg class="w-10 h-10 mx-auto text-zinc-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        <p class="text-xs">Keranjang masih kosong.</p>
                        <p class="text-[11px] text-zinc-400 mt-0.5">Pilih menu di samping untuk memesan.</p>
                    </div>
                @else
                    <!-- Cart Item List -->
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1 divide-y divide-zinc-100">
                        @foreach($cart as $itemKey => $item)
                            <div class="pt-3 first:pt-0 space-y-1.5">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1">
                                        <h5 class="text-xs font-bold text-zinc-900">{{ $item['name'] }}</h5>
                                        @if(!empty($item['options_text']))
                                            <p class="text-[10px] text-zinc-400">{{ $item['options_text'] }}</p>
                                        @endif
                                        <span class="text-xs font-semibold text-zinc-800">
                                            Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5 bg-zinc-100 rounded-lg px-2 py-0.5 shrink-0">
                                        <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $itemKey }}">
                                            <input type="hidden" name="delta" value="-1">
                                            <button type="submit" class="text-xs font-bold text-zinc-600 hover:text-zinc-950 px-1">-</button>
                                        </form>
                                        <span class="text-xs font-bold text-zinc-900 min-w-3 text-center">{{ $item['quantity'] }}</span>
                                        <form action="{{ route('cart.update') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $itemKey }}">
                                            <input type="hidden" name="delta" value="1">
                                            <button type="submit" class="text-xs font-bold text-zinc-600 hover:text-zinc-950 px-1">+</button>
                                        </form>
                                    </div>
                                </div>
                                @if(!empty($item['note']))
                                    <p class="text-[10px] text-amber-700 bg-amber-50 rounded-lg px-2 py-0.5 italic">"{{ $item['note'] }}"</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Cost Breakdown -->
                    @php
                        $sb = $subtotal;
                        $svc = (int) round($sb * (($setting->service_fee_pct ?? 5) / 100));
                        $pb = (int) round($sb * (($setting->pbjt_tax_pct ?? 10) / 100));
                        $gt = $sb + $svc + $pb;
                    @endphp
                    <div class="pt-3 border-t border-zinc-100 space-y-1 text-xs">
                        <div class="flex justify-between text-zinc-500">
                            <span>Subtotal ({{ $cartCount }} menu)</span>
                            <span>Rp{{ number_format($sb, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-zinc-500">
                            <span>Biaya Layanan & PBJT (10%)</span>
                            <span>Rp{{ number_format($svc + $pb, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-baseline pt-2 border-t border-zinc-100 font-extrabold text-zinc-950 text-sm">
                            <span>Total</span>
                            <span class="text-base text-[#E84E1B]">Rp{{ number_format($gt, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout') }}" class="w-full bg-[#E84E1B] hover:bg-[#d84414] active:scale-[0.99] text-white py-3 px-4 rounded-xl font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all">
                        <span>Lanjut ke Pembayaran QRIS</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                @endif
            </div>

            <!-- Desktop Fountain AI Widget Box -->
            <div class="bg-white border border-zinc-200/80 rounded-3xl p-5 shadow-sm space-y-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center text-[#E84E1B]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-zinc-900">Asisten Fountain AI</h4>
                        <p class="text-[11px] text-zinc-400">Barista concierge & info beans</p>
                    </div>
                </div>
                <p class="text-xs text-zinc-600 leading-relaxed">
                    Mau tahu profil beans, pilihan oat milk, atau rekomendasi pairing pastry hangat? Chat barista AI sekarang.
                </p>
                <a href="{{ route('ai.chat') }}" class="block text-center bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition-all shadow-sm">
                    Buka Chat Fountain AI →
                </a>
            </div>

        </div>

    </div>

</div>

<!-- Sticky Floating Cart Bar (Visible ONLY on Mobile devices) -->
@if($cartCount > 0)
<div class="lg:hidden fixed bottom-16 left-0 right-0 z-20 px-4 pointer-events-none">
    <div class="max-w-md mx-auto pointer-events-auto">
        <a href="{{ route('cart') }}" class="w-full bg-[#E84E1B] hover:bg-[#d84414] active:scale-[0.99] text-white py-3.5 px-5 rounded-2xl shadow-lg flex items-center justify-between font-bold text-sm transition-all">
            <div class="flex items-center gap-2">
                <span>Lihat keranjang</span>
                <span class="opacity-80">·</span>
                <span class="font-medium opacity-90">{{ $cartCount }} menu</span>
            </div>
            <div>
                <span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
        </a>
        <p class="text-center text-[10px] text-zinc-400 mt-1.5 font-medium">
            Belum termasuk pajak & biaya layanan
        </p>
    </div>
</div>
@endif
@endsection
