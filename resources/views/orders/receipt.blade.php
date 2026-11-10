<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Digital — {{ $order->order_number }} | {{ $setting->name ?? 'Fountain Hide' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .receipt-card { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-zinc-100 min-h-screen py-6 px-4 text-zinc-900 antialiased flex flex-col items-center justify-center">

    <div class="no-print mb-4 flex items-center gap-3">
        <a href="{{ route('orders.status', ['order_id' => $order->id]) }}" class="px-4 py-2 bg-white border border-zinc-200 rounded-xl text-xs font-bold text-zinc-700 hover:bg-zinc-50 shadow-xs flex items-center gap-1.5 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            <span>Kembali ke Pelacakan</span>
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-[#E84E1B] text-white rounded-xl text-xs font-bold hover:bg-[#d84414] shadow-sm flex items-center gap-1.5 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.67-2.03-1.27-2.829m0 0C6.91 9.54 8.78 8.5 12 8.5s5.09 1.04 6.55 2.5c-.6.8-1.03 1.753-1.27 2.829M12 18.5a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Z"/></svg>
            <span>Cetak / Simpan PDF</span>
        </button>
    </div>

    <!-- Thermal / Clean Receipt Card -->
    <div class="receipt-card w-full max-w-sm bg-white rounded-3xl border border-zinc-200/90 p-6 sm:p-7 shadow-lg space-y-4">
        <!-- Receipt Header -->
        <div class="text-center space-y-1 pb-3 border-b-2 border-dashed border-zinc-200">
            <img src="{{ asset('images/logo.png') }}" alt="Fountain Hide Logo" class="h-12 w-auto object-contain mx-auto mb-1">
            <h1 class="text-xl font-black tracking-tight text-zinc-950 uppercase">{{ $setting->name ?? 'FOUNTAIN HIDE' }}</h1>
            <p class="text-[11px] text-[#E84E1B] font-extrabold uppercase tracking-wider">{{ $setting->branch ?? 'Coffee & Roastery' }}</p>
            <p class="text-[10px] text-zinc-400 leading-tight max-w-[240px] mx-auto">{{ $setting->address ?? 'Jl. Senopati No. 88, Kebayoran Baru, Jakarta Selatan' }}</p>
            <p class="text-[10px] text-zinc-400">Telp: {{ $setting->phone ?? '0812-8800-4499' }}</p>
        </div>

        <!-- Meta Details -->
        <div class="text-xs space-y-1 text-zinc-600 pb-3 border-b-2 border-dashed border-zinc-200 mono">
            <div class="flex justify-between">
                <span>No. Pesanan:</span>
                <span class="font-bold text-zinc-900">{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal:</span>
                <span>{{ $order->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span class="font-bold text-zinc-900">{{ $order->customer_name }}</span>
            </div>
            <div class="flex justify-between">
                <span>Meja:</span>
                <span class="font-bold text-zinc-900">{{ $order->table_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Metode Bayar:</span>
                <span class="font-bold uppercase text-zinc-900">{{ $order->payment_method }}</span>
            </div>
            <div class="flex justify-between">
                <span>Status Bayar:</span>
                <span class="font-bold uppercase {{ $order->isPaid() ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $order->payment_status_label }}
                </span>
            </div>
        </div>

        <!-- Order Items -->
        <div class="space-y-2 pb-3 border-b-2 border-dashed border-zinc-200 text-xs">
            @foreach($order->items as $item)
                <div class="space-y-0.5">
                    <div class="flex justify-between font-semibold text-zinc-900">
                        <span>{{ $item->product_name }}</span>
                        <span class="mono">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[11px] text-zinc-500">
                        <span>{{ $item->quantity }} × Rp{{ number_format($item->price, 0, ',', '.') }}</span>
                        @if($item->options_selected)
                            <span class="text-zinc-400">{{ is_array($item->options_selected) ? implode(', ', $item->options_selected) : $item->options_selected }}</span>
                        @endif
                    </div>
                    @if($item->kitchen_note)
                        <div class="text-[10px] text-zinc-400 italic">"{{ $item->kitchen_note }}"</div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="space-y-1.5 text-xs text-zinc-600 pb-3 border-b-2 border-dashed border-zinc-200 mono">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Biaya Layanan ({{ $setting->service_fee_pct ?? 5 }}%):</span>
                <span>Rp{{ number_format($order->service_fee, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>PBJT Restoran ({{ $setting->pbjt_tax_pct ?? 10 }}%):</span>
                <span>Rp{{ number_format($order->pbjt_tax, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between font-black text-sm text-zinc-950 pt-1.5 border-t border-zinc-100">
                <span>TOTAL AKHIR:</span>
                <span class="text-[#E84E1B]">Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Footer Note & Barcode Simulation -->
        <div class="text-center space-y-2 pt-1">
            <div class="p-2 bg-zinc-50 rounded-xl border border-zinc-200/80 inline-block">
                <svg viewBox="0 0 100 18" class="w-40 h-8 mx-auto">
                    <rect x="0" y="0" width="2" height="18" fill="#141416"/>
                    <rect x="4" y="0" width="3" height="18" fill="#141416"/>
                    <rect x="9" y="0" width="1" height="18" fill="#141416"/>
                    <rect x="12" y="0" width="4" height="18" fill="#141416"/>
                    <rect x="18" y="0" width="2" height="18" fill="#141416"/>
                    <rect x="22" y="0" width="5" height="18" fill="#141416"/>
                    <rect x="29" y="0" width="2" height="18" fill="#141416"/>
                    <rect x="33" y="0" width="3" height="18" fill="#141416"/>
                    <rect x="38" y="0" width="1" height="18" fill="#141416"/>
                    <rect x="42" y="0" width="4" height="18" fill="#141416"/>
                    <rect x="48" y="0" width="2" height="18" fill="#141416"/>
                    <rect x="52" y="0" width="5" height="18" fill="#141416"/>
                    <rect x="59" y="0" width="2" height="18" fill="#141416"/>
                    <rect x="63" y="0" width="4" height="18" fill="#141416"/>
                    <rect x="69" y="0" width="1" height="18" fill="#141416"/>
                    <rect x="72" y="0" width="3" height="18" fill="#141416"/>
                    <rect x="77" y="0" width="2" height="18" fill="#141416"/>
                    <rect x="81" y="0" width="4" height="18" fill="#141416"/>
                    <rect x="87" y="0" width="2" height="18" fill="#141416"/>
                    <rect x="91" y="0" width="5" height="18" fill="#141416"/>
                    <rect x="98" y="0" width="2" height="18" fill="#141416"/>
                </svg>
            </div>
            <p class="text-[11px] font-bold text-zinc-700">Terima kasih atas kunjungan Anda!</p>
            <p class="text-[10px] text-zinc-400">Struk ini sah sebagai bukti pembayaran digital resmi.</p>
        </div>
    </div>

</body>
</html>
