@extends('layouts.app')

@section('content')
<div class="py-2 pb-24 md:pb-12 max-w-5xl mx-auto space-y-5">

    <!-- Top Navigation Bar -->
    <div class="py-1 flex items-center justify-between border-b border-zinc-200/80 pb-3">
        <a href="{{ route('cart') }}" class="flex items-center gap-1.5 text-xs sm:text-sm font-bold text-zinc-800 hover:text-zinc-600 transition-all">
            <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            <span>Kembali ke Keranjang</span>
        </a>
        <div class="flex items-center gap-1.5 bg-zinc-50 border border-zinc-200/80 rounded-full px-3 py-1 text-xs font-bold text-zinc-800">
            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3m0-7v3m4 0h3"/></svg>
            <span>{{ $table }}</span>
        </div>
    </div>

    @if(session('error'))
        <div class="p-3 bg-red-50 border border-red-200 rounded-2xl text-xs sm:text-sm text-red-600 font-medium">
            {{ session('error') }}
        </div>
    @endif

    <form id="payment-form" action="{{ route('checkout.pay') }}" method="POST">
        @csrf
        <input type="hidden" name="table_number" value="{{ $table }}">

        <!-- Responsive Layout: 1 col on mobile, 12 cols on desktop/tablet -->
        <div class="md:grid md:grid-cols-12 md:gap-8 items-start space-y-6 md:space-y-0">

            <!-- Left Column: Details & Methods (7 cols) -->
            <div class="md:col-span-6 lg:col-span-7 space-y-4">
                <!-- Title Banner -->
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight leading-snug">
                        Bayar beres.<br>
                        Tinggal duduk manis.
                    </h2>
                    <p class="text-xs sm:text-sm text-zinc-500 mt-1">
                        {{ $setting->name ?? 'Suap, Cikini' }} · Makan di tempat · {{ $table }}
                    </p>
                </div>

                <!-- Customer Name Input -->
                <div class="bg-white border border-zinc-200/80 rounded-3xl p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="customer_name" class="text-xs sm:text-sm font-bold text-zinc-900">
                            Nama pemesan
                        </label>
                        <span class="text-[11px] text-zinc-400">
                            Untuk panggilan pesanan
                        </span>
                    </div>
                    <input 
                        type="text" 
                        id="customer_name" 
                        name="customer_name" 
                        value="{{ old('customer_name', 'Dina') }}" 
                        placeholder="Contoh: Dina" 
                        required
                        class="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-zinc-900 focus:outline-none focus:bg-white focus:border-zinc-400 transition-all"
                    >
                </div>

                <!-- Order Summary Accordion -->
                <div class="bg-white border border-zinc-200/80 rounded-3xl p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                        <span class="text-xs sm:text-sm font-bold text-zinc-900">
                            Pesananmu · {{ $totalItems }} menu
                        </span>
                        <a href="{{ route('cart') }}" class="text-xs sm:text-sm font-bold text-[#E84E1B] hover:underline">
                            Ubah
                        </a>
                    </div>

                    <div class="space-y-2 text-xs sm:text-sm text-zinc-600">
                        @foreach($cart as $c)
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-semibold text-zinc-900">{{ $c['quantity'] }} × {{ $c['name'] }}</span>
                                    @if(!empty($c['options']))
                                        <div class="text-[11px] text-zinc-400">{{ implode(', ', $c['options']) }}</div>
                                    @endif
                                    @if(!empty($c['note']))
                                        <div class="text-[11px] text-zinc-500 italic">"{{ $c['note'] }}"</div>
                                    @endif
                                </div>
                                <span class="font-medium text-zinc-800">
                                    Rp{{ number_format($c['price'] * $c['quantity'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-2 border-t border-zinc-100 space-y-1 text-xs sm:text-sm">
                        <div class="flex justify-between text-zinc-500">
                            <span>Subtotal ({{ $totalItems }} menu)</span>
                            <span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-zinc-500">
                            <span>Biaya layanan ({{ $setting->service_fee_pct ?? 5 }}%)</span>
                            <span>Rp{{ number_format($serviceFee, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-zinc-500">
                            <span>PBJT ({{ $setting->pbjt_tax_pct ?? 10 }}% dari subtotal)</span>
                            <span>Rp{{ number_format($pbjt, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-baseline pt-2 border-t border-zinc-100 font-extrabold text-zinc-950">
                            <span class="text-xs sm:text-sm">Total pembayaran</span>
                            <span class="text-base sm:text-lg text-[#E84E1B]">Rp{{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Method List -->
                <div class="space-y-2 pt-1">
                    <h4 class="text-xs sm:text-sm font-bold text-zinc-900">Pilih Metode Pembayaran</h4>

                    <div class="space-y-2.5">
                        <!-- QRIS Dinamis -->
                        <label id="label-qris" class="flex items-center justify-between p-3.5 rounded-2xl border-2 cursor-pointer border-orange-500 bg-orange-50/60 shadow-xs transition-all">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-orange-100 border border-orange-200 flex items-center justify-center text-[#E84E1B]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3m0-7v3m4 0h3"/></svg>
                                </div>
                                <div>
                                    <div class="text-xs sm:text-sm font-bold text-zinc-900 flex items-center gap-2">
                                        <span>QRIS Dinamis</span>
                                        <span class="bg-[#E84E1B] text-white text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase">Instan</span>
                                    </div>
                                    <div class="text-[10px] sm:text-xs text-zinc-500">BCA, Mandiri, BRI, GoPay, OVO, DANA, ShopeePay</div>
                                </div>
                            </div>
                            <input type="radio" name="payment_method" value="qris" checked onchange="togglePaymentMethod('qris')" class="w-4 h-4 accent-[#E84E1B]">
                        </label>

                        <!-- Tunai di Kasir -->
                        <label id="label-cash" class="flex items-center justify-between p-3.5 rounded-2xl border-2 border-zinc-200 bg-white hover:bg-zinc-50 cursor-pointer shadow-xs transition-all">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-zinc-100 border border-zinc-200 flex items-center justify-center text-zinc-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                                </div>
                                <div>
                                    <div class="text-xs sm:text-sm font-bold text-zinc-900">Tunai (Bayar di Kasir)</div>
                                    <div class="text-[10px] sm:text-xs text-zinc-400">Pesan dulu, lalu bayar langsung ke kasir</div>
                                </div>
                            </div>
                            <input type="radio" name="payment_method" value="cash" onchange="togglePaymentMethod('cash')" class="w-4 h-4 accent-[#E84E1B]">
                        </label>
                    </div>
                </div>
            </div>

            <!-- Right Column: Interactive Box & Confirm Action (5 cols) -->
            <div class="md:col-span-6 lg:col-span-5 space-y-4 md:sticky md:top-24">
                
                <!-- QRIS Box (Shown when QRIS is selected) -->
                <div id="qris-box" class="bg-white border-2 border-orange-200/90 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4 text-center">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-2.5">
                        <span class="text-sm font-black tracking-wider text-zinc-900">QRIS DINAMIS</span>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-[#E84E1B] bg-orange-50 px-2.5 py-1 rounded-full">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span id="countdown-timer">15:00</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-[11px] uppercase font-bold text-zinc-400 tracking-wider">
                            {{ $setting->name ?? 'SUAP CIKINI' }}
                        </p>
                        <div class="text-3xl font-black text-zinc-950 mt-1">
                            Rp{{ number_format($grandTotal, 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- QR Code SVG Preview -->
                    <div class="flex justify-center p-4 bg-zinc-50 rounded-2xl border border-zinc-200/70 max-w-[210px] mx-auto shadow-inner">
                        <svg id="qris-graphic" viewBox="0 0 100 100" class="w-40 h-40">
                            <rect width="100" height="100" fill="white" />
                            <rect x="5" y="5" width="25" height="25" fill="#141416" rx="2" />
                            <rect x="9" y="9" width="17" height="17" fill="white" rx="1" />
                            <rect x="13" y="13" width="9" height="9" fill="#141416" />

                            <rect x="70" y="5" width="25" height="25" fill="#141416" rx="2" />
                            <rect x="74" y="9" width="17" height="17" fill="white" rx="1" />
                            <rect x="78" y="13" width="9" height="9" fill="#141416" />

                            <rect x="5" y="70" width="25" height="25" fill="#141416" rx="2" />
                            <rect x="9" y="74" width="17" height="17" fill="white" rx="1" />
                            <rect x="13" y="78" width="9" height="9" fill="#141416" />

                            <circle cx="40" cy="15" r="2.5" fill="#141416" />
                            <circle cx="50" cy="15" r="2.5" fill="#141416" />
                            <circle cx="60" cy="15" r="2.5" fill="#141416" />

                            <circle cx="15" cy="40" r="2.5" fill="#141416" />
                            <circle cx="25" cy="45" r="2.5" fill="#141416" />
                            <circle cx="35" cy="35" r="2.5" fill="#141416" />
                            <circle cx="45" cy="45" r="2.5" fill="#141416" />
                            <circle cx="55" cy="35" r="2.5" fill="#141416" />
                            <circle cx="65" cy="45" r="2.5" fill="#141416" />
                            <circle cx="75" cy="35" r="2.5" fill="#141416" />
                            <circle cx="85" cy="45" r="2.5" fill="#141416" />

                            <circle cx="35" cy="55" r="2.5" fill="#141416" />
                            <circle cx="45" cy="55" r="2.5" fill="#E84E1B" />
                            <circle cx="55" cy="55" r="2.5" fill="#141416" />
                            <circle cx="65" cy="55" r="2.5" fill="#E84E1B" />

                            <circle cx="40" cy="75" r="2.5" fill="#141416" />
                            <circle cx="50" cy="85" r="2.5" fill="#141416" />
                            <circle cx="60" cy="75" r="2.5" fill="#141416" />
                            <circle cx="70" cy="85" r="2.5" fill="#141416" />
                            <circle cx="85" cy="75" r="2.5" fill="#141416" />

                            <circle cx="50" cy="50" r="9" fill="#E84E1B" />
                            <text x="50" y="53" text-anchor="middle" fill="white" font-size="7" font-weight="900" font-family="sans-serif">Q</text>
                        </svg>
                    </div>

                    <p class="text-xs text-zinc-500 leading-relaxed max-w-[300px] mx-auto">
                        Setelah menekan tombol di bawah, QRIS resmi akan dihasilkan secara otomatis sesuai nominal pesanan mejamu.
                    </p>
                </div>

                <!-- Cash Box (Shown when Cash is selected) -->
                <div id="cash-box" class="hidden bg-white border-2 border-emerald-200/90 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4 text-center">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-2.5">
                        <span class="text-sm font-black tracking-wider text-zinc-900">PEMBAYARAN TUNAI</span>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                            <span>Bayar di Kasir</span>
                        </div>
                    </div>

                    <div class="w-16 h-16 rounded-full bg-emerald-50 border border-emerald-200 mx-auto flex items-center justify-center text-emerald-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>

                    <div>
                        <p class="text-[11px] uppercase font-bold text-zinc-400 tracking-wider">
                            Total yang harus dibayar
                        </p>
                        <div class="text-3xl font-black text-zinc-950 mt-1">
                            Rp{{ number_format($grandTotal, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="p-3 bg-zinc-50 border border-zinc-200/70 rounded-2xl text-left text-xs text-zinc-600 space-y-2">
                        <div class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-zinc-950 text-white font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">1</span>
                            <span>Tekan tombol pesan di bawah ini untuk mengirim pesanan ke sistem restoran.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-zinc-950 text-white font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">2</span>
                            <span>Silakan menuju meja kasir dan sebutkan <strong>{{ $table }}</strong> untuk pembayaran.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-5 h-5 rounded-full bg-zinc-950 text-white font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">3</span>
                            <span>Kasir akan mengonfirmasi pembayaran tunaimu dan dapur langsung mulai memasak.</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    id="btn-submit-order"
                    class="w-full bg-[#E84E1B] hover:bg-[#d84414] active:scale-[0.99] text-white py-3.5 px-4 rounded-2xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-md transition-all"
                >
                    <span id="btn-text">Bayar Sekarang via QRIS Dinamis</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </button>

                <div class="text-center space-y-1 pt-1">
                    <p class="text-[10px] text-zinc-400">
                        Status pesanan terpantau secara real-time dari dapur hingga diantar ke mejamu.
                    </p>
                    <div class="flex items-center justify-center gap-1 text-[11px] font-medium text-emerald-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        <span>Transaksi resmi & terenkripsi</span>
                    </div>
                </div>

            </div>

        </div>

    </form>

</div>

<script>
    function togglePaymentMethod(method) {
        const qrisBox = document.getElementById('qris-box');
        const cashBox = document.getElementById('cash-box');
        const labelQris = document.getElementById('label-qris');
        const labelCash = document.getElementById('label-cash');
        const btnText = document.getElementById('btn-text');

        if (method === 'qris') {
            qrisBox.classList.remove('hidden');
            cashBox.classList.add('hidden');
            labelQris.className = 'flex items-center justify-between p-3.5 rounded-2xl border-2 cursor-pointer border-orange-500 bg-orange-50/60 shadow-xs transition-all';
            labelCash.className = 'flex items-center justify-between p-3.5 rounded-2xl border-2 border-zinc-200 bg-white hover:bg-zinc-50 cursor-pointer shadow-xs transition-all';
            btnText.innerText = 'Bayar Sekarang via QRIS Dinamis';
        } else {
            qrisBox.classList.add('hidden');
            cashBox.classList.remove('hidden');
            labelCash.className = 'flex items-center justify-between p-3.5 rounded-2xl border-2 cursor-pointer border-emerald-500 bg-emerald-50/60 shadow-xs transition-all';
            labelQris.className = 'flex items-center justify-between p-3.5 rounded-2xl border-2 border-zinc-200 bg-white hover:bg-zinc-50 cursor-pointer shadow-xs transition-all';
            btnText.innerText = 'Pesan Sekarang (Bayar Tunai di Kasir)';
        }
    }

    let secondsLeft = 899; // 15 mins
    const timerEl = document.getElementById('countdown-timer');
    if (timerEl) {
        setInterval(() => {
            if (secondsLeft > 0) {
                secondsLeft--;
                const mins = Math.floor(secondsLeft / 60);
                const secs = secondsLeft % 60;
                timerEl.innerText = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            }
        }, 1000);
    }
</script>
@endsection
