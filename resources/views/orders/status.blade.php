@extends('layouts.app')

@section('content')
<div class="py-2 pb-24 md:pb-12 max-w-5xl mx-auto space-y-5">

    <!-- Top Navigation Bar -->
    <div class="py-1 flex items-center justify-between border-b border-zinc-200/80 pb-3">
        <div class="flex items-center gap-2">
            <h2 class="text-base sm:text-lg font-extrabold text-zinc-900">
                Status Pesanan
            </h2>
            <div id="live-indicator" class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/70 px-2.5 py-0.5 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                <span>Real-Time Live</span>
            </div>
        </div>
        <div class="flex items-center gap-1.5 bg-zinc-50 border border-zinc-200/80 rounded-full px-3 py-1 text-xs font-bold text-zinc-800">
            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3m0-7v3m4 0h3"/></svg>
            <span>{{ $table }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs sm:text-sm text-emerald-800 font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(!$order)
        <div class="text-center py-16 space-y-3 bg-white rounded-3xl border border-zinc-200/80 p-8 shadow-sm">
            <div class="w-14 h-14 rounded-2xl bg-orange-50 border border-orange-200 text-[#E84E1B] flex items-center justify-center mx-auto">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
            </div>
            <h3 class="text-base font-bold text-zinc-900">Belum Ada Pesanan Aktif</h3>
            <p class="text-xs sm:text-sm text-zinc-500 max-w-sm mx-auto">Belum ada pesanan yang tercatat untuk {{ $table }}. Silakan jelajahi menu makanan kami dan pesan langsung dari mejamu!</p>
            <div class="pt-2">
                <a href="{{ route('menu') }}" class="inline-block bg-[#E84E1B] hover:bg-[#d84414] text-white text-xs sm:text-sm font-bold py-2.5 px-6 rounded-xl shadow-sm transition-all">
                    Lihat Menu Makanan
                </a>
            </div>
        </div>
    @else
        <!-- Responsive Layout: 1 col on mobile, 12 cols on desktop/tablet -->
        <div class="md:grid md:grid-cols-12 md:gap-8 items-start space-y-6 md:space-y-0">

            <!-- Left Column: Status Hero, ETA & Timeline (7 cols) -->
            <div class="md:col-span-6 lg:col-span-7 space-y-4">

                <!-- 1. PAYMENT PENDING ALERT (IF UNPAID) -->
                @if(!$order->isPaid())
                    @if($order->payment_method === 'qris')
                        <!-- DYNAMIC QRIS CARD (FIGMA DESIGN ACCURATE) -->
                        <div class="bg-white border-2 border-orange-500/80 rounded-3xl p-5 sm:p-7 shadow-lg space-y-5 text-center relative overflow-hidden">
                            <!-- Top Header -->
                            <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="bg-[#E84E1B] text-white text-[10px] font-black px-2.5 py-0.5 rounded-md tracking-wider">
                                        QRIS
                                    </span>
                                    <span class="text-xs font-extrabold text-zinc-900 tracking-tight">QRIS Dinamis Otomatis</span>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs font-bold text-[#E84E1B] bg-orange-50 border border-orange-200/60 px-3 py-1 rounded-full">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <span id="countdown-timer">14:59</span>
                                </div>
                            </div>

                            <!-- Merchant & Amount Info -->
                            <div>
                                <p class="text-xs font-extrabold text-zinc-400 tracking-wider uppercase">
                                    SUAP CIKINI · {{ $order->order_number }} · {{ $order->table_number }}
                                </p>
                                <div class="text-3xl sm:text-4xl font-black text-zinc-950 mt-1 tracking-tight">
                                    Rp{{ number_format($order->total_amount, 0, ',', '.') }}
                                </div>
                                <p class="text-[11px] text-zinc-500 mt-1">
                                    Nominal pembayaran otomatis terkunci di QRIS (tidak perlu ketik nominal)
                                </p>
                            </div>

                            <!-- Dynamic QR Code Container -->
                            @php
                                $qrisUrl = $order->payment->raw_response['qris_url'] ?? ('https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=8&data=' . urlencode($order->payment->qris_payload ?? ''));
                            @endphp
                            <div class="flex flex-col items-center">
                                <div class="p-3.5 bg-white rounded-2xl border-2 border-zinc-200/90 shadow-md max-w-[240px] aspect-square flex items-center justify-center">
                                    <img src="{{ $qrisUrl }}" alt="QRIS {{ $order->order_number }}" class="w-full h-full object-contain">
                                </div>

                                <!-- Download / Save QR Button -->
                                <div class="mt-3 flex items-center gap-2">
                                    <a href="{{ $qrisUrl }}" target="_blank" download="QRIS-{{ $order->order_number }}.png" class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-bold rounded-xl flex items-center gap-1.5 transition-all shadow-2xs">
                                        <svg class="w-4 h-4 text-[#E84E1B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        <span>Unduh / Buka Gambar QR</span>
                                    </a>
                                </div>

                                <!-- Step Guide & Sandbox Notice -->
                                <div class="mt-3 bg-amber-50 border border-amber-200/80 rounded-xl p-3 text-left max-w-sm mx-auto">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                        <div class="text-[11px] text-amber-900 leading-relaxed">
                                            <strong class="font-extrabold text-amber-950">Mode Sandbox (Simulasi):</strong> Dompet digital asli (BCA, GoPay, DANA di HP) terhubung ke Bank Indonesia nyata sehingga menolak QR simulasi/sandbox. Untuk mencoba pembayaran, klik tombol <strong>Simulasi Bayar Berhasil</strong> di bawah atau gunakan <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" class="underline font-bold text-amber-900 hover:text-amber-700">Simulator Midtrans</a>.
                                        </div>
                                    </div>
                                </div>

                                <!-- Acquirers Supported Pills -->
                                <div class="flex flex-wrap items-center justify-center gap-1.5 mt-3 pt-3 border-t border-zinc-100 text-[10px] font-bold text-zinc-400">
                                    <span class="px-2 py-0.5 bg-zinc-50 border border-zinc-200 rounded">BCA</span>
                                    <span class="px-2 py-0.5 bg-zinc-50 border border-zinc-200 rounded">Mandiri</span>
                                    <span class="px-2 py-0.5 bg-zinc-50 border border-zinc-200 rounded">BRI</span>
                                    <span class="px-2 py-0.5 bg-zinc-50 border border-zinc-200 rounded">GoPay</span>
                                    <span class="px-2 py-0.5 bg-zinc-50 border border-zinc-200 rounded">OVO</span>
                                    <span class="px-2 py-0.5 bg-zinc-50 border border-zinc-200 rounded">DANA</span>
                                    <span class="px-2 py-0.5 bg-zinc-50 border border-zinc-200 rounded">ShopeePay</span>
                                </div>
                            </div>

                            <!-- Actions: Check Status & Sandbox Simulation -->
                            <div class="pt-2 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-center gap-2.5">
                                <button onclick="checkLiveOrderStatus(true)" class="w-full sm:w-auto px-5 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-zinc-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    <span>Cek Status Pembayaran</span>
                                </button>

                                <!-- Sandbox Simulation Form -->
                                <form action="{{ route('payment.simulate', $order->order_number) }}" method="POST" class="w-full sm:w-auto">
                                    @csrf
                                    <button type="submit" class="w-full px-5 py-2.5 bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5" title="Klik untuk simulasi bayar berhasil tanpa uang asli">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        <span>Simulasi Bayar Berhasil (Sandbox)</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Unpaid Cash Alert -->
                        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-3xl p-6 shadow-md space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="bg-white/20 text-white text-[11px] font-black px-3 py-1 rounded-full uppercase tracking-wider backdrop-blur-sm">
                                    Menunggu Pembayaran Tunai
                                </span>
                                <span class="text-xs font-bold text-blue-100">Bayar ke Kasir</span>
                            </div>
                            <div>
                                <h3 class="text-xl sm:text-2xl font-black">Silakan Menuju Kasir</h3>
                                <p class="text-xs sm:text-sm text-blue-100 mt-1">
                                    Mohon tunjukkan nomor meja <strong class="text-white">{{ $order->table_number }}</strong> atau pesanan <strong class="text-white">{{ $order->order_number }}</strong> ke kasir untuk membayar tunai sebesar <strong class="text-white">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</strong>.
                                </p>
                            </div>
                            <div class="p-3 bg-white/10 rounded-2xl border border-white/20 text-xs text-blue-50 flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                <span>Setelah kasir mengonfirmasi pembayaranmu, status di halaman ini akan otomatis berganti ke <strong>Pesanan Diterima</strong> tanpa perlu reload.</span>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Hero Status Card -->
                <div class="bg-[#141416] text-white rounded-3xl p-6 sm:p-7 shadow-lg border border-zinc-800 space-y-3.5 relative overflow-hidden">
                    <div class="flex items-center gap-2 text-xs font-bold {{ $order->isPaid() ? 'text-emerald-400 bg-emerald-950/60 border border-emerald-800/60' : 'text-amber-400 bg-amber-950/60 border border-amber-800/60' }} px-3.5 py-1.5 rounded-full w-fit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        <span id="badge-payment-status">{{ $order->payment_status_label }}</span>
                    </div>

                    <div>
                        <h3 id="hero-headline" class="text-2xl sm:text-3xl font-black tracking-tight leading-snug">
                            @if($order->status === 'completed')
                                Pesanan Sudah Selesai!
                            @elseif($order->status === 'delivering')
                                Sedang Diantar ke Mejamu!
                            @elseif($order->status === 'cooking' || $order->status === 'preparing')
                                Dapur Mulai Beraksi.
                            @elseif($order->status === 'received')
                                Pesanan Telah Diterima.
                            @else
                                Menunggu Pembayaran.
                            @endif
                        </h3>
                        <p class="text-xs sm:text-sm text-zinc-400 mt-2 leading-relaxed">
                            Makasih, <span class="text-white font-bold">{{ $order->customer_name }}</span>! Duduk santai, pesananmu akan kami antar ke <span class="text-white font-bold">{{ strtolower($table) }}</span>.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <span class="text-xs font-bold text-zinc-300 bg-zinc-800/80 px-3 py-1.5 rounded-xl border border-zinc-700/60">
                            {{ $order->order_number }}
                        </span>
                        <span class="text-xs font-bold text-zinc-300 bg-zinc-800/80 px-3 py-1.5 rounded-xl border border-zinc-700/60">
                            {{ $table }}
                        </span>
                    </div>
                </div>

                <!-- ETA Card -->
                <div class="bg-orange-50/70 border border-orange-200/70 rounded-3xl p-4 sm:p-5 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-orange-200 flex items-center justify-center text-[#E84E1B] shadow-2xs shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] sm:text-xs uppercase font-extrabold text-[#E84E1B] tracking-wider block">
                            PERKIRAAN TIBA DI MEJA
                        </span>
                        <span id="eta-text" class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight">
                            {{ $order->eta_range ?? '12.50–12.55' }}
                        </span>
                    </div>
                </div>

                <!-- 7-Stage Status Tracker Timeline -->
                <div class="bg-white border border-zinc-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                        <h4 class="text-xs sm:text-sm font-extrabold text-zinc-900">Perkembangan Pesanan</h4>
                        <span class="text-[11px] text-zinc-400 font-medium flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Auto Live</span>
                        </span>
                    </div>

                    @php
                        $stageMap = [
                            'waiting_payment' => 1,
                            'paid' => 2,
                            'received' => 3,
                            'cooking' => 4,
                            'preparing' => 5,
                            'delivering' => 6,
                            'completed' => 7,
                            'cancelled' => 0
                        ];
                        $currentStage = $stageMap[$order->status] ?? 1;
                        if ($order->isPaid() && $currentStage < 2) {
                            $currentStage = 2;
                        }
                    @endphp

                    <div class="space-y-5 relative before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:bg-zinc-100">

                        <!-- Stage 1 & 2: Pembayaran -->
                        <div class="relative flex items-start gap-3.5 pl-1">
                            <div class="w-5 h-5 rounded-full {{ $order->isPaid() ? 'bg-zinc-900 text-white' : 'bg-orange-500 text-white animate-pulse' }} flex items-center justify-center text-[10px] z-10 shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </div>
                            <div class="flex-1 -mt-0.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs sm:text-sm font-bold text-zinc-900">1 & 2. Status Pembayaran</span>
                                    <span class="text-[10px] sm:text-xs font-semibold text-zinc-400">{{ $order->payment_time ?? $order->order_time }}</span>
                                </div>
                                <p class="text-[11px] sm:text-xs text-zinc-500 mt-0.5">
                                    {{ $order->isPaid() ? 'Pembayaran berhasil dikonfirmasi via ' . strtoupper($order->payment_method) : 'Menunggu pembayaran via ' . strtoupper($order->payment_method) }}
                                </p>
                            </div>
                        </div>

                        <!-- Stage 3: Pesanan Diterima Dapur -->
                        <div class="relative flex items-start gap-3.5 pl-1">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] z-10 shrink-0 {{ $currentStage >= 3 ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-400 border border-zinc-300' }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </div>
                            <div class="flex-1 -mt-0.5">
                                <span class="text-xs sm:text-sm {{ $currentStage >= 3 ? 'font-bold text-zinc-900' : 'font-medium text-zinc-400' }}">
                                    3. Pesanan Diterima
                                </span>
                                <p class="text-[11px] sm:text-xs text-zinc-500 mt-0.5">
                                    Pesanan masuk ke antrean masak dapur resto.
                                </p>
                            </div>
                        </div>

                        <!-- Stage 4: Sedang Dibuat (Dimasak) -->
                        <div class="relative flex items-start gap-3.5 pl-1">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] z-10 shrink-0 {{ $order->status === 'cooking' ? 'bg-orange-500 text-white ring-4 ring-orange-100 animate-pulse' : ($currentStage > 4 ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-400 border border-zinc-300') }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48z"/></svg>
                            </div>
                            <div class="flex-1 -mt-0.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs sm:text-sm {{ $order->status === 'cooking' ? 'font-extrabold text-[#E84E1B]' : ($currentStage > 4 ? 'font-bold text-zinc-900' : 'font-medium text-zinc-400') }}">
                                        4. Sedang Dibuat
                                    </span>
                                    @if($order->cooking_time)
                                        <span class="text-[10px] sm:text-xs font-semibold text-zinc-400">{{ $order->cooking_time }}</span>
                                    @endif
                                </div>
                                <p class="text-[11px] sm:text-xs text-zinc-500 mt-0.5">
                                    Chef sedang memasak hidangan pesanan mejamu dengan segar.
                                </p>
                            </div>
                        </div>

                        <!-- Stage 5: Sedang Disiapkan -->
                        <div class="relative flex items-start gap-3.5 pl-1">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] z-10 shrink-0 {{ $order->status === 'preparing' ? 'bg-amber-500 text-white ring-4 ring-amber-100 animate-pulse' : ($currentStage > 5 ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-400 border border-zinc-300') }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/></svg>
                            </div>
                            <div class="flex-1 -mt-0.5">
                                <span class="text-xs sm:text-sm {{ $order->status === 'preparing' ? 'font-extrabold text-amber-600' : ($currentStage > 5 ? 'font-bold text-zinc-900' : 'font-medium text-zinc-400') }}">
                                    5. Sedang Disiapkan
                                </span>
                                <p class="text-[11px] sm:text-xs text-zinc-500 mt-0.5">
                                    Plating, pengecekan pesanan, dan penataan nampan.
                                </p>
                            </div>
                        </div>

                        <!-- Stage 6: Sedang Diantarkan -->
                        <div class="relative flex items-start gap-3.5 pl-1">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] z-10 shrink-0 {{ $order->status === 'delivering' ? 'bg-blue-600 text-white ring-4 ring-blue-100 animate-pulse' : ($currentStage > 6 ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-400 border border-zinc-300') }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            </div>
                            <div class="flex-1 -mt-0.5">
                                <span class="text-xs sm:text-sm {{ $order->status === 'delivering' ? 'font-extrabold text-blue-600' : ($currentStage > 6 ? 'font-bold text-zinc-900' : 'font-medium text-zinc-400') }}">
                                    6. Sedang Diantarkan
                                </span>
                                <p class="text-[11px] sm:text-xs text-zinc-500 mt-0.5">
                                    Waiter sedang melangkah mengantar makanan langsung ke {{ strtolower($table) }}.
                                </p>
                            </div>
                        </div>

                        <!-- Stage 7: Selesai -->
                        <div class="relative flex items-start gap-3.5 pl-1">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] z-10 shrink-0 {{ $currentStage >= 7 ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-400 border border-zinc-300' }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8zM6 1v3M10 1v3M14 1v3"/></svg>
                            </div>
                            <div class="flex-1 -mt-0.5">
                                <span class="text-xs sm:text-sm {{ $currentStage >= 7 ? 'font-extrabold text-emerald-600' : 'font-medium text-zinc-400' }}">
                                    7. Selesai
                                </span>
                                <p class="text-[11px] sm:text-xs text-zinc-500 mt-0.5">
                                    Makanan telah disajikan di mejamu. Selamat menikmati suapannya!
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Right Column: Digital Receipt & Actions (5 cols) -->
            <div class="md:col-span-6 lg:col-span-5 space-y-4 md:sticky md:top-24">
                <!-- Detail Pesanan Card (Receipt) -->
                <div class="bg-white border border-zinc-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
                        <h4 class="text-sm font-extrabold text-zinc-900">Detail Pesanan</h4>
                        <span class="text-xs text-zinc-400 font-medium">
                            {{ $order->created_at ? $order->created_at->format('d M Y, H:i') : '' }}
                        </span>
                    </div>

                    <div class="space-y-2.5 text-xs sm:text-sm divide-y divide-zinc-50">
                        @if(!empty($order->items))
                            @foreach($order->items as $it)
                                <div class="pt-2.5 first:pt-0 flex justify-between items-start">
                                    <div>
                                        <span class="font-bold text-zinc-800">
                                            {{ $it->quantity }} × {{ $it->product_name }}
                                        </span>
                                        @if(!empty($it->options_selected))
                                            <p class="text-[11px] text-zinc-400">{{ is_array($it->options_selected) ? implode(', ', $it->options_selected) : $it->options_selected }}</p>
                                        @endif
                                        @if(!empty($it->kitchen_note))
                                            <p class="text-[11px] text-amber-700 italic">"{{ $it->kitchen_note }}"</p>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-zinc-800 shrink-0">
                                        Rp{{ number_format($it->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        @endif

                        <div class="pt-2.5 flex justify-between text-zinc-500">
                            <span>Subtotal</span>
                            <span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-zinc-500">
                            <span>Biaya Layanan & PBJT (10%)</span>
                            <span>Rp{{ number_format(($order->service_fee + $order->pbjt_tax), 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-zinc-100 flex items-baseline justify-between">
                        <div>
                            <span class="text-xs sm:text-sm font-bold text-zinc-900 block">Total Pembayaran</span>
                            <span class="text-[10px] sm:text-xs font-bold {{ $order->isPaid() ? 'text-emerald-600 bg-emerald-50' : 'text-amber-600 bg-amber-50' }} px-2 py-0.5 rounded-md uppercase">
                                {{ $order->payment_status }} · {{ $order->payment_method }}
                            </span>
                        </div>
                        <span class="text-lg sm:text-xl font-black text-zinc-950">
                            Rp{{ number_format($order->total_amount, 0, ',', '.') }}
                        </span>
                    </div>

                    <!-- Struk Digital Download / Print -->
                    <a href="{{ route('orders.receipt', $order->order_number) }}" target="_blank" class="w-full mt-2 py-3 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl text-xs sm:text-sm font-bold text-zinc-700 flex items-center justify-center gap-2 transition-all">
                        <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        <span>Lihat & Unduh Struk Digital</span>
                    </a>
                </div>

                <!-- Support Banner -->
                <a href="{{ route('ai.chat') }}" class="block bg-white border border-zinc-200 rounded-3xl p-4 sm:p-5 hover:border-orange-300 transition-all shadow-sm group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center text-[#E84E1B]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                            </div>
                            <div>
                                <span class="text-xs sm:text-sm font-bold text-zinc-800 group-hover:text-[#E84E1B] transition-colors block">
                                    Ada pertanyaan soal pesanan?
                                </span>
                                <span class="text-[11px] text-zinc-400">Tanyakan ke Asisten Suap AI</span>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-zinc-400 group-hover:text-[#E84E1B] group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            </div>

        </div>

        <!-- Real-Time Auto Polling & QRIS Countdown Script -->
        <script>
            const orderNumber = "{{ $order->order_number }}";
            let currentStatus = "{{ $order->status }}";
            let currentPaymentStatus = "{{ $order->payment_status }}";
            const statusEndpoint = "{{ url('/api/orders') }}/" + encodeURIComponent(orderNumber) + "/status";

            function checkLiveOrderStatus(manual = false) {
                fetch(statusEndpoint)
                    .then(res => res.json())
                    .then(data => {
                        if (data && (data.status !== currentStatus || data.payment_status !== currentPaymentStatus)) {
                            // Status has changed! Trigger celebration & reload smoothly
                            try {
                                confetti({ particleCount: 80, spread: 60, origin: { y: 0.6 } });
                            } catch(e) {}
                            
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else if (manual) {
                            alert("Status saat ini: " + (data.payment_status_label || "Menunggu Pembayaran") + ". Sistem akan otomatis memperbarui tampilan saat pembayaran terdeteksi.");
                        }
                    })
                    .catch(err => {
                        console.log('Polling error:', err);
                        if (manual) {
                            alert("Sedang memeriksa status pesanan...");
                        }
                    });
            }

            // Poll every 3 seconds for seamless real-time order tracking
            setInterval(() => checkLiveOrderStatus(false), 3000);

            // 15-Minute Countdown Timer for QRIS
            const timerEl = document.getElementById('countdown-timer');
            if (timerEl) {
                let secondsRemaining = 15 * 60;
                const timerInterval = setInterval(() => {
                    secondsRemaining--;
                    if (secondsRemaining <= 0) {
                        clearInterval(timerInterval);
                        timerEl.innerText = "00:00 (Kedaluwarsa)";
                    } else {
                        const m = Math.floor(secondsRemaining / 60).toString().padStart(2, '0');
                        const s = (secondsRemaining % 60).toString().padStart(2, '0');
                        timerEl.innerText = `${m}:${s}`;
                    }
                }, 1000);
            }
        </script>
    @endif

</div>
@endsection
