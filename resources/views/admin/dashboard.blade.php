@extends('admin.layout', ['title' => 'Dashboard Utama'])

@section('content')
<div class="space-y-6">

    <!-- Top Headline Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Ringkasan Operasional Restoran</h2>
            <p class="text-xs text-zinc-400 mt-1">Pantau performa penjualan, pesanan masuk, dan menu terlaris secara langsung.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.menu.create') }}" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-rose-950/40 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Menu Baru</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="px-3.5 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 rounded-xl text-xs font-bold transition-all border border-zinc-700 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                <span>Laporan Lengkap</span>
            </a>
        </div>
    </div>

    <!-- 4 Main KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Pendapatan -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-xs font-bold uppercase tracking-wider">Total Pendapatan</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-950/80 border border-emerald-800 text-emerald-400 flex items-center justify-center text-xs">Rp</span>
            </div>
            <div class="text-2xl font-black text-white">
                Rp{{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-zinc-400">Akumulasi seluruh transaksi lunas</p>
        </div>

        <!-- Pendapatan Hari Ini -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-xs font-bold uppercase tracking-wider">Omset Hari Ini</span>
                <span class="w-8 h-8 rounded-xl bg-rose-950/80 border border-rose-800 text-rose-400 flex items-center justify-center text-xs">📅</span>
            </div>
            <div class="text-2xl font-black text-rose-400">
                Rp{{ number_format($todayRevenue, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-zinc-400">{{ $todayOrders }} pesanan masuk hari ini</p>
        </div>

        <!-- Total Pesanan -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-xs font-bold uppercase tracking-wider">Total Pesanan</span>
                <span class="w-8 h-8 rounded-xl bg-blue-950/80 border border-blue-800 text-blue-400 flex items-center justify-center text-xs">📦</span>
            </div>
            <div class="text-2xl font-black text-white">
                {{ $totalOrders }}
            </div>
            <p class="text-[11px] text-zinc-400">Total pesanan sejak sistem aktif</p>
        </div>

        <!-- QRIS vs Cash -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-zinc-400">
                <span class="text-xs font-bold uppercase tracking-wider">QRIS Dinamis vs Tunai</span>
                <span class="w-8 h-8 rounded-xl bg-orange-950/80 border border-orange-800 text-orange-400 flex items-center justify-center text-xs">💳</span>
            </div>
            <div class="text-sm font-bold text-white flex justify-between pt-1">
                <span class="text-orange-400">QRIS: Rp{{ number_format($qrisRevenue, 0, ',', '.') }}</span>
            </div>
            <div class="text-sm font-bold text-white flex justify-between">
                <span class="text-emerald-400">Cash: Rp{{ number_format($cashRevenue, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Charts & Best Sellers Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- 7-Day Revenue Trend Chart (8 cols) -->
        <div class="lg:col-span-8 bg-zinc-900 border border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <div>
                    <h3 class="text-base font-extrabold text-white">Tren Penjualan 7 Hari Terakhir</h3>
                    <p class="text-xs text-zinc-400">Grafik omset harian restoran</p>
                </div>
                <span class="text-xs font-bold text-zinc-400 bg-zinc-800 px-3 py-1 rounded-full">Live Analytics</span>
            </div>

            <div class="h-64">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Top 5 Best Sellers (4 cols) -->
        <div class="lg:col-span-4 bg-zinc-900 border border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h3 class="text-base font-extrabold text-white">Menu Terlaris</h3>
                <span class="text-xs text-zinc-400 font-semibold">Paling Diminati</span>
            </div>

            <div class="space-y-3.5 divide-y divide-zinc-800/80">
                @forelse($bestSellers as $idx => $dish)
                    <div class="pt-3.5 first:pt-0 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-lg bg-zinc-800 text-zinc-400 font-black text-xs flex items-center justify-center shrink-0">
                                {{ $idx + 1 }}
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-white">{{ $dish->product_name }}</h4>
                                <span class="text-[11px] text-zinc-400">{{ $dish->total_sold }} porsi terjual</span>
                            </div>
                        </div>
                        <span class="text-xs font-black text-rose-400">
                            Rp{{ number_format($dish->total_revenue, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-zinc-500 py-6 text-center">Belum ada data menu terjual.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
            <div>
                <h3 class="text-base font-extrabold text-white">Aktivitas Transaksi Terbaru</h3>
                <p class="text-xs text-zinc-400">8 pesanan terakhir yang masuk ke sistem</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-rose-400 hover:text-rose-300">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-zinc-400 uppercase tracking-wider bg-zinc-950/60 border-b border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 font-bold">No. Pesanan</th>
                        <th class="py-3 px-4 font-bold">Waktu</th>
                        <th class="py-3 px-4 font-bold">Meja & Pelanggan</th>
                        <th class="py-3 px-4 font-bold">Metode Bayar</th>
                        <th class="py-3 px-4 font-bold">Status Bayar</th>
                        <th class="py-3 px-4 font-bold">Tahap Pesanan</th>
                        <th class="py-3 px-4 font-bold text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
                    @forelse($recentOrders as $ro)
                        <tr class="hover:bg-zinc-800/40 transition-colors">
                            <td class="py-3 px-4 font-bold text-white font-mono">{{ $ro->order_number }}</td>
                            <td class="py-3 px-4 text-zinc-400">{{ $ro->created_at->format('d/m H:i') }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-white">{{ $ro->table_number }}</span>
                                <span class="text-zinc-400">({{ $ro->customer_name }})</span>
                            </td>
                            <td class="py-3 px-4 uppercase font-bold text-zinc-300">{{ $ro->payment_method }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-block px-2 py-0.5 rounded-md font-bold text-[10px] uppercase {{ $ro->isPaid() ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-amber-950 text-amber-300 border border-amber-800' }}">
                                    {{ $ro->payment_status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-block px-2 py-0.5 rounded-md font-bold text-[10px] bg-zinc-800 text-zinc-300 border border-zinc-700">
                                    {{ $ro->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-black text-white">
                                Rp{{ number_format($ro->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-zinc-500">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart Script -->
<script>
    const labels = {!! json_encode(array_column($salesTrend, 'label')) !!};
    const revenues = {!! json_encode(array_column($salesTrend, 'revenue')) !!};

    const ctx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Omset Harian (Rp)',
                data: revenues,
                backgroundColor: 'rgba(225, 29, 72, 0.8)',
                hoverBackgroundColor: 'rgba(225, 29, 72, 1)',
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#A1A1AA', font: { family: 'Plus Jakarta Sans', size: 11 } }
                },
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { 
                        color: '#A1A1AA', 
                        font: { family: 'Plus Jakarta Sans', size: 10 },
                        callback: function(value) { return 'Rp' + value.toLocaleString('id-ID'); }
                    }
                }
            }
        }
    });
</script>
@endsection

