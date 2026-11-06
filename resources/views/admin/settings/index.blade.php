@extends('admin.layout', ['title' => 'Profil & Pengaturan Restoran'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Headline -->
    <div>
        <h2 class="text-2xl font-black text-white tracking-tight">Profil & Pengaturan Restoran</h2>
        <p class="text-xs text-zinc-400 mt-1">Data profil ini otomatis disinkronkan ke halaman pelanggan dan basis data Asisten Suap AI.</p>
    </div>

    <!-- Settings Form -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-sm">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nama Restoran -->
                <div class="space-y-1.5">
                    <label for="name" class="text-xs font-bold text-zinc-300">Nama Restoran <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $setting->name) }}" 
                        required 
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500"
                    >
                </div>

                <!-- Cabang -->
                <div class="space-y-1.5">
                    <label for="branch" class="text-xs font-bold text-zinc-300">Nama Cabang / Lokasi <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="branch" 
                        name="branch" 
                        value="{{ old('branch', $setting->branch) }}" 
                        required 
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500"
                    >
                </div>

                <!-- Nomor Telepon / WA -->
                <div class="space-y-1.5">
                    <label for="phone" class="text-xs font-bold text-zinc-300">No. WhatsApp / Telepon Resto <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone', $setting->phone) }}" 
                        required 
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500"
                    >
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-bold text-zinc-300">Email Restoran <span class="text-rose-500">*</span></label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', $setting->email) }}" 
                        required 
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500"
                    >
                </div>

                <!-- Jam Operasional -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="open_hours" class="text-xs font-bold text-zinc-300">Jam Operasional <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        id="open_hours" 
                        name="open_hours" 
                        value="{{ old('open_hours', $setting->open_hours) }}" 
                        required 
                        placeholder="Setiap hari: 10.00 – 22.00 WIB"
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500"
                    >
                </div>

                <!-- Alamat Lengkap -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="address" class="text-xs font-bold text-zinc-300">Alamat Lengkap Restoran <span class="text-rose-500">*</span></label>
                    <textarea 
                        id="address" 
                        name="address" 
                        rows="2" 
                        required 
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500"
                    >{{ old('address', $setting->address) }}</textarea>
                </div>

                <!-- Profil / Deskripsi -->
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="description" class="text-xs font-bold text-zinc-300">Profil / Deskripsi Restoran (Dipakai Asisten AI) <span class="text-rose-500">*</span></label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="3" 
                        required 
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500"
                    >{{ old('description', $setting->description) }}</textarea>
                </div>

                <!-- Biaya Layanan % -->
                <div class="space-y-1.5">
                    <label for="service_fee_pct" class="text-xs font-bold text-zinc-300">Biaya Layanan / Service Charge (%) <span class="text-rose-500">*</span></label>
                    <input 
                        type="number" 
                        step="0.1" 
                        id="service_fee_pct" 
                        name="service_fee_pct" 
                        value="{{ old('service_fee_pct', $setting->service_fee_pct) }}" 
                        required 
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500"
                    >
                </div>

                <!-- Pajak PBJT % -->
                <div class="space-y-1.5">
                    <label for="pbjt_tax_pct" class="text-xs font-bold text-zinc-300">Pajak Restoran PBJT (%) <span class="text-rose-500">*</span></label>
                    <input 
                        type="number" 
                        step="0.1" 
                        id="pbjt_tax_pct" 
                        name="pbjt_tax_pct" 
                        value="{{ old('pbjt_tax_pct', $setting->pbjt_tax_pct) }}" 
                        required 
                        class="w-full bg-zinc-950 border border-zinc-700/80 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-rose-500"
                    >
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-800 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-rose-950/40">
                    Simpan Pengaturan Restoran
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

