<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Kategori Produk Cetak</h1>
    </x-slot>

    <div class="space-y-5">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-2xs animate-fade-in">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-xs font-bold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Page Action Toolbar Card --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span>Pengelompokan Kategori Cetakan</span>
                    <span class="bg-slate-100 text-slate-600 font-mono text-[10px] px-2 py-0.5 rounded-md font-bold">{{ count($categories) }} kategori</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola kategori untuk pengelompokan produk percetakan.</p>
            </div>
            <div>
                <a href="{{ route('kategori.create') }}" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-xs rounded-xl transition-all shadow-2xs inline-flex items-center gap-1.5 cursor-pointer active:scale-[0.98] whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Kategori</span>
                </a>
            </div>
        </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-16 text-center">No</th>
                        <th>Nama Kategori</th>
                        <th class="text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $key => $item)
                        <tr>
                            <td class="text-center font-medium text-surface-500">{{ $key + 1 }}</td>
                            <td class="font-semibold text-surface-900">{{ $item->category_name }}</td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('kategori.edit', $item->id) }}" class="btn-xs btn-secondary">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </a>
                                    
                                    <form action="{{ route('kategori.destroy', $item->id) }}" method="POST" class="inline"
                                          x-data
                                          @submit.prevent="if(confirm('Hapus kategori {{ addslashes($item->category_name) }}? Produk dengan kategori ini mungkin akan terpengaruh.')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-xs btn-danger">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="empty-state py-12">
                                    <div class="empty-state-icon">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-surface-700">Belum ada kategori</p>
                                    <p class="text-xs text-surface-400 mt-1 mb-4">Tambahkan kategori produk pertama Anda</p>
                                    <a href="{{ route('kategori.create') }}" class="btn-sm btn-primary">+ Tambah Kategori</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>