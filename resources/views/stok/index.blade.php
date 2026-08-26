<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Manajemen Stok & Persediaan</h1>
    </x-slot>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs animate-fade-in mb-5">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                <p class="text-xs font-bold">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Daftar Produk & Stok Saat Ini --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-sm font-semibold text-surface-800 uppercase tracking-wide">Status Persediaan Produk</h3>
                </div>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th class="text-center w-24">Stok</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $prod)
                                <tr class="cursor-pointer hover:bg-surface-50/70" 
                                    x-data
                                    @click="$dispatch('select-product', { id: '{{ $prod->id }}', name: '{{ addslashes($prod->product_name) }}', stock: {{ $prod->stock }} })">
                                    <td>
                                        <div class="font-medium text-surface-900">{{ $prod->product_name }}</div>
                                        <p class="text-3xs text-surface-400 font-mono mt-0.5">ID: PROD-{{ $prod->id }}</p>
                                    </td>
                                    <td>
                                        <span class="badge-neutral">{{ $prod->category->category_name ?? '—' }}</span>
                                    </td>
                                    <td class="text-center font-bold">
                                        <span class="{{ $prod->stock == 0 ? 'text-danger-600' : ($prod->stock < 10 ? 'text-warning-600' : 'text-surface-700') }}">
                                            {{ $prod->stock }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($prod->stock == 0)
                                            <span class="status-canceled">Habis</span>
                                        @elseif($prod->stock < 10)
                                            <span class="status-pending">Kritis</span>
                                        @else
                                            <span class="status-done">Cukup</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-surface-400 italic">Belum ada produk terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="text-2xs text-surface-400 italic">* Klik pada baris tabel produk untuk memilih produk yang ingin diupdate stoknya secara instan di panel sebelah kanan.</p>
        </div>

        {{-- Panel Update Stok --}}
        <div class="lg:col-span-1" x-data="{ selectedId: '', selectedName: 'Pilih produk...', currentStock: 0, showForm: false }"
             @select-product.window="selectedId = $event.detail.id; selectedName = $event.detail.name; currentStock = $event.detail.stock; showForm = true">
            
            <div class="card sticky top-6">
                <div class="card-header bg-surface-50">
                    <h3 class="text-sm font-semibold text-surface-800 uppercase tracking-wide">Perbarui Stok</h3>
                </div>
                <div class="card-body">
                    
                    {{-- State Belum Pilih Produk --}}
                    <div x-show="!showForm" class="py-8 text-center space-y-3">
                        <div class="w-12 h-12 rounded-full bg-surface-100 flex items-center justify-center mx-auto text-surface-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                            </svg>
                        </div>
                        <p class="text-xs text-surface-500 font-medium">Belum ada produk dipilih</p>
                        <p class="text-2xs text-surface-400 max-w-xs mx-auto">Klik salah satu produk pada tabel di sebelah kiri untuk melakukan penyesuaian stok.</p>
                    </div>

                    {{-- Form Update Stok --}}
                    <div x-show="showForm" x-cloak class="space-y-4">
                        <div class="p-3 bg-primary-50 border border-primary-100 rounded-xl">
                            <span class="text-3xs font-semibold uppercase text-primary-400 tracking-wider">Produk Terpilih</span>
                            <h4 class="font-bold text-primary-900 truncate mt-0.5" x-text="selectedName"></h4>
                            <p class="text-xs text-primary-700/80 mt-1">Stok saat ini: <strong class="text-primary-900" x-text="currentStock"></strong> unit</p>
                        </div>

                        <form :action="'{{ url('/manajemen-stok') }}/' + selectedId" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label class="form-label">Aksi Penyesuaian</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="border border-surface-200 rounded-lg p-2.5 flex items-center gap-2 cursor-pointer hover:bg-surface-50 transition-colors">
                                        <input type="radio" name="aksi" value="tambah" checked class="text-primary-600 focus:ring-primary-500">
                                        <div class="text-xs">
                                            <span class="font-semibold text-surface-800 block">Tambah</span>
                                            <span class="text-3xs text-surface-400">Akumulasi stok</span>
                                        </div>
                                    </label>
                                    <label class="border border-surface-200 rounded-lg p-2.5 flex items-center gap-2 cursor-pointer hover:bg-surface-50 transition-colors">
                                        <input type="radio" name="aksi" value="atur" class="text-primary-600 focus:ring-primary-500">
                                        <div class="text-xs">
                                            <span class="font-semibold text-surface-800 block">Atur Ulang</span>
                                            <span class="text-3xs text-surface-400">Set nilai absolut</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="jumlah_stok" class="form-label">Jumlah Unit</label>
                                <input type="number" name="jumlah_stok" id="jumlah_stok" required min="0" placeholder="Masukkan jumlah unit..."
                                       class="form-input">
                            </div>

                            <div class="form-group">
                                <label for="keterangan" class="form-label">Keterangan Mutasi <span class="text-surface-400 font-normal">(opsional)</span></label>
                                <textarea name="keterangan" id="keterangan" rows="2" placeholder="Misal: Restock dari supplier, Koreksi stok, dll..."
                                          class="{{ $errors->has('keterangan') ? 'form-input-error' : 'form-textarea' }}">{{ old('keterangan') }}</textarea>
                                @error('keterangan')<p class="form-error"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                            </div>

                            <div class="pt-2 flex gap-2">
                                <button type="submit" class="flex-1 btn-md btn-primary justify-center">
                                    Simpan Perubahan
                                </button>
                                <button type="button" @click="showForm = false" class="btn-md btn-secondary">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>
