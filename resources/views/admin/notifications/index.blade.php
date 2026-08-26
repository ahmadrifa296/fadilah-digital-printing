<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title text-slate-800">⚙️ Manajemen Notifikasi</h1>
            <p class="page-subtitle text-slate-500">Kirim pemberitahuan, broadcast promo, atau pantau status notifikasi pelanggan.</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ targetType: 'all' }">
        {{-- Panel Kiri: Form Kirim Notifikasi --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-6 h-fit lg:col-span-1">
            <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Kirim Notifikasi Baru
            </h2>

            @if(session('success'))
                <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2 text-2xs font-semibold animate-fade-in">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('admin.notifications.send') }}" method="POST" class="space-y-4">
                @csrf

                {{-- Target Type --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Target Pengiriman</label>
                    <select name="target_type" x-model="targetType" class="form-select w-full rounded-xl py-2 text-xs border-slate-200">
                        <option value="all">Broadcast (Semua Pelanggan)</option>
                        <option value="single">Pelanggan Spesifik</option>
                    </select>
                </div>

                {{-- User Select (Shown only if Target Type is single) --}}
                <div class="space-y-1.5" x-show="targetType === 'single'" x-cloak>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Pilih Pelanggan</label>
                    <select name="user_id" class="form-select w-full rounded-xl py-2 text-xs border-slate-200">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-rose-500 text-[10px] font-bold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Title --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Judul Notifikasi</label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: Voucher Diskon Baru Untukmu!" class="form-input w-full rounded-xl py-2 text-xs border-slate-200" required>
                    @error('title')
                        <p class="text-rose-500 text-[10px] font-bold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Content --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Konten / Deskripsi</label>
                    <textarea name="content" placeholder="Tulis deskripsi notifikasi secara jelas dan padat..." rows="4" class="form-input w-full rounded-xl py-2 text-xs border-slate-200" required>{{ old('content') }}</textarea>
                    @error('content')
                        <p class="text-rose-500 text-[10px] font-bold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Category --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Kategori</label>
                    <select name="category" class="form-select w-full rounded-xl py-2 text-xs border-slate-200" required>
                        <option value="sistem" selected>⚙️ Sistem & Pengumuman</option>
                        <option value="promo">🔥 Promo / Diskon</option>
                        <option value="produk">🏷️ Produk Baru</option>
                        <option value="pesanan">📦 Pesanan</option>
                        <option value="pembayaran">💳 Pembayaran</option>
                        <option value="chat">💬 Chat</option>
                        <option value="akun">👤 Akun</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    {{-- Icon Selection --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Ikon</label>
                        <select name="icon" class="form-select w-full rounded-xl py-2 text-xs border-slate-200" required>
                            <option value="bell" selected>🔔 Lonceng</option>
                            <option value="shopping-bag">📦 Tas</option>
                            <option value="credit-card">💳 Kartu</option>
                            <option value="tag">🏷️ Tag</option>
                            <option value="percent">🔥 Persen</option>
                            <option value="chat">💬 Chat</option>
                            <option value="user">👤 User</option>
                            <option value="shield">🛡️ Shield</option>
                            <option value="info">ℹ️ Info</option>
                        </select>
                    </div>

                    {{-- Color Selection --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase">Warna</label>
                        <select name="color" class="form-select w-full rounded-xl py-2 text-xs border-slate-200" required>
                            <option value="orange" selected>Orange</option>
                            <option value="blue">Biru</option>
                            <option value="green">Hijau</option>
                            <option value="red">Merah</option>
                            <option value="yellow">Kuning</option>
                            <option value="purple">Ungu</option>
                            <option value="pink">Pink</option>
                            <option value="cyan">Cyan</option>
                            <option value="slate">Abu-abu</option>
                        </select>
                    </div>
                </div>

                {{-- Target URL --}}
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">Tautan Tujuan (URL)</label>
                    <input type="text" name="url" value="{{ old('url') }}" placeholder="Contoh: {{ url('/dashboard') }}" class="form-input w-full rounded-xl py-2 text-xs border-slate-200">
                    <p class="text-[9px] text-slate-400 font-medium">Opsional. Link halaman tujuan ketika notifikasi diklik pelanggan.</p>
                </div>

                <button type="submit" class="w-full btn-md bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold py-2.5 shadow-md transition-all text-xs flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Kirim Notifikasi
                </button>
            </form>
        </div>

        {{-- Panel Kanan: Tabel Riwayat Notifikasi Terkirim --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4 lg:col-span-2">
            <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Riwayat Notifikasi Terkirim
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 font-bold text-slate-600">
                            <th class="p-3">Pelanggan</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3">Notifikasi</th>
                            <th class="p-3">Waktu</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($notifications as $n)
                            @php
                                $userTarget = $n->notifiable;
                                $nTitle = $n->data['title'] ?? 'Notifikasi';
                                $nContent = $n->data['content'] ?? '';
                                $nCategory = $n->data['category'] ?? 'sistem';
                                $nRead = !is_null($n->read_at);
                            @endphp
                            <tr class="hover:bg-slate-50/55 transition-colors">
                                <td class="p-3">
                                    @if($userTarget)
                                        <div class="font-bold text-slate-800">{{ $userTarget->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium">{{ $userTarget->email }}</div>
                                    @else
                                        <span class="text-slate-400 italic">User Terhapus</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-600">
                                        {{ $nCategory }}
                                    </span>
                                </td>
                                <td class="p-3 max-w-xs">
                                    <div class="font-bold text-slate-800 truncate">{{ $nTitle }}</div>
                                    <div class="text-slate-500 text-[10px] truncate mt-0.5">{{ $nContent }}</div>
                                </td>
                                <td class="p-3 text-slate-400 whitespace-nowrap text-[10px] font-medium">
                                    {{ $n->created_at->diffForHumans() }}
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    @if($nRead)
                                        <span class="px-2 py-0.5 bg-green-50 text-green-600 border border-green-200 rounded-full text-[9px] font-bold">✓ Dibaca</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-yellow-50 text-yellow-600 border border-yellow-200 rounded-full text-[9px] font-bold">🔵 Dikirim</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <form action="{{ route('admin.notifications.destroy', $n->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')" class="m-0 inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400 italic">
                                    Belum ada notifikasi terkirim.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pt-2">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
