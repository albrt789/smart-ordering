@extends('admin.layout', ['title' => 'Daftar Seluruh Pesanan'])

@section('content')
<div class="space-y-6">

    <!-- Top Headline -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Manajemen & Arsip Pesanan Restoran</h2>
            <p class="text-xs text-zinc-400 mt-1">Pantau seluruh riwayat transaksi pesanan, nomor meja, dan status pembayaran.</p>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-4">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Search -->
            <div>
                <label class="text-[11px] font-bold text-zinc-400 block mb-1">Cari Pesanan</label>
                <input 
                    type="text" 
                    name="q" 
                    value="{{ $search }}" 
                    placeholder="No. pesanan, meja, nama..." 
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white placeholder:text-zinc-500 focus:outline-none focus:border-rose-500"
                >
            </div>

            <!-- Status Pesanan -->
            <div>
                <label class="text-[11px] font-bold text-zinc-400 block mb-1">Tahap Pengerjaan</label>
                <select name="status" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                    <option value="all">Semua Tahap</option>
                    <option value="waiting_payment" {{ $status === 'waiting_payment' ? 'selected' : '' }}>Menunggu Bayar</option>
                    <option value="received" {{ $status === 'received' ? 'selected' : '' }}>Pesanan Diterima</option>
                    <option value="cooking" {{ $status === 'cooking' ? 'selected' : '' }}>Sedang Dibuat</option>
                    <option value="preparing" {{ $status === 'preparing' ? 'selected' : '' }}>Sedang Disiapkan</option>
                    <option value="delivering" {{ $status === 'delivering' ? 'selected' : '' }}>Sedang Diantar</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <!-- Status Pembayaran -->
            <div>
                <label class="text-[11px] font-bold text-zinc-400 block mb-1">Status Pembayaran</label>
                <select name="payment_status" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                    <option value="all">Semua Status Bayar</option>
                    <option value="paid" {{ $paymentStatus === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                    <option value="pending" {{ $paymentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="expired" {{ $paymentStatus === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <!-- Dari Tanggal -->
            <div>
                <label class="text-[11px] font-bold text-zinc-400 block mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $dateFrom }}" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition-all">
                    Terapkan
                </button>
                <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-400 rounded-xl text-xs font-bold transition-all">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-zinc-400 uppercase tracking-wider bg-zinc-950/60 border-b border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 font-bold">No. Pesanan</th>
                        <th class="py-3 px-4 font-bold">Waktu</th>
                        <th class="py-3 px-4 font-bold">Meja & Pelanggan</th>
                        <th class="py-3 px-4 font-bold">Menu Dipesan</th>
                        <th class="py-3 px-4 font-bold">Metode & Status Bayar</th>
                        <th class="py-3 px-4 font-bold">Tahap Pengerjaan</th>
                        <th class="py-3 px-4 font-bold text-right">Total</th>
                        <th class="py-3 px-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
                    @forelse($orders as $o)
                        <tr class="hover:bg-zinc-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-white">{{ $o->order_number }}</td>
                            <td class="py-3.5 px-4 text-zinc-400">
                                {{ $o->created_at->format('d/m/Y') }}<br>
                                <span class="text-[10px] text-zinc-500">{{ $o->created_at->format('H:i') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">{{ $o->table_number }}</span>
                                <span class="text-zinc-400">{{ $o->customer_name }}</span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="space-y-0.5 line-clamp-2">
                                    @foreach($o->items as $it)
                                        <div class="text-[11px] text-zinc-300">
                                            {{ $it->quantity }}× {{ $it->product_name }}
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="uppercase font-bold text-zinc-200 block text-[11px]">{{ $o->payment_method }}</span>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $o->isPaid() ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-amber-950 text-amber-300 border border-amber-800' }}">
                                    {{ $o->payment_status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold bg-zinc-800 text-zinc-200 border border-zinc-700">
                                    {{ $o->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-rose-400 text-xs sm:text-sm">
                                Rp{{ number_format($o->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('orders.receipt', $o->order_number) }}" target="_blank" class="p-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 rounded-xl inline-block transition-all" title="Buka Struk Digital">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-zinc-500">Tidak ada pesanan ditemukan sesuai kriteria filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection

