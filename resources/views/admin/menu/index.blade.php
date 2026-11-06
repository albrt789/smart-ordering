@extends('admin.layout', ['title' => 'Manajemen Menu'])

@section('content')
<div class="space-y-6">

    <!-- Top Headline -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Manajemen Menu Makanan & Minuman</h2>
            <p class="text-xs text-zinc-400 mt-1">Kelola katalog produk, harga, foto, komposisi, dan ketersediaan menu.</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.menu.stock.reset_daily') }}" method="POST" onsubmit="return confirm('Reset stok semua menu ke kuota harian masing-masing?')">
                @csrf
                <button type="submit" class="px-3.5 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 border border-zinc-700 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm" title="Reset stok hari ini ke kuota harian">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    <span>Reset Stok Harian</span>
                </button>
            </form>
            <a href="{{ route('admin.menu.create') }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-rose-950/40 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Menu Baru</span>
            </a>
        </div>
    </div>

    <!-- Category Quick Strip -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Kategori Menu Restoran</h3>
            <span class="text-xs text-zinc-500">{{ $categories->count() }} Kategori Tersedia</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.menu.index', ['category' => 'all']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $categoryFilter === 'all' ? 'bg-rose-600 text-white shadow-xs' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700' }}">
                Semua ({{ \App\Models\Product::count() }})
            </a>
            @foreach($categories as $cat)
                <div class="flex items-center bg-zinc-800 border border-zinc-700/80 rounded-xl overflow-hidden text-xs">
                    <a href="{{ route('admin.menu.index', ['category' => $cat->id]) }}" class="px-3 py-1.5 font-bold transition-all {{ (string)$categoryFilter === (string)$cat->id ? 'bg-rose-600 text-white' : 'text-zinc-300 hover:text-white' }}">
                        {{ $cat->name }} ({{ $cat->products_count }})
                    </a>
                    @if($cat->products_count === 0)
                        <form action="{{ route('admin.category.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')" class="border-l border-zinc-700 px-2 py-1 text-zinc-500 hover:text-red-400">
                            @csrf
                            @method('DELETE')
                            <button type="submit">&times;</button>
                        </form>
                    @endif
                </div>
            @endforeach

            <!-- Add Category Inline Form -->
            <form action="{{ route('admin.category.store') }}" method="POST" class="flex items-center gap-1.5 ml-auto">
                @csrf
                <input type="text" name="name" required placeholder="+ Kategori Baru..." class="bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-1.5 text-xs text-white placeholder:text-zinc-500 focus:outline-none focus:border-rose-500">
                <button type="submit" class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 rounded-xl text-xs font-bold">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Product Table Card -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <!-- Search & Filter Bar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <h3 class="text-base font-extrabold text-white">Daftar Menu Makanan & Minuman</h3>
            
            <form action="{{ route('admin.menu.index') }}" method="GET" class="w-full sm:w-72">
                <input type="hidden" name="category" value="{{ $categoryFilter }}">
                <div class="relative">
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ $search }}" 
                        placeholder="Cari nama, bahan, deskripsi..." 
                        class="w-full bg-zinc-950 border border-zinc-700 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder:text-zinc-500 focus:outline-none focus:border-rose-500"
                    >
                    <svg class="w-4 h-4 text-zinc-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-zinc-400 uppercase tracking-wider bg-zinc-950/60 border-b border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 font-bold">Menu</th>
                        <th class="py-3 px-4 font-bold">Kategori</th>
                        <th class="py-3 px-4 font-bold">Harga</th>
                        <th class="py-3 px-4 font-bold">Stok Hari Ini</th>
                        <th class="py-3 px-4 font-bold">Waktu Masak</th>
                        <th class="py-3 px-4 font-bold">Status</th>
                        <th class="py-3 px-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
                    @forelse($products as $prod)
                        <tr class="hover:bg-zinc-800/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $prod->image }}" alt="{{ $prod->name }}" class="w-12 h-12 rounded-xl object-cover bg-zinc-800 shrink-0 border border-zinc-700">
                                    <div>
                                        <div class="font-extrabold text-white text-xs sm:text-sm flex items-center gap-1.5">
                                            <span>{{ $prod->name }}</span>
                                            @if($prod->is_signature)
                                                <span class="bg-rose-950 border border-rose-800 text-rose-400 text-[9px] font-black px-1.5 py-0.2 rounded-md">SIGNATURE</span>
                                            @endif
                                            @if($prod->badge)
                                                <span class="bg-zinc-800 text-zinc-300 text-[9px] font-bold px-1.5 py-0.2 rounded-md">{{ $prod->badge }}</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-zinc-400 line-clamp-1 max-w-sm mt-0.5">{{ $prod->description }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="bg-zinc-800 border border-zinc-700 text-zinc-300 px-2 py-0.5 rounded-md font-semibold text-[11px]">
                                    {{ $prod->category->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-black text-rose-400 text-xs sm:text-sm">
                                Rp{{ number_format($prod->price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if(!$prod->track_stock)
                                    <span class="text-[10px] text-zinc-500 font-medium italic">Tidak dibatasi</span>
                                @else
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1.5">
                                            @if($prod->stock <= 0)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-red-950 border border-red-800 text-red-300">
                                                    HABIS (0)
                                                </span>
                                            @elseif($prod->stock <= 5)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-950 border border-amber-800 text-amber-300">
                                                    Sisa {{ $prod->stock }} / {{ $prod->daily_stock }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-800 border border-zinc-700 text-emerald-400">
                                                    {{ $prod->stock }} / {{ $prod->daily_stock }} porsi
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Quick Inline Stock Form -->
                                        <form action="{{ route('admin.menu.stock.update', $prod->id) }}" method="POST" class="flex items-center gap-1">
                                            @csrf
                                            <input 
                                                type="number" 
                                                name="stock" 
                                                value="{{ $prod->stock }}" 
                                                min="0" 
                                                title="Ubah stok saat ini" 
                                                class="w-14 bg-zinc-950 border border-zinc-700 rounded-lg px-2 py-1 text-[11px] text-white focus:outline-none focus:border-rose-500"
                                            >
                                            <button 
                                                type="submit" 
                                                class="px-2 py-1 bg-zinc-800 hover:bg-rose-600 text-zinc-300 hover:text-white rounded-lg transition-colors text-[10px] font-bold"
                                                title="Simpan Stok Langsung"
                                            >
                                                Ubah
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-zinc-400 font-medium">
                                {{ $prod->prep_time_minutes }} Menit
                            </td>
                            <td class="py-3.5 px-4">
                                @if(!$prod->is_available || ($prod->track_stock && $prod->stock <= 0))
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-red-400 bg-red-950 border border-red-800 px-2 py-0.5 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Habis
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-400 bg-emerald-950 border border-emerald-800 px-2 py-0.5 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Tersedia
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.menu.edit', $prod->id) }}" class="p-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 rounded-xl transition-all font-bold" title="Edit Menu">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                    </a>

                                    <form action="{{ route('admin.menu.destroy', $prod->id) }}" method="POST" onsubmit="return confirm('Hapus menu {{ $prod->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-zinc-800 hover:bg-red-950/80 text-zinc-400 hover:text-red-300 rounded-xl transition-all" title="Hapus Menu">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-zinc-500">Tidak ada produk ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $products->links() }}
        </div>
    </div>

</div>
@endsection

