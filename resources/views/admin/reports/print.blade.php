<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Cafe — {{ $setting->name ?? 'Fountain Hide' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-zinc-100 text-zinc-900 min-h-screen p-8 antialiased">

    <!-- Top Action Toolbar -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between">
        <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-white border border-zinc-200 rounded-xl text-xs font-bold text-zinc-700 hover:bg-zinc-50 shadow-xs flex items-center gap-1.5">
            &larr; Kembali ke Panel Admin
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold shadow-sm">
            Cetak Laporan / Simpan PDF
        </button>
    </div>

    <!-- Formal Statement Sheet -->
    <div class="max-w-4xl mx-auto bg-white border border-zinc-200 rounded-3xl p-8 sm:p-10 shadow-sm space-y-6">
        <!-- Letterhead Header -->
        <div class="border-b-2 border-zinc-900 pb-4 flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-zinc-950 uppercase">{{ $setting->name ?? 'FOUNTAIN HIDE' }}</h1>
                <p class="text-xs text-zinc-600 font-medium">{{ $setting->branch ?? 'Fountain Hide Coffee & Roastery' }}</p>
                <p class="text-[11px] text-zinc-500 mt-1 max-w-md">{{ $setting->address ?? 'Jl. Senopati No. 88, Kebayoran Baru, Jakarta Selatan' }} &bull; Telp: {{ $setting->phone ?? '0812-8800-4499' }}</p>
            </div>
            <div class="text-right">
                <span class="inline-block bg-zinc-900 text-white text-[10px] font-black uppercase px-2.5 py-1 rounded-md">Laporan Resmi</span>
                <div class="text-xs font-bold text-zinc-800 mt-2">Periode {{ ucfirst($period) }}</div>
                <div class="text-[11px] text-zinc-500">{{ $start->format('d/m/Y') }} s/d {{ $end->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Summary KPIs -->
        <div class="grid grid-cols-4 gap-4 py-2">
            <div class="p-3 bg-zinc-50 rounded-2xl border border-zinc-200">
                <div class="text-[10px] uppercase font-bold text-zinc-500">Total Transaksi</div>
                <div class="text-lg font-black text-zinc-900">{{ $totalTransactions }} Pesanan</div>
            </div>
            <div class="p-3 bg-zinc-50 rounded-2xl border border-zinc-200">
                <div class="text-[10px] uppercase font-bold text-zinc-500">QRIS Dinamis</div>
                <div class="text-lg font-black text-orange-600">Rp{{ number_format($qrisTotal, 0, ',', '.') }}</div>
            </div>
            <div class="p-3 bg-zinc-50 rounded-2xl border border-zinc-200">
                <div class="text-[10px] uppercase font-bold text-zinc-500">Tunai (Kasir)</div>
                <div class="text-lg font-black text-emerald-600">Rp{{ number_format($cashTotal, 0, ',', '.') }}</div>
            </div>
            <div class="p-3 bg-zinc-100 rounded-2xl border border-zinc-300">
                <div class="text-[10px] uppercase font-bold text-zinc-600">Total Pendapatan</div>
                <div class="text-lg font-black text-zinc-950">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Transaction Details Table -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-700">Rincian Transaksi Selesai</h3>
            <table class="w-full text-left text-xs border border-zinc-200">
                <thead class="bg-zinc-100 text-zinc-700 uppercase font-bold text-[10px] border-b border-zinc-200">
                    <tr>
                        <th class="py-2 px-3">No. Pesanan</th>
                        <th class="py-2 px-3">Waktu</th>
                        <th class="py-2 px-3">Meja / Customer</th>
                        <th class="py-2 px-3">Metode</th>
                        <th class="py-2 px-3 text-right">Subtotal</th>
                        <th class="py-2 px-3 text-right">Layanan</th>
                        <th class="py-2 px-3 text-right">PBJT 10%</th>
                        <th class="py-2 px-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @foreach($orders as $o)
                        <tr>
                            <td class="py-2 px-3 font-mono font-bold">{{ $o->order_number }}</td>
                            <td class="py-2 px-3 text-zinc-500">{{ $o->created_at->format('d/m H:i') }}</td>
                            <td class="py-2 px-3">{{ $o->table_number }} ({{ $o->customer_name }})</td>
                            <td class="py-2 px-3 uppercase font-semibold text-[11px]">{{ $o->payment_method }}</td>
                            <td class="py-2 px-3 text-right">Rp{{ number_format($o->subtotal, 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-right">Rp{{ number_format($o->service_fee, 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-right">Rp{{ number_format($o->tax_pbjt, 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-right font-bold">Rp{{ number_format($o->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-zinc-100 font-black border-t-2 border-zinc-300">
                    <tr>
                        <td colspan="4" class="py-2.5 px-3 uppercase text-right">TOTAL KESELURUHAN:</td>
                        <td class="py-2.5 px-3 text-right">Rp{{ number_format($subtotalAmount, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right">Rp{{ number_format($serviceFeeAmount, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right">Rp{{ number_format($pbjtTaxAmount, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right text-rose-700">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Signature Block -->
        <div class="pt-8 flex justify-between text-xs text-zinc-700">
            <div>
                <p>Dicetak pada: {{ now()->format('d F Y, H:i:s') }}</p>
                <p class="text-[10px] text-zinc-400 mt-0.5">Sistem Laporan Otomatis Fountain Hide POS</p>
            </div>
            <div class="text-center w-48">
                <p>Disetujui oleh,</p>
                <div class="h-16"></div>
                <p class="font-bold border-t border-zinc-800 pt-1">Administrator Restoran</p>
            </div>
        </div>
    </div>

</body>
</html>

