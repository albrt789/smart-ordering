@extends('admin.layout', ['title' => 'Laporan Keuangan'])

@section('content')
<div class="space-y-6">

    <!-- Top Headline & Export Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Laporan Keuangan Restoran</h2>
            <p class="text-xs text-zinc-400 mt-1">
                Periode: <strong class="text-zinc-200">{{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}</strong> ({{ ucfirst($period) }})
            </p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Export CSV / Excel -->
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3.5 py-2.5 bg-emerald-700 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <span>Ekspor Excel (.CSV)</span>
            </a>

            <!-- Print / PDF -->
            <a href="{{ request()->fullUrlWithQuery(['export' => 'print']) }}" target="_blank" class="px-3.5 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 rounded-xl text-xs font-bold transition-all border border-zinc-700 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.67-2.03-1.27-2.829m0 0C6.91 9.54 8.78 8.5 12 8.5s5.09 1.04 6.55 2.5c-.6.8-1.03 1.753-1.27 2.829M12 18.5a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Z"/></svg>
                <span>Cetak / PDF</span>
            </a>
        </div>
    </div>

    <!-- Period Filter Switcher -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.index', ['period' => 'daily']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ $period === 'daily' ? 'bg-rose-600 text-white' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700' }}">
                Harian (Hari Ini)
            </a>
            <a href="{{ route('admin.reports.index', ['period' => 'weekly']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ $period === 'weekly' ? 'bg-rose-600 text-white' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700' }}">
                Mingguan (7 Hari)
            </a>
            <a href="{{ route('admin.reports.index', ['period' => 'monthly']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ $period === 'monthly' ? 'bg-rose-600 text-white' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700' }}">
                Bulanan (Bulan Ini)
            </a>
        </div>

        <!-- Custom Date Range -->
        <form action="{{ route('admin.reports.index') }}" method="GET" class="flex items-center gap-2 text-xs">
            <input type="hidden" name="period" value="custom">
            <input type="date" name="start_date" value="{{ request('start_date', $start->format('Y-m-d')) }}" class="bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-1.5 text-white">
            <span class="text-zinc-500">s/d</span>
            <input type="date" name="end_date" value="{{ request('end_date', $end->format('Y-m-d')) }}" class="bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-1.5 text-white">
            <button type="submit" class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl font-bold">Filter</button>
        </form>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Pendapatan Bersih -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Total Pendapatan (Gross)</div>
            <div class="text-2xl font-black text-rose-400">
                Rp{{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-zinc-400">{{ $totalTransactions }} transaksi lunas</p>
        </div>

        <!-- Subtotal Makanan Murni -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Subtotal Menu Murni</div>
            <div class="text-2xl font-black text-white">
                Rp{{ number_format($subtotalAmount, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-zinc-400">Omset hidangan tanpa pajak/layanan</p>
        </div>

        <!-- PBJT Restoran (10%) -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Pajak PBJT (10%)</div>
            <div class="text-2xl font-black text-emerald-400">
                Rp{{ number_format($pbjtTaxAmount, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-zinc-400">Pajak daerah restoran terakumulasi</p>
        </div>

        <!-- Biaya Layanan (5%) -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Biaya Layanan (5%)</div>
            <div class="text-2xl font-black text-blue-400">
                Rp{{ number_format($serviceFeeAmount, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-zinc-400">Service charge restoran</p>
        </div>
    </div>

    <!-- Payment Breakdown: QRIS vs Cash -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gradient-to-br from-orange-950/40 to-zinc-900 border border-orange-900/60 rounded-3xl p-6 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-orange-400">Total Transaksi QRIS Dinamis</span>
                <div class="text-3xl font-black text-white mt-1">Rp{{ number_format($qrisTotal, 0, ',', '.') }}</div>
                <p class="text-xs text-orange-300/80 mt-1">Settlement otomatis payment gateway</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-orange-950 border border-orange-800 text-orange-400 flex items-center justify-center text-xl font-black">
                Q
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-950/40 to-zinc-900 border border-emerald-900/60 rounded-3xl p-6 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Total Transaksi Tunai (Cash)</span>
                <div class="text-3xl font-black text-white mt-1">Rp{{ number_format($cashTotal, 0, ',', '.') }}</div>
                <p class="text-xs text-emerald-300/80 mt-1">Diterima & diverifikasi langsung oleh kasir</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-950 border border-emerald-800 text-emerald-400 flex items-center justify-center text-xl font-black">
                Rp
            </div>
        </div>
    </div>

    <!-- Product Sales Breakdown in Selected Period -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-base font-extrabold text-white">Rincian Penjualan per Menu (Periode Ini)</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-zinc-400 uppercase tracking-wider bg-zinc-950/60 border-b border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 font-bold">Nama Menu</th>
                        <th class="py-3 px-4 font-bold text-center">Porsi Terjual</th>
                        <th class="py-3 px-4 font-bold text-right">Total Pendapatan Menu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
                    @forelse($productSales as $ps)
                        <tr class="hover:bg-zinc-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-white">{{ $ps->product_name }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-rose-400">{{ $ps->qty }}</td>
                            <td class="py-3.5 px-4 text-right font-black text-white">Rp{{ number_format($ps->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-zinc-500">Belum ada penjualan menu pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

