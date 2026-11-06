@extends('layouts.app')

@section('content')
<div class="py-2 pb-24 md:pb-12 max-w-5xl mx-auto">

    <!-- Top Navigation Bar -->
    <div class="py-2 flex items-center justify-between mb-4 border-b border-zinc-200/80">
        <a href="{{ route('menu') }}" class="flex items-center gap-2 text-xs sm:text-sm font-bold text-zinc-800 hover:text-zinc-600 transition-all">
            <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            <span>Kembali ke Menu</span>
        </a>
        <div class="flex items-center gap-1.5 bg-zinc-50 border border-zinc-200/80 rounded-full px-3 py-1 text-xs font-bold text-zinc-800">
            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3m0-7v3m4 0h3"/></svg>
            <span>{{ $table }}</span>
        </div>
    </div>

    <form id="add-to-cart-form" action="{{ route('cart.add') }}" method="POST">
        @csrf
        <input type="hidden" name="dish_id" value="{{ $dish->id }}">

        <!-- Responsive Layout: 1 col on mobile, 2 cols on tablet/desktop -->
        <div class="md:grid md:grid-cols-12 md:gap-8 items-start space-y-6 md:space-y-0">

            <!-- Left Col (Photo & Allergen) -->
            <div class="md:col-span-6 lg:col-span-6 space-y-4">
                <!-- Main Food Image -->
                <div class="relative rounded-3xl overflow-hidden aspect-4/3 w-full bg-zinc-100 shadow-md">
                    <img src="{{ $dish->image }}" alt="{{ $dish->name }}" class="w-full h-full object-cover {{ $dish->isOutOfStock() ? 'grayscale opacity-65' : '' }}">
                    @if($dish->isOutOfStock())
                        <div class="absolute inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center">
                            <span class="bg-black/90 text-white border border-zinc-700 text-xs sm:text-sm font-black px-4 py-2 rounded-xl uppercase tracking-wider shadow-xl">
                                STOK HABIS HARI INI
                            </span>
                        </div>
                    @elseif($dish->is_signature)
                        <div class="absolute top-3.5 left-3.5 flex items-center gap-1.5 bg-zinc-950/85 backdrop-blur-md text-white text-[10px] sm:text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                            <svg class="w-3.5 h-3.5 text-[#E84E1B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                            <span>FAVORIT SUAP</span>
                        </div>
                    @endif
                </div>

                <!-- Allergen Alert Card -->
                <div class="bg-amber-50/70 border border-amber-200/70 rounded-2xl p-4 flex items-start gap-3 shadow-2xs">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <div class="text-xs text-amber-900 leading-relaxed">
                        <span class="font-bold block mb-0.5">Punya alergi makanan?</span>
                        <p class="text-amber-800/90 font-normal">
                            {{ $dish->allergens ?? 'Restoran kami menggunakan bahan-bahan pilihan. Jika memiliki alergi tertentu, tanyakan info bahan ke Asisten Suap AI.' }}
                        </p>
                    </div>
                </div>

                <!-- Komposisi Card -->
                @if($dish->composition)
                    <div class="bg-zinc-50 border border-zinc-200/70 rounded-2xl p-4 text-xs text-zinc-600 space-y-1">
                        <span class="font-bold text-zinc-900 block">Komposisi & Bahan:</span>
                        <p>{{ $dish->composition }}</p>
                    </div>
                @endif
            </div>

            <!-- Right Col (Details & Customizations) -->
            <div class="md:col-span-6 lg:col-span-6 bg-white border border-zinc-200/80 rounded-3xl p-5 sm:p-6 shadow-sm space-y-5">
                
                @if($dish->isOutOfStock())
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3 text-red-800 text-xs sm:text-sm font-semibold">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <span class="font-extrabold block">Stok Habis Untuk Hari Ini</span>
                            <span class="text-xs text-red-700/90 font-normal">Mohon maaf, hidangan ini sudah habis terjual dan belum dapat dipesan saat ini.</span>
                        </div>
                    </div>
                @elseif($dish->track_stock)
                    <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl px-4 py-2.5 flex items-center justify-between text-emerald-900 text-xs font-semibold">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Ketersediaan Hari Ini:</span>
                        </span>
                        <span class="bg-white px-2.5 py-0.5 rounded-full border border-emerald-200 text-emerald-800 font-extrabold">
                            Sisa {{ $dish->stock }} porsi
                        </span>
                    </div>
                @endif

                <!-- Title & Meta -->
                <div>
                    <span class="text-[10px] sm:text-xs font-extrabold uppercase tracking-wider text-[#E84E1B]">
                        {{ $dish->category->name ?? 'MAKANAN' }}
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight mt-1">
                        {{ $dish->name }}
                    </h2>
                    <div class="text-xl font-black text-zinc-900 mt-1.5" id="display-price">
                        Rp{{ number_format($dish->price, 0, ',', '.') }}
                    </div>
                    <p class="text-xs sm:text-sm text-zinc-500 mt-2.5 leading-relaxed">
                        {{ $dish->description }}
                    </p>

                    <!-- Chips / Badges -->
                    <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-zinc-100">
                        @if($dish->spice_level)
                            <span class="text-[11px] font-semibold text-zinc-600 bg-zinc-100 px-3 py-1 rounded-lg flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48z"/></svg>
                                <span>{{ $dish->spice_level }}</span>
                            </span>
                        @endif

                        @if($dish->portion_desc)
                            <span class="text-[11px] font-semibold text-zinc-600 bg-zinc-100 px-3 py-1 rounded-lg flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                <span>{{ $dish->portion_desc }}</span>
                            </span>
                        @endif

                        <span class="text-[11px] font-semibold text-zinc-600 bg-zinc-100 px-3 py-1 rounded-lg flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>{{ $dish->prep_time_minutes }}–{{ $dish->prep_time_minutes + 5 }} menit</span>
                        </span>
                    </div>
                </div>

                <!-- Modifiers / Radio Options -->
                @if($dish->options && $dish->options->isNotEmpty())
                    @foreach($dish->options as $opt)
                        <div class="pt-2 border-t border-zinc-100">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-xs sm:text-sm font-bold text-zinc-900">{{ $opt->name }}</h4>
                                @if($opt->is_required)
                                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">WAJIB · PILIH 1</span>
                                @endif
                            </div>

                            <div class="space-y-2">
                                @foreach($opt->choices as $choice)
                                    <label class="flex items-center justify-between p-3 rounded-xl border text-xs sm:text-sm cursor-pointer transition-all border-zinc-200/80 bg-white hover:bg-zinc-50 has-[:checked]:border-orange-300 has-[:checked]:bg-orange-50/60 has-[:checked]:font-semibold has-[:checked]:text-zinc-900 shadow-2xs">
                                        <div class="flex items-center gap-2.5">
                                            <input 
                                                type="radio" 
                                                name="options[{{ $opt->id }}]" 
                                                value="{{ $choice->name }}" 
                                                data-extra="{{ $choice->extra_price ?? 0 }}"
                                                {{ $choice->is_default ? 'checked' : '' }} 
                                                class="w-4 h-4 accent-[#E84E1B] option-radio"
                                            >
                                            <span>{{ $choice->name }}</span>
                                        </div>
                                        <span class="text-zinc-400 font-medium">
                                            {{ ($choice->extra_price ?? 0) > 0 ? '+Rp' . number_format($choice->extra_price, 0, ',', '.') : 'Termasuk' }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif

                <!-- Kitchen Note -->
                <div class="pt-2 border-t border-zinc-100">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs sm:text-sm font-bold text-zinc-900">Catatan untuk dapur</h4>
                        <span class="text-[10px] font-bold text-zinc-400">Opsional</span>
                    </div>
                    <div class="relative">
                        <textarea 
                            name="note" 
                            id="kitchen-note" 
                            rows="2" 
                            maxlength="150" 
                            placeholder="Contoh: Ayamnya dipotong kecil, ya." 
                            class="w-full bg-zinc-50 border border-zinc-200/80 rounded-xl p-3 text-xs sm:text-sm text-zinc-800 placeholder:text-zinc-400 focus:outline-none focus:border-zinc-400 focus:bg-white transition-all resize-none"
                        ></textarea>
                        <span id="char-counter" class="absolute bottom-2.5 right-3 text-[10px] text-zinc-400 font-medium">
                            0/150
                        </span>
                    </div>
                </div>

                <!-- Action Bar -->
                @if($dish->isOutOfStock())
                    <div class="pt-4 border-t border-zinc-100">
                        <button 
                            type="button" 
                            disabled 
                            class="w-full bg-zinc-200 text-zinc-500 py-3.5 px-5 rounded-xl font-bold text-xs sm:text-sm text-center cursor-not-allowed shadow-none"
                        >
                            Stok Habis Untuk Hari Ini (Tidak Dapat Dipesan)
                        </button>
                    </div>
                @else
                    <div class="pt-4 border-t border-zinc-100 flex items-center gap-3">
                        <!-- Stepper -->
                        <div class="flex items-center justify-between bg-zinc-100 rounded-xl px-3 py-2.5 min-w-[100px] border border-zinc-200/50">
                            <button type="button" id="btn-minus" class="text-zinc-600 hover:text-zinc-950 font-bold px-1.5 text-sm">
                                -
                            </button>
                            <span id="qty-text" class="text-sm font-bold text-zinc-900">1</span>
                            <input type="hidden" name="quantity" id="qty-input" value="1">
                            <button type="button" id="btn-plus" class="text-zinc-600 hover:text-zinc-950 font-bold px-1.5 text-sm">
                                +
                            </button>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            class="flex-1 bg-[#E84E1B] hover:bg-[#d84414] active:scale-[0.99] text-white py-3.5 px-5 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-between shadow-md transition-all"
                        >
                            <span>Simpan ke keranjang</span>
                            <span id="btn-total-price">Rp{{ number_format($dish->price, 0, ',', '.') }}</span>
                        </button>
                    </div>
                @endif

            </div>

        </div>

    </form>
</div>

<script>
    const basePrice = {{ $dish->price }};
    const maxStock = {{ $dish->track_stock ? $dish->stock : 99 }};
    let quantity = 1;

    function calculateTotal() {
        let extra = 0;
        document.querySelectorAll('.option-radio:checked').forEach(el => {
            extra += parseInt(el.dataset.extra || 0);
        });
        const total = (basePrice + extra) * quantity;
        const formatted = 'Rp' + total.toLocaleString('id-ID');
        const btnTotal = document.getElementById('btn-total-price');
        if (btnTotal) btnTotal.innerText = formatted;
        const dispPrice = document.getElementById('display-price');
        if (dispPrice) dispPrice.innerText = 'Rp' + (basePrice + extra).toLocaleString('id-ID');
    }

    const btnPlus = document.getElementById('btn-plus');
    if (btnPlus) {
        btnPlus.addEventListener('click', () => {
            if (quantity < maxStock) {
                quantity++;
                document.getElementById('qty-text').innerText = quantity;
                document.getElementById('qty-input').value = quantity;
                calculateTotal();
            } else {
                alert(`Maksimal pesanan menu ini adalah ${maxStock} porsi sesuai stok hari ini.`);
            }
        });
    }

    const btnMinus = document.getElementById('btn-minus');
    if (btnMinus) {
        btnMinus.addEventListener('click', () => {
            if (quantity > 1) {
                quantity--;
                document.getElementById('qty-text').innerText = quantity;
                document.getElementById('qty-input').value = quantity;
                calculateTotal();
            }
        });
    }

    document.querySelectorAll('.option-radio').forEach(r => {
        r.addEventListener('change', calculateTotal);
    });

    const noteInput = document.getElementById('kitchen-note');
    const counter = document.getElementById('char-counter');
    if (noteInput && counter) {
        noteInput.addEventListener('input', (e) => {
            counter.innerText = `${e.target.value.length}/150`;
        });
    }

    calculateTotal();
</script>
@endsection
