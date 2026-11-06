@extends('layouts.app')

@section('content')
<div class="py-2 pb-24 md:pb-12 max-w-5xl mx-auto space-y-4">

    <!-- Top Bar -->
    <div class="py-1 flex items-center justify-between border-b border-zinc-200/80 pb-3">
        <a href="{{ route('menu') }}" class="flex items-center gap-1.5 text-xs sm:text-sm font-bold text-zinc-800 hover:text-zinc-600 transition-all">
            <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            <span>Kembali ke Menu</span>
        </a>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1.5 bg-zinc-50 border border-zinc-200/80 rounded-full px-3 py-1 text-xs font-bold text-zinc-800">
                <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3m0-7v3m4 0h3"/></svg>
                <span>{{ $table }}</span>
            </div>
            <span class="text-xs font-extrabold text-[#E84E1B] bg-orange-50 border border-orange-200 px-2.5 py-1 rounded-full flex items-center gap-1.5" title="Model: openai/gpt-oss-120b">
                <span class="w-1.5 h-1.5 rounded-full bg-[#E84E1B] animate-pulse"></span>
                <span>AI Aktif</span>
                <span class="text-[10px] bg-orange-100 text-[#E84E1B] px-1.5 py-0.2 rounded font-mono font-bold">gpt-oss-120b</span>
            </span>
        </div>
    </div>

    <!-- Responsive Layout: 1 col on mobile, 12 cols on desktop/tablet -->
    <div class="md:grid md:grid-cols-12 md:gap-8 items-start space-y-4 md:space-y-0">

        <!-- Left Column: Info & Quick Prompts Sidebar (Desktop) -->
        <div class="md:col-span-4 space-y-4">
            <!-- Welcome Banner -->
            <div class="bg-white border border-zinc-200/80 rounded-3xl p-5 shadow-sm space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-orange-50 border border-orange-100 flex items-center justify-center text-[#E84E1B] shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-zinc-900 leading-snug">
                            Tanya dulu, suap kemudian.
                        </h3>
                        <p class="text-xs text-zinc-500 mt-1">
                            Menu, bahan, dan pesanan — di satu chat.
                        </p>
                        <span class="inline-block mt-1.5 text-[10px] font-mono text-zinc-400 bg-zinc-100 px-2 py-0.5 rounded-md">
                            Engine: openai/gpt-oss-120b
                        </span>
                    </div>
                </div>

                <!-- Quick Action Pills -->
                <div class="pt-3 border-t border-zinc-100 space-y-2">
                    <span class="text-[10px] uppercase font-bold text-zinc-400 tracking-wider block">
                        PINTASAN PERTANYAAN:
                    </span>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="sendQuickPrompt('Cek alergi')" class="bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                            Cek alergi
                        </button>
                        <button type="button" onclick="sendQuickPrompt('Status pesanan')" class="bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                            Status pesanan
                        </button>
                        <button type="button" onclick="sendQuickPrompt('Camilan nabati')" class="bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                            Camilan nabati
                        </button>
                        <button type="button" onclick="sendQuickPrompt('Menu pedas')" class="bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                            Menu pedas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Disclaimer Box -->
            <div class="bg-zinc-50 border border-zinc-200/70 rounded-2xl p-4 text-[11px] text-zinc-500 leading-relaxed shadow-2xs">
                💡 <span class="font-semibold text-zinc-700">Tips Suap AI:</span> Anda dapat menanyakan komposisi rempah, tingkat kepedasan, pilihan vegetarian, hingga perkiraan waktu antar ke {{ strtolower($table) }}.
            </div>
        </div>

        <!-- Right Column: Interactive Chat Stream -->
        <div class="md:col-span-8 bg-white border border-zinc-200/80 rounded-3xl p-5 shadow-sm space-y-4 flex flex-col justify-between min-h-[520px]">
            <!-- Chat Input Form Fixed Top -->
            <form id="ai-chat-form" action="{{ route('ai.send') }}" method="POST" class="space-y-1.5">
                @csrf
                <div class="relative flex items-center bg-zinc-50 border border-zinc-200 rounded-2xl shadow-inner overflow-hidden pr-2 focus-within:border-zinc-400 focus-within:bg-white transition-all">
                    <input 
                        type="text" 
                        name="message" 
                        id="chat-input" 
                        placeholder="Tanya apa saja soal menu..." 
                        class="flex-1 py-3 pl-4 pr-2 text-xs sm:text-sm placeholder:text-zinc-400 text-zinc-800 bg-transparent focus:outline-none"
                        required
                    >
                    <button 
                        type="submit" 
                        class="w-9 h-9 rounded-xl bg-[#E84E1B] hover:bg-[#d84414] active:scale-95 text-white flex items-center justify-center transition-all shadow-sm"
                    >
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0 6 6m-6-6-6 6"/></svg>
                    </button>
                </div>
                <p class="text-[10px] text-zinc-400 text-center">
                    Suap AI bisa keliru. Info alergi perlu dikonfirmasi ke tim restoran.
                </p>
            </form>

            <!-- Timestamp Divider -->
            <div class="text-center my-2">
                <span class="text-[10px] font-bold text-zinc-400 bg-zinc-100 px-3 py-1 rounded-full uppercase tracking-wider">
                    Hari ini · 12.24
                </span>
            </div>

            <!-- Messages Timeline List -->
            <div class="space-y-4 flex-1 overflow-y-auto max-h-[500px] pr-2">
                @foreach($messages as $msg)
                    @if($msg->sender === 'user')
                        <!-- User Bubble -->
                        <div class="flex justify-end">
                            <div class="bg-[#18181B] text-white text-xs sm:text-sm rounded-2xl rounded-tr-xs px-4 py-3 max-w-[85%] sm:max-w-[70%] shadow-sm leading-relaxed">
                                {{ $msg->text }}
                            </div>
                        </div>
                    @else
                        <!-- AI Bubble -->
                        <div class="space-y-2.5 max-w-[95%] sm:max-w-[85%]">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-zinc-700">
                                <svg class="w-4 h-4 text-[#E84E1B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                                <span>SUAP AI</span>
                            </div>

                            <div class="bg-zinc-50 border border-zinc-200/90 text-zinc-800 text-xs sm:text-sm rounded-2xl rounded-tl-xs p-4 shadow-2xs leading-relaxed">
                                {{ $msg->text }}
                            </div>

                            <!-- Allergen Alert Box -->
                            @if($msg->has_allergen_warning)
                                <div class="bg-red-50/70 border border-red-200/70 rounded-2xl p-3.5 flex items-start gap-2.5">
                                    <svg class="w-4 h-4 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <p class="text-xs text-red-900 leading-relaxed font-normal">
                                        Dapur & alat dipakai bersama; bebas alergen tidak dapat dijamin. Jika ini alergi, minta tim restoran memastikan sebelum memesan.
                                    </p>
                                </div>
                            @endif

                            <!-- Embedded Product Card in Chat -->
                            @if($msg->product)
                                @php $prod = $msg->product; @endphp
                                <div class="bg-white border border-zinc-200 rounded-2xl overflow-hidden shadow-sm flex flex-col sm:flex-row items-center max-w-lg">
                                    <div class="relative w-full sm:w-44 aspect-video sm:aspect-square bg-zinc-100 shrink-0">
                                        <img src="{{ $prod->image }}" alt="{{ $prod->name }}" class="w-full h-full object-cover">
                                        <span class="absolute top-2 left-2 text-[9px] font-extrabold px-2 py-0.5 rounded-md bg-white/95 text-zinc-900 shadow-sm">
                                            PILIHAN BUAT KAMU
                                        </span>
                                    </div>
                                    <div class="p-3.5 flex-1 w-full">
                                        <div class="flex items-baseline justify-between mb-1">
                                            <h4 class="font-extrabold text-sm text-zinc-900">{{ $prod->name }}</h4>
                                            <span class="font-black text-xs text-zinc-900">Rp{{ number_format($prod->price, 0, ',', '.') }}</span>
                                        </div>
                                        <p class="text-[11px] text-zinc-500 line-clamp-2">
                                            {{ $prod->description }}
                                        </p>
                                        <div class="mt-3 pt-2.5 border-t border-zinc-100 flex items-center justify-between">
                                            <span class="text-[11px] font-bold text-zinc-600">1 di keranjang</span>
                                            <form action="{{ route('cart.add') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="dish_id" value="{{ $prod->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <input type="hidden" name="is_ai_pick" value="1">
                                                <button type="submit" class="bg-[#E84E1B] hover:bg-[#d84414] text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-sm transition-all">
                                                    + Tambah Lagi
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Cart Ready Card -->
                            @if($msg->show_cart_ready_card)
                                <a href="{{ route('cart') }}" class="block bg-orange-50/70 hover:bg-orange-100 border border-orange-200/80 rounded-2xl p-3.5 shadow-sm transition-all max-w-md">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-xs sm:text-sm font-bold text-zinc-900 block">Keranjangmu siap</span>
                                            <span class="text-[11px] text-zinc-500">
                                                {{ $cartCount }} menu · Subtotal Rp{{ number_format($subtotal, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs font-bold text-[#E84E1B]">
                                            <span>Lihat</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </div>
                                    </div>
                                </a>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

        </div>

    </div>

</div>

<script>
    function sendQuickPrompt(promptText) {
        document.getElementById('chat-input').value = promptText;
        document.getElementById('ai-chat-form').submit();
    }
</script>
@endsection
