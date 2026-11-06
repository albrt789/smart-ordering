@extends('layouts.app')

@section('content')
<div class="py-2 pb-24 md:pb-12 max-w-5xl mx-auto space-y-6">

    <!-- Top Navigation Bar -->
    <div class="py-1 flex items-center justify-between border-b border-zinc-200/80 pb-3">
        <a href="{{ route('menu') }}" class="flex items-center gap-1.5 text-xs sm:text-sm font-bold text-zinc-800 hover:text-zinc-600 transition-all">
            <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            <span>Kembali ke Menu</span>
        </a>
        <span class="text-xs sm:text-sm font-extrabold text-zinc-900">Kelola & Cetak QR Meja Restoran</span>
    </div>

    <!-- Active Table Selector Grid (Responsive 12 cols on desktop!) -->
    <div class="bg-white border border-zinc-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-3.5">
        <div>
            <h3 class="text-xs sm:text-sm font-extrabold text-zinc-900">Pilih Meja untuk Disimulasikan:</h3>
            <p class="text-xs text-zinc-400">Pilih meja untuk mensimulasikan scan QR dari meja fisik restoran</p>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-12 gap-2">
            @foreach($tablesList as $t)
                @php $isCurrent = ($table === $t); @endphp
                <a 
                    href="{{ route('menu', ['meja' => $t]) }}" 
                    class="py-2.5 px-2 text-center rounded-xl text-xs font-bold border transition-all {{ $isCurrent ? 'border-[#E84E1B] bg-orange-50 text-[#E84E1B] shadow-sm' : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50' }}"
                >
                    {{ $t }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Printable Table Tent Card -->
    <div class="border border-zinc-200 rounded-3xl p-6 sm:p-10 bg-white text-center space-y-4 shadow-sm max-w-lg mx-auto" id="printable-card">
        <span class="text-[10px] sm:text-xs uppercase tracking-widest font-black text-[#E84E1B] bg-orange-50 px-3 py-1 rounded-full border border-orange-200">
            SUAP. SMART DINE-IN SYSTEM
        </span>

        <h4 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight">
            Scan untuk Pesan di {{ $table }}
        </h4>

        <!-- Real Generated QR Code -->
        @php
            $targetUrl = url('/?meja=' . urlencode($table));
            $qrApi = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($targetUrl);
        @endphp
        <div class="bg-zinc-50 p-5 rounded-3xl border border-zinc-200/80 inline-block mx-auto shadow-inner">
            <img src="{{ $qrApi }}" alt="QR Code {{ $table }}" class="w-48 h-48 sm:w-56 sm:h-56 rounded-xl mix-blend-multiply mx-auto">
        </div>

        <p class="text-xs sm:text-sm text-zinc-500 max-w-xs mx-auto leading-relaxed">
            Arahkan kamera smartphone ke kode QR di atas meja untuk membuka menu & memesan tanpa perlu ke kasir.
        </p>

        <div class="pt-3 flex items-center justify-center gap-3">
            <button 
                type="button" 
                onclick="window.print()" 
                class="flex items-center gap-1.5 bg-zinc-950 hover:bg-zinc-800 text-white text-xs sm:text-sm font-bold py-2.5 px-5 rounded-xl shadow-sm transition-all"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.03-.48-2.06-.72-3.09h12c-.24 1.03-.48 2.06-.72 3.09M6 18h12M6 6h12v4H6V6z"/></svg>
                <span>Cetak Kartu Meja</span>
            </button>
            <a 
                href="{{ $targetUrl }}" 
                class="flex items-center gap-1.5 bg-orange-50 hover:bg-orange-100 text-[#E84E1B] border border-orange-200 text-xs sm:text-sm font-bold py-2.5 px-4 rounded-xl transition-all"
            >
                <span>Buka Menu Meja Ini</span>
            </a>
        </div>
    </div>

</div>
@endsection
