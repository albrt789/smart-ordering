<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System (KDS) — SUAP.</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-zinc-950 text-white min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-zinc-900 border-b border-zinc-800 px-6 py-4 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-600/20 border border-orange-500/40 flex items-center justify-center text-[#E84E1B]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6v-7.13zM6 17h12"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-black tracking-tight">Kitchen Display System (KDS) — SUAP.</h1>
                <p class="text-xs text-zinc-400">Layar monitoring pesanan dapur real-time</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-1.5 text-xs text-emerald-400 bg-emerald-950/80 border border-emerald-800/80 px-3 py-1.5 rounded-full font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Dapur Siaga</span>
            </div>
            <a href="{{ route('menu') }}" class="text-xs font-bold bg-zinc-800 hover:bg-zinc-700 text-zinc-200 px-3 py-1.5 rounded-xl transition-all">
                ← Kembali ke Menu
            </a>
        </div>
    </header>

    <!-- Content Grid -->
    <main class="flex-1 p-6 overflow-y-auto">
        @if($orders->isEmpty())
            <div class="text-center py-20 text-zinc-500">
                <p class="text-sm font-bold">Belum ada pesanan aktif.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($orders as $ord)
                    @php
                        $isCooking = ($ord->status === 'cooking');
                        $isDelivering = ($ord->status === 'delivering');
                        $isDone = ($ord->status === 'completed');
                    @endphp
                    <div class="rounded-2xl border p-4 flex flex-col justify-between transition-all {{ $isDone ? 'bg-zinc-900/40 border-zinc-800 opacity-60' : ($isCooking ? 'bg-zinc-900 border-orange-500/80 shadow-lg shadow-orange-950/30 ring-1 ring-orange-500/30' : 'bg-zinc-900 border-zinc-700') }}">
                        <div>
                            <!-- Header Card -->
                            <div class="flex items-start justify-between border-b border-zinc-800 pb-3 mb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg font-black text-white">{{ $ord->table_number }}</span>
                                        <span class="text-xs font-bold text-zinc-400">{{ $ord->order_number }}</span>
                                    </div>
                                    <span class="text-xs text-orange-400 font-semibold">Tamu: {{ $ord->customer_name }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-mono font-bold text-zinc-400">{{ $ord->payment_time }}</span>
                                    <div class="mt-1">
                                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md {{ $isDone ? 'bg-zinc-800 text-zinc-400' : ($isDelivering ? 'bg-blue-950 text-blue-400 border border-blue-800' : ($isCooking ? 'bg-orange-950 text-orange-400 border border-orange-800 animate-pulse' : 'bg-emerald-950 text-emerald-400 border border-emerald-800')) }}">
                                            {{ $isDone ? 'Selesai' : ($isDelivering ? 'Siap Antar' : ($isCooking ? 'Dimasak' : 'Baru')) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Items -->
                            <div class="space-y-2 mb-4">
                                @if(!empty($ord->items))
                                    @foreach($ord->items as $it)
                                        <div class="bg-zinc-950/60 rounded-xl p-2.5 border border-zinc-800">
                                            <div class="flex justify-between items-baseline font-bold text-sm text-zinc-100">
                                                <span>{{ $it['quantity'] }} × {{ $it['name'] }}</span>
                                            </div>
                                            @if(!empty($it['options']))
                                                <p class="text-xs text-zinc-400 mt-0.5">{{ $it['options'] }}</p>
                                            @endif
                                            @if(!empty($it['note']))
                                                <div class="mt-1 bg-amber-950/60 border border-amber-800/80 text-amber-300 text-xs px-2 py-1 rounded-lg">
                                                    ⚠️ <span class="font-semibold">Catatan:</span> {{ $it['note'] }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-3 border-t border-zinc-800">
                            @if($ord->status === 'paid')
                                <form action="{{ route('kitchen.status', $ord->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="cooking">
                                    <button type="submit" class="w-full bg-[#E84E1B] hover:bg-[#d84414] text-white py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors">
                                        🔥 <span>Mulai Masak</span>
                                    </button>
                                </form>
                            @elseif($ord->status === 'cooking')
                                <form action="{{ route('kitchen.status', $ord->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="delivering">
                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors">
                                        🚲 <span>Siap Diantar</span>
                                    </button>
                                </form>
                            @elseif($ord->status === 'delivering')
                                <form action="{{ route('kitchen.status', $ord->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition-colors">
                                        ✅ <span>Tandai Selesai (Tiba di Meja)</span>
                                    </button>
                                </form>
                            @else
                                <div class="w-full text-center py-1.5 text-xs text-zinc-500 font-semibold">
                                    ✓ Pesanan Selesai Disajikan
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

</body>
</html>

