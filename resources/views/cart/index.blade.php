<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="page-title text-slate-800">🛒 Keranjang Saya</h1>
                <p class="page-subtitle text-slate-500">Kelola item produk cetak pilihan Anda sebelum melakukan pembayaran</p>
            </div>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 btn-sm bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold px-4 py-2 border border-slate-200 shadow-2xs transition-colors">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                Lanjut Belanja
            </a>
        </div>
    </x-slot>

    {{-- Flash Message Alerts --}}
    <div class="space-y-4">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-2 animate-fade-in text-xs font-semibold">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-2 animate-fade-in text-xs font-semibold">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($cartItems->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-16 text-center max-w-md mx-auto space-y-4">
                <div class="w-20 h-20 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center mx-auto border border-orange-100 shadow-sm animate-pulse">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="font-bold text-slate-800 text-sm">Keranjang Masih Kosong</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Anda belum menambahkan produk apa pun ke keranjang belanja Anda. Jelajahi katalog kami sekarang!
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ url('/') }}" class="inline-flex btn-md bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold px-8 py-2.5 shadow-md shadow-orange-500/10 transition-all text-xs active:scale-95">
                        Jelajahi Produk
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start animate-fade-in">

                {{-- Left Area: Cart items loop --}}
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col divide-y divide-slate-100">
                        <div class="p-4 flex items-center justify-between bg-slate-50/50">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $cartCount }} Produk di Keranjang</span>
                            
                            <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh keranjang?')">
                                @csrf
                                <button type="submit" class="text-red-500 hover:text-red-600 hover:underline text-[10px] font-bold uppercase tracking-wider flex items-center gap-1 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Kosongkan
                                </button>
                            </form>
                        </div>

                        <div class="divide-y divide-slate-100 bg-white">
                            @foreach($cartItems as $item)
                                @php $isCustom = $item->product?->isCustom() ?? true; @endphp
                                <div class="p-5 flex flex-col sm:flex-row sm:items-start gap-4 hover:bg-slate-50/20 transition-all">
                                    
                                    {{-- Thumbnail --}}
                                    <a href="{{ route('produk.show', $item->product_id) }}" class="block flex-shrink-0 mx-auto sm:mx-0">
                                        <div class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-200 overflow-hidden flex items-center justify-center shadow-inner">
                                            @if($item->product && $item->product->image)
                                                <img src="{{ Storage::url($item->product->image) }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            @endif
                                        </div>
                                    </a>

                                    {{-- Description Info --}}
                                    <div class="flex-1 min-w-0 space-y-1 text-center sm:text-left">
                                        <a href="{{ route('produk.show', $item->product_id) }}" class="font-bold text-slate-800 text-xs hover:text-orange-500 transition-colors truncate block">
                                            {{ $item->product?->product_name ?? 'Produk Percetakan' }}
                                        </a>
                                        
                                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-1.5 text-[9px] font-bold text-slate-400 uppercase">
                                            <span>{{ $item->product?->category->category_name ?? 'Percetakan' }}</span>
                                            <span>&middot;</span>
                                            <span>Harga Satuan: Rp{{ number_format($item->price, 0, ',', '.') }}</span>
                                        </div>

                                        {{-- Custom Print specifications --}}
                                        @if($isCustom)
                                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/50 text-[9px] text-slate-500 text-left space-y-0.5 mt-2">
                                                @if(in_array($item->product?->calculation_type, ['custom_size', 'quantity_custom_size']) && $item->custom_length && $item->custom_width)
                                                    <div><strong class="font-bold uppercase text-[7px] text-slate-400">Ukuran Kustom:</strong> {{ number_format($item->custom_length, 2) }} m &times; {{ number_format($item->custom_width, 2) }} m ({{ number_format($item->custom_area ?: ($item->custom_length * $item->custom_width), 2) }} m²)</div>
                                                    @if($item->price_per_m2)
                                                        <div><strong class="font-bold uppercase text-[7px] text-slate-400">Harga per m²:</strong> Rp{{ number_format($item->price_per_m2, 0, ',', '.') }}</div>
                                                    @endif
                                                @endif
                                                @if($item->ukuran) <div><strong class="font-bold uppercase text-[7px] text-slate-400">Ukuran:</strong> {{ $item->ukuran }}</div> @endif
                                                @if($item->bahan) <div><strong class="font-bold uppercase text-[7px] text-slate-400">Bahan:</strong> {{ $item->bahan }}</div> @endif
                                                @if($item->finishing) <div><strong class="font-bold uppercase text-[7px] text-slate-400">Finishing:</strong> {{ $item->finishing }}</div> @endif
                                                @if($item->custom_text) <div><strong class="font-bold uppercase text-[7px] text-slate-400">Tulisan Custom:</strong> "{{ $item->custom_text }}"</div> @endif
                                                @if($item->notes) <div><strong class="font-bold uppercase text-[7px] text-slate-400">Catatan Cetak:</strong> <span class="italic text-slate-400">{{ $item->notes }}</span></div> @endif
                                            </div>
                                            
                                            @if($item->design_file)
                                                <div class="mt-2 flex items-center gap-1 bg-white border border-slate-200 rounded-lg p-1.5 w-max mx-auto sm:mx-0 shadow-3xs">
                                                    <svg class="h-3.5 w-3.5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <a href="{{ $item->design_file_url }}" target="_blank" class="font-black text-[8px] text-orange-500 hover:text-orange-600 hover:underline uppercase tracking-wider">Desain Terlampir</a>
                                                </div>
                                            @endif
                                        @endif

                                        {{-- Inline Qty Selector --}}
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST"
                                              class="flex items-center justify-center sm:justify-start gap-2 mt-3">
                                            @csrf
                                            @method('PUT')
                                            @php
                                                $minOrder = $item->product->minimum_order ?: 1;
                                                $maxOrder = in_array($item->product->calculation_type, ['custom_size', 'quantity_custom_size']) 
                                                    ? ($item->product->maximum_order ?: 99999) 
                                                    : min($item->product->stock ?: 99999, $item->product->maximum_order ?: 99999);
                                            @endphp
                                            <span class="text-[9px] text-slate-400 font-bold uppercase">Jumlah:</span>
                                            <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-white shadow-2xs h-8 flex-shrink-0">
                                                <button type="button" 
                                                        @click="let inp = $el.nextElementSibling; if(parseInt(inp.value) > {{ $minOrder }}) { inp.value--; $el.closest('form').submit(); }"
                                                        class="px-2.5 py-1 text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors font-bold text-xs cursor-pointer">-</button>
                                                <input type="number"
                                                       name="qty"
                                                       value="{{ $item->qty }}"
                                                       min="{{ $minOrder }}"
                                                       max="{{ $maxOrder }}"
                                                       readonly
                                                       class="w-10 border-0 py-1 text-center text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none select-none bg-white">
                                                <button type="button"
                                                        @click="let inp = $el.previousElementSibling; if(parseInt(inp.value) < {{ $maxOrder }}) { inp.value++; $el.closest('form').submit(); }"
                                                        class="px-2.5 py-1 text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors font-bold text-xs cursor-pointer">+</button>
                                            </div>
                                        </form>
                                    </div>

                                    {{-- Subtotal & Hapus --}}
                                    <div class="flex sm:flex-col items-center justify-between sm:items-end sm:justify-between h-full flex-shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                        <div class="text-xs font-black text-slate-800">
                                            Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                                        </div>
                                        
                                        <form action="{{ route('cart.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-600 hover:underline text-[9px] font-bold uppercase tracking-wider flex items-center gap-0.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Right Area: Payment Summary card --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4 sticky top-6">
                        <h3 class="font-extrabold text-slate-800 text-xs border-b border-slate-100 pb-3 uppercase tracking-wider">Ringkasan Belanja</h3>
                        
                        <div class="space-y-3 text-xs">
                            @foreach($cartItems as $item)
                                <div class="flex justify-between items-start text-slate-500">
                                    <span class="truncate mr-2 max-w-[150px] font-medium">{{ $item->product?->product_name ?? 'Produk' }}</span>
                                    <span class="flex-shrink-0 font-bold text-slate-700">{{ $item->qty }} &times; Rp{{ number_format($item->price, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-slate-100 pt-4 space-y-4">
                            <div class="flex justify-between font-black text-slate-800 text-sm">
                                <span>Total Belanja</span>
                                <span class="text-orange-500">Rp{{ number_format($cartTotal, 0, ',', '.') }}</span>
                            </div>
                            
                            <a href="{{ route('checkout.index') }}" class="w-full inline-flex justify-center items-center gap-1.5 btn-md bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-xl py-3 shadow-md hover:shadow-orange-500/10 transition-all active:scale-[0.98] cursor-pointer text-xs uppercase tracking-wider">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Lanjut ke Checkout
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        @endif
    </div>
</x-app-layout>
