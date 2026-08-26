@props(['product'])

@php
    $finalPrice = $product->final_price;
    $discountPercent = $product->discount_percent;
    $discountFlat = $product->discount_flat;
    $hasDiscount = ($discountPercent > 0 || $discountFlat > 0);
@endphp

<div class="group bg-white rounded-2xl border border-slate-200/60 p-3 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between h-[370px] relative">
    {{-- Top Section: Image & Badge --}}
    <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-slate-50 border border-slate-100 shrink-0 cursor-pointer"
         onclick="window.location.href = '{{ route('produk.show', $product->id) }}'">
        
        @if($product->image)
            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->product_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="absolute inset-0 flex items-center justify-center text-slate-300">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif

        {{-- Category Badge --}}
        <div class="absolute top-2 left-2 bg-slate-900/80 backdrop-blur-xs px-2 py-0.5 rounded-md text-[8px] font-bold text-white uppercase tracking-wider">
            {{ $product->category->category_name ?? 'Umum' }}
        </div>
    </div>

    {{-- Content Section --}}
    <div class="flex-grow flex flex-col justify-between mt-2.5">
        <div class="space-y-1">
            {{-- Name --}}
            <h3 class="text-xs font-bold text-slate-800 line-clamp-2 hover:text-orange-500 transition-colors leading-tight cursor-pointer"
                title="{{ $product->product_name }}"
                onclick="window.location.href = '{{ route('produk.show', $product->id) }}'">
                {{ $product->product_name }}
            </h3>

            {{-- Rating --}}
            <div class="flex items-center gap-1 text-[10px] text-slate-500 font-bold">
                <span class="text-amber-400 text-xs">★</span>
                <span>{{ number_format($product->average_rating ?: 5.0, 1) }}</span>
                <span class="text-slate-300">|</span>
                <span>({{ $product->reviews_count ?: 0 }})</span>
            </div>

            {{-- Price tag --}}
            <div class="flex flex-wrap items-baseline gap-1.5 pt-0.5">
                <span class="text-[16px] sm:text-[18px] font-black text-orange-500">
                    Rp{{ number_format($finalPrice, 0, ',', '.') }}
                </span>
                @if($hasDiscount)
                    <span class="text-[9px] text-slate-400 line-through">
                        Rp{{ number_format($product->price, 0, ',', '.') }}
                    </span>
                @endif
            </div>

            {{-- Stock status --}}
            <div class="flex items-center gap-1 text-[10px]">
                @if($product->stock > 0)
                    <span class="text-emerald-500 font-bold">●</span>
                    <span class="text-slate-500">Stok {{ $product->stock }}</span>
                @else
                    <span class="text-rose-500 font-bold">●</span>
                    <span class="text-rose-600 font-bold">Stok Habis</span>
                @endif
            </div>
        </div>

        {{-- Button Section --}}
        <div class="pt-2">
            @auth
                @if($product->stock > 0)
                    <button type="button"
                            onclick="window.location.href = '{{ route('produk.show', $product->id) }}'"
                            class="h-9 w-full flex items-center justify-center bg-orange-500 hover:bg-orange-600 text-white rounded-full font-bold text-xs shadow-sm transition active:scale-95 cursor-pointer">
                        Pesan Sekarang
                    </button>
                @else
                    <div class="h-9 w-full bg-slate-100 text-slate-400 rounded-full font-bold text-xs flex items-center justify-center border border-slate-200">
                        Stok Habis
                    </div>
                @endif
            @else
                <a href="{{ route('login') }}" class="h-9 w-full flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-full font-bold text-xs text-center cursor-pointer">
                    Masuk untuk Memesan
                </a>
            @endauth
        </div>
    </div>
</div>
