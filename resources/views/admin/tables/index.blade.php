@extends('admin.layout', ['title' => 'QR Code Meja Restoran'])

@section('content')
<div class="space-y-6">

    @php
        $detectedLanIp = gethostbyname(gethostname());
        $isRemoteHost = str_contains(request()->getHost(), 'ngrok') || str_contains(request()->getHost(), 'qr.test');
        $defaultHost = $isRemoteHost ? url('/') : 'http://' . $detectedLanIp . '/qr/public';
        $activeBaseUrl = request()->get('base_url', $defaultHost);
        $activeBaseUrl = rtrim($activeBaseUrl, '/');
    @endphp

    <!-- Top Headline -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">QR Code Meja (Tent-Card Generator)</h2>
            <p class="text-xs text-zinc-400 mt-1">Setiap meja memiliki kode QR unik. Ketika dipindai pelanggan via kamera HP, langsung membuka menu meja tersebut.</p>
        </div>
        <div>
            <button onclick="document.getElementById('modal-add-table').classList.remove('hidden')" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-rose-950/40 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Meja Baru</span>
            </button>
        </div>
    </div>

    <!-- Network / Host Config for Mobile Scan -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-xs sm:text-sm font-extrabold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Pengaturan URL untuk Scan Kamera HP</span>
                </h3>
                <p class="text-[11px] text-zinc-400 mt-0.5">
                    Hubungkan HP ke Wi-Fi yang sama dengan laptop. QR Code di bawah otomatis menggunakan alamat IP laptop Anda.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-bold text-zinc-400">IP Wi-Fi Laptop:</span>
                <span class="px-2.5 py-1 bg-zinc-800 border border-zinc-700 text-emerald-400 font-mono text-xs rounded-lg font-bold">
                    {{ $detectedLanIp }}
                </span>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.tables.index') }}" class="pt-3 border-t border-zinc-800/80 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <div class="flex-1">
                <input 
                    type="text" 
                    name="base_url" 
                    value="{{ $activeBaseUrl }}" 
                    placeholder="http://{{ $detectedLanIp }}/qr/public atau URL Ngrok"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 font-mono"
                >
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition-all shrink-0">
                    Perbarui QR Code
                </button>
                <a href="{{ route('admin.tables.index') }}" class="px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 rounded-xl text-xs font-bold transition-all shrink-0">
                    Reset Default IP
                </a>
            </div>
        </form>
    </div>

    <!-- Table Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($tables as $tbl)
            @php
                $tableUrl = $activeBaseUrl . '/?meja=' . urlencode($tbl->table_number);
                $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($tableUrl);
            @endphp
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4 flex flex-col justify-between hover:border-zinc-700 transition-all">
                <div>
                    <!-- Top Info -->
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
                        <div>
                            <h3 class="text-lg font-black text-white">{{ $tbl->table_number }}</h3>
                            <span class="text-[11px] text-zinc-400">{{ $tbl->location ?? 'Indoor Dining' }}</span>
                        </div>
                        <span class="text-xs font-bold text-zinc-400 bg-zinc-800 px-2.5 py-1 rounded-xl">
                            {{ $tbl->capacity }} Kursi
                        </span>
                    </div>

                    <!-- Tent-Card Preview -->
                    <div class="my-4 p-4 bg-white text-zinc-900 rounded-2xl text-center shadow-inner space-y-2">
                        <div class="text-[10px] font-black uppercase tracking-wider text-[#E84E1B]">
                            SUAP DINE-IN SMART ORDER
                        </div>
                        <div class="w-40 h-40 mx-auto bg-zinc-50 p-2 rounded-xl border border-zinc-200 flex items-center justify-center">
                            <img src="{{ $qrApiUrl }}" alt="QR {{ $tbl->table_number }}" class="w-full h-full object-contain">
                        </div>
                        <div class="text-xs font-extrabold text-zinc-950">
                            {{ $tbl->table_number }}
                        </div>
                        <p class="text-[9px] text-zinc-500">Scan via kamera HP untuk pesan makanan</p>
                        <div class="text-[9px] text-zinc-400 font-mono truncate px-1 pt-1 border-t border-zinc-100" title="{{ $tableUrl }}">
                            {{ $tableUrl }}
                        </div>
                    </div>
                </div>

                <!-- Action Links -->
                <div class="space-y-2 pt-2 border-t border-zinc-800">
                    <a href="{{ $tableUrl }}" target="_blank" class="w-full py-2 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        <span>Tes Buka di Tab Baru</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

</div>

<!-- Modal Add Table -->
<div id="modal-add-table" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
            <h3 class="text-base font-black text-white">Tambah Meja Restoran Baru</h3>
            <button onclick="document.getElementById('modal-add-table').classList.add('hidden')" class="text-zinc-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('admin.tables.store') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Nama / Nomor Meja</label>
                <input type="text" name="table_number" required placeholder="Contoh: Meja 13" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Kapasitas Kursi</label>
                <input type="number" name="capacity" value="4" min="1" required class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Area / Lokasi</label>
                <input type="text" name="location" value="Indoor Dining Area" placeholder="Contoh: Outdoor Terrace / Lantai 2" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div class="pt-3 border-t border-zinc-800 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-add-table').classList.add('hidden')" class="px-4 py-2 bg-zinc-800 text-zinc-300 rounded-xl text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold">Simpan Meja</button>
            </div>
        </form>
    </div>
</div>
@endsection

