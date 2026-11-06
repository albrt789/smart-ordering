@extends('admin.layout', ['title' => 'Manajemen Akun Kasir'])

@section('content')
<div class="space-y-6">

    <!-- Top Headline -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Manajemen Akun Karyawan Kasir</h2>
            <p class="text-xs text-zinc-400 mt-1">Kelola hak akses kasir untuk mengonfirmasi pembayaran tunai dan mengelola pesanan.</p>
        </div>
        <div>
            <button onclick="document.getElementById('modal-add-user').classList.remove('hidden')" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-rose-950/40 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Akun Kasir</span>
            </button>
        </div>
    </div>

    <!-- Cashiers Table Card -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-zinc-400 uppercase tracking-wider bg-zinc-950/60 border-b border-zinc-800">
                    <tr>
                        <th class="py-3 px-4 font-bold">Nama Kasir</th>
                        <th class="py-3 px-4 font-bold">Email Login</th>
                        <th class="py-3 px-4 font-bold">No. HP</th>
                        <th class="py-3 px-4 font-bold">Status Akun</th>
                        <th class="py-3 px-4 font-bold">Terdaftar Sejak</th>
                        <th class="py-3 px-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
                    @forelse($cashiers as $user)
                        <tr class="hover:bg-zinc-800/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-zinc-800 border border-zinc-700 text-rose-400 flex items-center justify-center font-bold text-[11px]">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-zinc-300">{{ $user->email }}</td>
                            <td class="py-3.5 px-4 text-zinc-400">{{ $user->phone ?? '-' }}</td>
                            <td class="py-3.5 px-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-400 bg-emerald-950 border border-emerald-800 px-2 py-0.5 rounded-md">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-red-400 bg-red-950 border border-red-800 px-2 py-0.5 rounded-md">
                                        Dinonaktifkan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-zinc-400">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openEditModal({{ json_encode($user) }})" class="p-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 rounded-xl font-bold transition-all" title="Edit Akun Kasir">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                    </button>

                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus akun kasir {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-zinc-800 hover:bg-red-950/80 text-zinc-400 hover:text-red-300 rounded-xl transition-all" title="Hapus Akun">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-zinc-500">Belum ada akun kasir terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Add User -->
<div id="modal-add-user" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
            <h3 class="text-base font-black text-white">Tambah Akun Kasir Baru</h3>
            <button onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="text-zinc-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Nama Lengkap</label>
                <input type="text" name="name" required placeholder="Contoh: Rian Kasir" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Email Login</label>
                <input type="email" name="email" required placeholder="rian@suap.id" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Nomor Telepon</label>
                <input type="text" name="phone" placeholder="0812xxxxxxxx" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Kata Sandi Awal</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div class="pt-3 border-t border-zinc-800 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="px-4 py-2 bg-zinc-800 text-zinc-300 rounded-xl text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User -->
<div id="modal-edit-user" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
            <h3 class="text-base font-black text-white">Perbarui Akun Kasir</h3>
            <button onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="text-zinc-400 hover:text-white">&times;</button>
        </div>

        <form id="edit-user-form" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Nama Lengkap</label>
                <input type="text" id="edit-name" name="name" required class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Email Login</label>
                <input type="email" id="edit-email" name="email" required class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Nomor Telepon</label>
                <input type="text" id="edit-phone" name="phone" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Ganti Kata Sandi (Opsional)</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div>
                <label class="text-xs font-bold text-zinc-300 block mb-1">Status Akun</label>
                <select id="edit-is-active" name="is_active" class="w-full bg-zinc-950 border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                    <option value="1">Aktif (Dapat Login)</option>
                    <option value="0">Nonaktifkan Akun</option>
                </select>
            </div>

            <div class="pt-3 border-t border-zinc-800 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="px-4 py-2 bg-zinc-800 text-zinc-300 rounded-xl text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(user) {
        document.getElementById('edit-user-form').action = `/admin/users/${user.id}`;
        document.getElementById('edit-name').value = user.name;
        document.getElementById('edit-email').value = user.email;
        document.getElementById('edit-phone').value = user.phone || '';
        document.getElementById('edit-is-active').value = user.is_active ? '1' : '0';
        document.getElementById('modal-edit-user').classList.remove('hidden');
    }
</script>
@endsection

