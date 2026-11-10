<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir & Barista — Fountain Hide POS</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.4/dist/confetti.browser.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#0F1117] text-zinc-100 min-h-screen antialiased selection:bg-[#E84E1B] selection:text-white">

    <!-- Top Navigation for Cashier -->
    <header class="bg-zinc-900/90 border-b border-zinc-800 sticky top-0 z-40 backdrop-blur-md px-4 sm:px-6 lg:px-8 py-3.5">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Left: Brand & Status -->
            <div class="flex items-center gap-3">
                <a href="{{ route('cashier.dashboard') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo-white.png') }}" alt="Logo" class="h-9 w-auto object-contain">
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-black tracking-tight text-white uppercase">Kasir & Barista Fountain Hide</h1>
                        <span class="bg-zinc-800 text-zinc-300 border border-zinc-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                            POS Station
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-zinc-400">
                        <span>Petugas: <strong class="text-zinc-200">{{ Auth::user()->name }}</strong></span>
                        <span>•</span>
                        <span class="text-emerald-400 flex items-center gap-1 font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live Real-Time
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Sound Toggle & Logout -->
            <div class="flex items-center gap-3">
                <button id="sound-btn" onclick="toggleSound()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-zinc-800 text-zinc-200 hover:bg-zinc-700 border border-zinc-700 transition-all">
                    <span id="sound-icon">🔔</span>
                    <span id="sound-text">Audio Aktif</span>
                </button>

                <form action="{{ route('cashier.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-red-950/80 hover:bg-red-900 text-red-200 border border-red-800/80 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Flash Notifications -->
        @if(session('success'))
            <div class="p-4 bg-emerald-950/90 border border-emerald-700 rounded-2xl text-xs sm:text-sm text-emerald-200 font-semibold flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-950/90 border border-red-700 rounded-2xl text-xs sm:text-sm text-red-200 font-semibold flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white">&times;</button>
            </div>
        @endif

        <!-- Quick Summary KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <!-- Total Hari Ini -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Total Hari Ini</div>
                <div class="text-2xl font-black text-white mt-1">{{ $counts['total'] }}</div>
                <div class="text-[10px] text-zinc-500 mt-0.5">Pesanan masuk</div>
            </div>

            <!-- Menunggu Tunai (Cash Alert) -->
            <div class="bg-blue-950/40 border border-blue-800/80 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-blue-300 uppercase tracking-wider flex items-center gap-1.5">
                    @if($counts['waiting_cash'] > 0)
                        <span class="w-2 h-2 rounded-full bg-blue-400 animate-ping"></span>
                    @endif
                    <span>Menunggu Tunai</span>
                </div>
                <div class="text-2xl font-black text-blue-200 mt-1">{{ $counts['waiting_cash'] }}</div>
                <div class="text-[10px] text-blue-400 mt-0.5">Butuh konfirmasi kasir</div>
            </div>

            <!-- Diterima Dapur -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Pesanan Diterima</div>
                <div class="text-2xl font-black text-white mt-1">{{ $counts['received'] }}</div>
                <div class="text-[10px] text-zinc-500 mt-0.5">Antrean masak</div>
            </div>

            <!-- Sedang Dimasak -->
            <div class="bg-orange-950/30 border border-orange-800/60 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-orange-400 uppercase tracking-wider">Sedang Dibuat</div>
                <div class="text-2xl font-black text-orange-200 mt-1">{{ $counts['cooking'] }}</div>
                <div class="text-[10px] text-orange-400/80 mt-0.5">Dapur beraksi</div>
            </div>

            <!-- Sedang Diantar -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Sedang Diantar</div>
                <div class="text-2xl font-black text-white mt-1">{{ $counts['delivering'] }}</div>
                <div class="text-[10px] text-zinc-500 mt-0.5">Menuju meja</div>
            </div>

            <!-- Selesai Hari Ini -->
            <div class="bg-emerald-950/30 border border-emerald-800/60 rounded-2xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Selesai</div>
                <div class="text-2xl font-black text-emerald-300 mt-1">{{ $counts['completed'] }}</div>
                <div class="text-[10px] text-emerald-400/80 mt-0.5">Dine-in tuntas</div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto no-scrollbar">
                <a href="{{ route('cashier.dashboard', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ $statusFilter === 'all' ? 'bg-[#E84E1B] text-white' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700' }}">
                    Semua Status
                </a>
                <a href="{{ route('cashier.dashboard', ['status' => 'waiting_payment']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ $statusFilter === 'waiting_payment' ? 'bg-blue-600 text-white' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700' }}">
                    Menunggu Bayar ({{ $counts['waiting_cash'] }})
                </a>
                <a href="{{ route('cashier.dashboard', ['status' => 'received']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ $statusFilter === 'received' ? 'bg-amber-600 text-white' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700' }}">
                    Diterima ({{ $counts['received'] }})
                </a>
                <a href="{{ route('cashier.dashboard', ['status' => 'cooking']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ $statusFilter === 'cooking' ? 'bg-orange-600 text-white' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700' }}">
                    Sedang Dibuat ({{ $counts['cooking'] }})
                </a>
                <a href="{{ route('cashier.dashboard', ['status' => 'delivering']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ $statusFilter === 'delivering' ? 'bg-indigo-600 text-white' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700' }}">
                    Diantar ({{ $counts['delivering'] }})
                </a>
                <a href="{{ route('cashier.dashboard', ['status' => 'completed']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ $statusFilter === 'completed' ? 'bg-emerald-600 text-white' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700' }}">
                    Selesai
                </a>
            </div>

            <!-- Search Bar -->
            <form action="{{ route('cashier.dashboard') }}" method="GET" class="w-full md:w-72">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <div class="relative">
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ $search }}" 
                        placeholder="Cari no. pesanan, meja, nama..." 
                        class="w-full bg-zinc-950 border border-zinc-700 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder:text-zinc-500 focus:outline-none focus:border-[#E84E1B]"
                    >
                    <svg class="w-4 h-4 text-zinc-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
            </form>
        </div>

        <!-- Orders Queue Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($orders as $order)
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-lg space-y-4 hover:border-zinc-700 transition-all flex flex-col justify-between">
                    
                    <!-- Card Top Header -->
                    <div>
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-black text-white">{{ $order->order_number }}</span>
                                    <span class="bg-[#E84E1B] text-white text-xs font-black px-2.5 py-0.5 rounded-lg shadow-2xs">
                                        {{ $order->table_number }}
                                    </span>
                                </div>
                                <div class="text-xs text-zinc-400 mt-1 flex items-center gap-2">
                                    <span>Pelanggan: <strong class="text-zinc-200">{{ $order->customer_name }}</strong></span>
                                    <span>•</span>
                                    <span>{{ $order->order_time ?? $order->created_at->format('H.i') }}</span>
                                </div>
                            </div>

                            <!-- Payment Status Badge -->
                            <div class="text-right">
                                @if($order->isPaid())
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400 bg-emerald-950/80 border border-emerald-800 px-2.5 py-1 rounded-full uppercase">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        <span>LUNAS ({{ strtoupper($order->payment_method) }})</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-300 bg-amber-950/80 border border-amber-800 px-2.5 py-1 rounded-full uppercase animate-pulse">
                                        <span>BELUM BAYAR ({{ strtoupper($order->payment_method) }})</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Special Action: Cash Payment Confirmation Needed -->
                        @if($order->payment_method === 'cash' && !$order->isPaid())
                            <div class="mt-3 p-3 bg-blue-950/60 border border-blue-800 rounded-2xl flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs font-bold text-blue-200">Pelanggan Bayar Tunai</div>
                                    <div class="text-xs text-blue-300 font-extrabold">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                </div>
                                <form action="{{ route('cashier.orders.confirm_cash', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs px-3 py-2 rounded-xl shadow-sm transition-all flex items-center gap-1 active:scale-95">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        <span>Konfirmasi Tunai</span>
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Special Alert for Pending QRIS -->
                        @if($order->payment_method === 'qris' && !$order->isPaid())
                            <div class="mt-3 p-2.5 bg-amber-950/40 border border-amber-800/70 rounded-xl text-xs text-amber-300 flex items-center justify-between">
                                <span>Menunggu scan QRIS customer...</span>
                                <form action="{{ route('payment.simulate', $order->order_number) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="Simulasi instan konfirmasi webhook gateway" class="bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold px-2 py-1 rounded-lg">
                                        Bypass (Sandbox)
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Menu Items Ordered -->
                        <div class="mt-4 pt-3 border-t border-zinc-800/80 space-y-2 text-xs">
                            @foreach($order->items as $item)
                                <div class="flex justify-between items-start text-zinc-300">
                                    <div>
                                        <span class="font-bold text-white">{{ $item->quantity }}× {{ $item->product_name }}</span>
                                        @if(!empty($item->options_text))
                                            <div class="text-[11px] text-zinc-400">• {{ $item->options_text }}</div>
                                        @endif
                                        @if(!empty($item->kitchen_notes))
                                            <div class="text-[11px] text-amber-400 italic">"{{ $item->kitchen_notes }}"</div>
                                        @endif
                                    </div>
                                    <span class="font-medium text-zinc-300 shrink-0">
                                        Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach

                            <div class="pt-2 border-t border-zinc-800 flex justify-between font-black text-sm text-white">
                                <span>Total Tagihan:</span>
                                <span class="text-[#E84E1B]">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Stage Controls (Advance Order Status) -->
                    <div class="pt-3 border-t border-zinc-800 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-400">Tahap Pengerjaan:</span>
                            <span class="font-bold text-[#E84E1B] bg-orange-950/60 border border-orange-900/60 px-2 py-0.5 rounded-md">
                                {{ $order->status_label }}
                            </span>
                        </div>

                        <!-- Status Action Buttons -->
                        <form action="{{ route('cashier.orders.update_status', $order->id) }}" method="POST" class="grid grid-cols-2 gap-1.5 pt-1">
                            @csrf
                            @if($order->status === 'received' || $order->status === 'paid' || $order->status === 'waiting_payment')
                                <button type="submit" name="status" value="cooking" class="col-span-2 py-2.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition-all active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48z"/></svg>
                                    <span>Mulai Masak (Dapur)</span>
                                </button>
                            @elseif($order->status === 'cooking')
                                <button type="submit" name="status" value="preparing" class="col-span-2 py-2.5 bg-amber-600 hover:bg-amber-500 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition-all active:scale-95">
                                    <span>Masakan Matang & Siapkan Nampan</span>
                                </button>
                            @elseif($order->status === 'preparing')
                                <button type="submit" name="status" value="delivering" class="col-span-2 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition-all active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    <span>Antar ke {{ $order->table_number }}</span>
                                </button>
                            @elseif($order->status === 'delivering')
                                <button type="submit" name="status" value="completed" class="col-span-2 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition-all active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    <span>Tandai Selesai Disajikan</span>
                                </button>
                            @else
                                <div class="col-span-2 py-2 bg-zinc-800 text-zinc-400 rounded-xl text-center text-xs font-semibold">
                                    Pesanan Selesai Disajikan
                                </div>
                            @endif

                            <a href="{{ route('orders.receipt', $order->order_number) }}" target="_blank" class="py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 rounded-xl font-semibold text-xs flex items-center justify-center gap-1 transition-all">
                                <span>Cetak Struk</span>
                            </a>

                            @if($order->status !== 'completed' && $order->status !== 'cancelled')
                                <button type="submit" name="status" value="cancelled" onclick="return confirm('Yakin ingin membatalkan pesanan ini?')" class="py-2 bg-zinc-800 hover:bg-red-950/80 text-zinc-400 hover:text-red-300 rounded-xl font-semibold text-xs transition-all">
                                    Batalkan
                                </button>
                            @endif
                        </form>
                    </div>

                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-zinc-900 border border-zinc-800 rounded-3xl p-8 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-zinc-800 flex items-center justify-center mx-auto text-zinc-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 14.14 14.14"/></svg>
                    </div>
                    <div class="text-base font-bold text-zinc-300">Tidak ada pesanan pada filter ini</div>
                    <p class="text-xs text-zinc-500">Pesanan baru dari smartphone pelanggan akan otomatis muncul secara live di sini.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="pt-4">
            {{ $orders->links() }}
        </div>

    </main>

    <!-- Audio Chime & Real-Time Polling Script -->
    <script>
        let soundEnabled = true;
        let lastOrderId = {{ \App\Models\Order::max('id') ?? 0 }};

        function toggleSound() {
            soundEnabled = !soundEnabled;
            document.getElementById('sound-icon').innerText = soundEnabled ? '🔔' : '🔕';
            document.getElementById('sound-text').innerText = soundEnabled ? 'Audio Aktif' : 'Mute';
            if (soundEnabled) {
                playChime();
            }
        }

        // Web Audio API Synthesizer Chime (No external audio files needed!)
        function playChime() {
            if (!soundEnabled) return;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                
                // Tone 1
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                gain1.gain.setValueAtTime(0.2, ctx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start();
                osc1.stop(ctx.currentTime + 0.5);

                // Tone 2
                setTimeout(() => {
                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880, ctx.currentTime); // A5
                    gain2.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.7);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start();
                    osc2.stop(ctx.currentTime + 0.7);
                }, 150);
            } catch (e) {
                console.log('Audio playback error:', e);
            }
        }

        // Live Order Polling
        function pollIncomingOrders() {
            fetch(`/kasir/api/orders/live?last_id=${lastOrderId}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.has_new) {
                        playChime();
                        lastOrderId = data.latest_id;
                        // Trigger confetti and smooth page reload to display new order cards
                        try {
                            confetti({ particleCount: 100, spread: 70, origin: { y: 0.2 } });
                        } catch(e) {}
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                })
                .catch(err => console.log('Cashier poll error:', err));
        }

        // Poll every 3.5 seconds
        setInterval(pollIncomingOrders, 3500);
    </script>

</body>
</html>

