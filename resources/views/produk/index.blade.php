<x-app-layout>

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
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-2xs animate-fade-in">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    <p class="text-xs font-bold">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Page Action Toolbar Card --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span>Katalog Produk Cetakan</span>
                    <span class="bg-slate-100 text-slate-600 font-mono text-[10px] px-2 py-0.5 rounded-md font-bold">{{ count($products) }} item</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola seluruh barang cetakan, harga, varian, dan spesifikasi produk percetakan.</p>
            </div>
            <div>
                <a href="{{ route('produk.create') }}" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-xs rounded-xl transition-all shadow-2xs inline-flex items-center gap-1.5 cursor-pointer active:scale-[0.98] whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Produk Baru</span>
                </a>
            </div>
        </div>

        {{-- Tabel Katalog Produk --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-600 font-extrabold text-[10px] uppercase tracking-wider border-b border-slate-200/80 whitespace-nowrap">
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Produk</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Kategori</th>
                            <th class="px-3.5 py-2.5 text-right whitespace-nowrap">Harga Base</th>
                            <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Stok</th>
                            <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Status</th>
                            <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @forelse($products as $item)
                            <tr class="hover:bg-orange-50/20 transition-colors">
                                <td class="px-3.5 py-2.5 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0 border border-slate-200">
                                            @if($item->image)
                                                <img src="{{ asset('storage/' . $item->image) }}"
                                                     alt="{{ $item->product_name }}"
                                                     class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 text-xs truncate max-w-[220px] whitespace-nowrap" title="{{ $item->product_name }}">{{ $item->product_name }}</p>
                                            @if($item->description)
                                                <p class="text-[10px] text-slate-400 font-normal truncate max-w-[220px] whitespace-nowrap" title="{{ $item->description }}">{{ $item->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3.5 py-2.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-extrabold bg-slate-100 text-slate-700 border border-slate-200 uppercase tracking-wider whitespace-nowrap">
                                        {{ $item->category->category_name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-2.5 text-right font-black text-slate-900 text-xs whitespace-nowrap">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                </td>
                                <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                    <span class="font-mono text-xs font-black {{ $item->stock == 0 ? 'text-rose-600' : ($item->stock < 10 ? 'text-amber-600' : 'text-slate-800') }} whitespace-nowrap">
                                        {{ number_format($item->stock, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                    @if($item->stock == 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wider whitespace-nowrap">Habis</span>
                                    @elseif($item->stock < 10)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider whitespace-nowrap">Menipis</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider whitespace-nowrap">Tersedia</span>
                                    @endif
                                </td>
                                <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5 whitespace-nowrap">
                                        <a href="{{ route('produk.edit', $item->id) }}"
                                           class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold border border-slate-200 text-[10px] uppercase tracking-wider rounded-lg px-2.5 py-1 transition-colors whitespace-nowrap inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Edit</span>
                                        </a>
                                        <form action="{{ route('produk.destroy', $item->id) }}" method="POST" class="inline"
                                              x-data
                                              @submit.prevent="if(confirm('Hapus produk {{ addslashes($item->product_name) }}? Data tidak dapat dikembalikan.')) $el.submit()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 font-extrabold border border-rose-200 text-[10px] uppercase tracking-wider rounded-lg px-2.5 py-1 transition-colors whitespace-nowrap inline-flex items-center gap-1 cursor-pointer">
                                                <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="py-12 text-center text-slate-400 font-medium">
                                        <p class="text-xs font-bold text-slate-600">Belum ada produk</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5 mb-3">Tambahkan produk cetak pertama Anda.</p>
                                        <a href="{{ route('produk.create') }}" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-xs rounded-xl transition-all shadow-2xs inline-flex items-center gap-1.5 cursor-pointer">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span>Tambah Produk Baru</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>