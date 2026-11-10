<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Fountain Hide — Specialty Coffee & Roastery | Smart Dine-In QR' }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Play CDN for rapid zero-build setup) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            orange: '#E84E1B',
                            dark: '#141416'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Canvas Confetti for Celebration -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.4/dist/confetti.browser.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#F5F5F7] text-zinc-900 min-h-screen antialiased selection:bg-[#E84E1B] selection:text-white pb-20 md:pb-8">

    <!-- Top Header (Responsive for Mobile, Tablet, Laptop, Desktop) -->
    <header class="bg-white/95 backdrop-blur-md sticky top-0 z-40 border-b border-zinc-200/80 px-4 sm:px-6 lg:px-8 py-3.5 transition-all">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Left: Brand & Location -->
            <div class="flex items-center gap-6">
                <div>
                    <a href="{{ route('menu') }}" class="flex items-center gap-2.5 group">
                        <img src="{{ asset('images/logo.png') }}" alt="Fountain Hide Logo" class="h-9 w-auto object-contain group-hover:scale-105 transition-transform">
                        <div>
                            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-zinc-950 uppercase leading-none">FOUNTAIN HIDE</h1>
                            <span class="text-[9px] tracking-widest text-[#E84E1B] uppercase font-extrabold block mt-0.5">COFFEE & ROASTERY</span>
                        </div>
                    </a>
                    <div class="flex items-center gap-2 mt-1 text-xs text-zinc-500">
                        <span>{{ $setting->branch ?? 'Fountain Hide Coffee & Roastery' }}</span>
                        <span>·</span>
                        <span class="hidden sm:inline">Makan di tempat</span>
                        <span class="sm:hidden">Dine-in</span>
                        <span class="hidden md:inline">· Pesan dari mejamu</span>
                    </div>
                </div>

                <!-- Desktop / Tablet Nav Menu Links -->
                <nav class="hidden md:flex items-center gap-1 ml-4 pl-4 border-l border-zinc-200">
                    <a href="{{ route('menu') }}" class="px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('menu*') ? 'bg-zinc-950 text-white shadow-sm' : 'text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8zM6 1v3M10 1v3M14 1v3"/></svg>
                        <span>Menu & Coffee</span>
                    </a>

                    <a href="{{ route('ai.chat') }}" class="px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('ai*') ? 'bg-zinc-950 text-white shadow-sm' : 'text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100' }}">
                        <svg class="w-4 h-4 text-[#E84E1B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                        <span>Asisten Fountain AI</span>
                    </a>

                    <a href="{{ route('orders.status') }}" class="px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('orders*') ? 'bg-zinc-950 text-white shadow-sm' : 'text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                        <span>Pesanan</span>
                    </a>
                </nav>
            </div>

            <!-- Right: Table Badge, Cart & Status -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Table Selector / Indicator -->
                <div class="flex items-center gap-1.5 bg-zinc-50 border border-zinc-200/90 rounded-full px-3 py-1.5 text-xs font-bold text-zinc-800 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3m0-7v3m4 0h3"/></svg>
                    <span>{{ $table ?? session('current_table', 'Meja 08') }}</span>
                </div>

                <!-- Desktop Cart Button -->
                @php
                    $sessionCart = session('cart', []);
                    $headerCartCount = array_sum(array_column($sessionCart, 'quantity'));
                @endphp
                <a href="{{ route('cart') }}" class="hidden sm:flex items-center gap-2 bg-[#E84E1B] hover:bg-[#d84414] text-white px-3.5 py-1.5 rounded-full text-xs font-bold shadow-sm transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <span>Keranjang</span>
                    @if($headerCartCount > 0)
                        <span class="bg-white text-[#E84E1B] text-[10px] font-black px-1.5 py-0.2 rounded-full">{{ $headerCartCount }}</span>
                    @endif
                </a>

                <!-- Online Status Pulse -->
                <div class="hidden lg:flex items-center gap-1.5 text-[11px] font-medium text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Terhubung</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container (Expanded for Desktop / Tablet) -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3">
        @yield('content')
    </main>

    <!-- Bottom Navigation Bar (Visible ONLY on Mobile devices) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-zinc-200/80 pb-safe shadow-lg">
        <div class="max-w-md mx-auto grid grid-cols-3 py-2">
            <!-- Tab Menu -->
            <a href="{{ route('menu') }}" class="flex flex-col items-center justify-center py-1 transition-all {{ request()->routeIs('menu*') ? 'text-[#E84E1B] font-bold' : 'text-zinc-400 hover:text-zinc-600 font-medium' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('menu*') ? 'scale-110' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8zM6 1v3M10 1v3M14 1v3"/></svg>
                <span class="text-[11px] mt-1">Menu</span>
            </a>

            <!-- Tab Asisten (Fountain AI) -->
            <a href="{{ route('ai.chat') }}" class="flex flex-col items-center justify-center py-1 transition-all {{ request()->routeIs('ai*') ? 'text-[#E84E1B] font-bold' : 'text-zinc-400 hover:text-zinc-600 font-medium' }}">
                <div class="relative">
                    <svg class="w-5 h-5 {{ request()->routeIs('ai*') ? 'scale-110' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                </div>
                <span class="text-[11px] mt-1">Asisten</span>
            </a>

            <!-- Tab Pesanan -->
            <a href="{{ route('orders.status') }}" class="flex flex-col items-center justify-center py-1 transition-all {{ request()->routeIs('orders*') ? 'text-[#E84E1B] font-bold' : 'text-zinc-400 hover:text-zinc-600 font-medium' }}">
                <div class="relative">
                    <svg class="w-5 h-5 {{ request()->routeIs('orders*') ? 'scale-110' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                    <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-[#E84E1B]"></span>
                </div>
                <span class="text-[11px] mt-1">Pesanan</span>
            </a>
        </div>
    </nav>

</body>
</html>
