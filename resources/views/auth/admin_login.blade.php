<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator — SUAP Restoran</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%2318181B'/><text x='50%' y='58%' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-weight='900' font-size='42' fill='%23F43F5E'>A.</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#0B0F17] text-white min-h-screen flex items-center justify-center p-4 antialiased selection:bg-rose-600 selection:text-white">

    <div class="w-full max-w-md space-y-6">
        <!-- Logo & Title -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-zinc-900 border border-zinc-800 text-rose-500 font-black text-2xl flex items-center justify-center mx-auto shadow-xl">
                A.
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white">Administrator Restoran</h1>
            <p class="text-xs text-zinc-400">Portal Manajemen Menu, Laporan Keuangan & Pengaturan</p>
        </div>

        <!-- Login Card -->
        <div class="bg-zinc-900/90 border border-zinc-800/90 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-5 backdrop-blur-md">
            @if(session('success'))
                <div class="p-3 bg-emerald-950/80 border border-emerald-800 text-xs text-emerald-300 rounded-xl font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 bg-red-950/80 border border-red-800 text-xs text-red-300 rounded-xl font-medium space-y-1">
                    @foreach($errors->all() as $err)
                        <div>• {{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-bold text-zinc-300">Email Administrator</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', 'admin@suap.id') }}" 
                        required 
                        placeholder="admin@suap.id"
                        class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-all"
                    >
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="text-xs font-bold text-zinc-300">Kata Sandi</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        value="admin123"
                        required 
                        placeholder="••••••••"
                        class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder:text-zinc-600 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-all"
                    >
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-zinc-400 hover:text-zinc-300">
                        <input type="checkbox" name="remember" class="accent-rose-500 rounded">
                        <span>Ingat sesi saya</span>
                    </label>
                    <span class="text-zinc-500 text-[11px]">Role: Admin HRD</span>
                </div>

                <button 
                    type="submit" 
                    class="w-full py-3 bg-rose-600 hover:bg-rose-500 active:scale-[0.99] text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-rose-950/50 transition-all flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                    <span>Masuk ke Panel Admin</span>
                </button>
            </form>

            <div class="pt-2 border-t border-zinc-800 text-center">
                <p class="text-[11px] text-zinc-500">
                    Akun demo admin: <code class="text-zinc-300 font-mono">admin@suap.id</code> / <code class="text-zinc-300 font-mono">admin123</code>
                </p>
            </div>
        </div>

        <p class="text-center text-[11px] text-zinc-600">
            SUAP Central Administration Panel &bull; Restoran Mitra
        </p>
    </div>

</body>
</html>

