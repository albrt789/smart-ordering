@extends('admin.layout', ['title' => $isEdit ? 'Edit Menu' : 'Tambah Menu Baru'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Headline -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">
                {{ $isEdit ? 'Perbarui Menu: ' . $product->name : 'Tambah Menu Baru' }}
            </h2>
            <p class="text-xs text-zinc-400 mt-1">Masukkan informasi hidangan, komposisi, harga, dan foto produk.</p>
        </div>
        <a href="{{ route('admin.menu.index') }}" class="px-3.5 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm">
        <form action="{{ $isEdit ? route('admin.menu.update', $product->id) : route('admin.menu.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nama Menu -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="name" class="text-xs font-bold text-zinc-300">Nama Menu / Hidangan <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $product->name) }}" 
                        required 
                        placeholder="Contoh: Ayam Sambal Matah"
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 transition-all"
                    >
                </div>

                <!-- Subtitle / Tagline -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="subtitle" class="text-xs font-bold text-zinc-300">Subtitle / Tagline Singkat</label>
                    <input 
                        type="text" 
                        id="subtitle" 
                        name="subtitle" 
                        value="{{ old('subtitle', $product->subtitle) }}" 
                        placeholder="Contoh: Nasi hangat, ayam panggang, sambal matah"
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 transition-all"
                    >
                </div>

                <!-- Kategori -->
                <div class="space-y-1.5">
                    <label for="category_id" class="text-xs font-bold text-zinc-300">Kategori <span class="text-rose-500">*</span></label>
                    <select 
                        id="category_id" 
                        name="category_id" 
                        required 
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500 transition-all"
                    >
                        <option value="">Pilih Kategori...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Harga Satuan -->
                <div class="space-y-1.5">
                    <label for="price" class="text-xs font-bold text-zinc-300">Harga Satuan (Rp) <span class="text-rose-500">*</span></label>
                    <input 
                        type="number" 
                        id="price" 
                        name="price" 
                        value="{{ old('price', $product->price) }}" 
                        required 
                        min="0"
                        placeholder="42000"
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 transition-all"
                    >
                </div>

                <!-- Waktu Masak / Estimasi -->
                <div class="space-y-1.5">
                    <label for="prep_time_minutes" class="text-xs font-bold text-zinc-300">Estimasi Masak (Menit) <span class="text-rose-500">*</span></label>
                    <input 
                        type="number" 
                        id="prep_time_minutes" 
                        name="prep_time_minutes" 
                        value="{{ old('prep_time_minutes', $product->prep_time_minutes ?? 15) }}" 
                        required 
                        min="1"
                        placeholder="15"
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 transition-all"
                    >
                </div>

                <!-- Porsi Deskripsi -->
                <div class="space-y-1.5">
                    <label for="portion_desc" class="text-xs font-bold text-zinc-300">Porsi</label>
                    <input 
                        type="text" 
                        id="portion_desc" 
                        name="portion_desc" 
                        value="{{ old('portion_desc', $product->portion_desc ?? '1 porsi') }}" 
                        placeholder="1 porsi / 1 gelas"
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 transition-all"
                    >
                </div>

                <!-- Level Pedas & Badge -->
                <div class="space-y-1.5">
                    <label for="spice_level" class="text-xs font-bold text-zinc-300">Tingkat Kepedasan</label>
                    <input 
                        type="text" 
                        id="spice_level" 
                        name="spice_level" 
                        value="{{ old('spice_level', $product->spice_level ?? 'Tidak pedas') }}" 
                        placeholder="Pedas sedang / Pedas mantap / Tidak pedas"
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 transition-all"
                    >
                </div>

                <div class="space-y-1.5">
                    <label for="badge" class="text-xs font-bold text-zinc-300">Label Badge Promosi</label>
                    <input 
                        type="text" 
                        id="badge" 
                        name="badge" 
                        value="{{ old('badge', $product->badge) }}" 
                        placeholder="PALING LARIS / SEGAR / FAVORIT"
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 transition-all"
                    >
                </div>

                <!-- Card Pengelolaan Stok Harian -->
                <div class="sm:col-span-2 bg-zinc-950/80 border border-zinc-800 rounded-2xl p-4 sm:p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-rose-950 border border-rose-800 text-rose-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                            </span>
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-white">Pengelolaan Stok Harian</h4>
                                <p class="text-[11px] text-zinc-400">Atur ketersediaan porsi per hari. Jika stok habis, customer otomatis tidak dapat memesan menu ini.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-zinc-800/80 grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <div class="sm:col-span-1">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-zinc-300">
                                <input type="checkbox" name="track_stock" value="1" {{ old('track_stock', $product->track_stock ?? true) ? 'checked' : '' }} class="accent-rose-600 w-4 h-4 rounded">
                                <span>Aktifkan Pelacakan Stok</span>
                            </label>
                            <p class="text-[10px] text-zinc-500 mt-1">Centang jika menu ini memiliki kuota terbatas per hari.</p>
                        </div>

                        <div class="space-y-1.5">
                            <label for="daily_stock" class="text-xs font-bold text-zinc-300">Alokasi Stok Harian (Porsi)</label>
                            <input 
                                type="number" 
                                id="daily_stock" 
                                name="daily_stock" 
                                value="{{ old('daily_stock', $product->daily_stock ?? 50) }}" 
                                min="0"
                                placeholder="50"
                                class="w-full bg-zinc-900 border border-zinc-700/80 rounded-xl px-3.5 py-2 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500 transition-all"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label for="stock" class="text-xs font-bold text-zinc-300">Stok Saat Ini (Sisa Hari Ini)</label>
                            <input 
                                type="number" 
                                id="stock" 
                                name="stock" 
                                value="{{ old('stock', $product->stock ?? 50) }}" 
                                min="0"
                                placeholder="50"
                                class="w-full bg-zinc-900 border border-zinc-700/80 rounded-xl px-3.5 py-2 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500 transition-all"
                            >
                        </div>
                    </div>
                </div>

                <!-- Foto Menu -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="image" class="text-xs font-bold text-zinc-300">Foto Hidangan</label>
                    <div class="flex items-center gap-4">
                        @if($product->image)
                            <img src="{{ $product->image }}" alt="Preview" class="w-16 h-16 rounded-2xl object-cover bg-zinc-800 border border-zinc-700">
                        @endif
                        <input 
                            type="file" 
                            id="image" 
                            name="image" 
                            accept="image/*"
                            class="text-xs text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-zinc-200 hover:file:bg-zinc-700"
                        >
                    </div>
                </div>

                <!-- Deskripsi Lengkap -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="description" class="text-xs font-bold text-zinc-300">Deskripsi Lengkap <span class="text-rose-500">*</span></label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="3" 
                        required 
                        placeholder="Jelaskan kelezatan hidangan ini..."
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 transition-all"
                    >{{ old('description', $product->description) }}</textarea>
                </div>

                <!-- Komposisi & Bahan Baku -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="composition" class="text-xs font-bold text-zinc-300">Komposisi & Bahan Baku (Dipakai Asisten AI)</label>
                    <textarea 
                        id="composition" 
                        name="composition" 
                        rows="2" 
                        placeholder="Ayam fillet panggang, nasi putih, cabai rawit merah, bawang merah, serai, daun jeruk, minyak kelapa asli."
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 transition-all"
                    >{{ old('composition', $product->composition) }}</textarea>
                </div>

                <!-- Info Alergen -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="allergens" class="text-xs font-bold text-zinc-300">Informasi Alergen (Dipakai Asisten AI)</label>
                    <input 
                        type="text" 
                        id="allergens" 
                        name="allergens" 
                        value="{{ old('allergens', $product->allergens) }}" 
                        placeholder="Mengandung terasi udang & kedelai. Bebas susu dan kacang."
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 transition-all"
                    >
                </div>

                <!-- Checkboxes: Signature & Available -->
                <div class="sm:col-span-2 flex flex-wrap gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-zinc-300">
                        <input type="checkbox" name="is_signature" value="1" {{ old('is_signature', $product->is_signature) ? 'checked' : '' }} class="accent-rose-600 w-4 h-4 rounded">
                        <span>Tandai sebagai Menu Signature (Paling Atas)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-zinc-300">
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', $product->is_available ?? true) ? 'checked' : '' }} class="accent-rose-600 w-4 h-4 rounded">
                        <span>Menu Tersedia (Dapat Dipesan Pelanggan)</span>
                    </label>
                </div>

            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-zinc-800 flex justify-end gap-3">
                <a href="{{ route('admin.menu.index') }}" class="px-5 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 rounded-xl text-xs font-bold transition-all">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-rose-950/40">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Buat Menu Baru' }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

