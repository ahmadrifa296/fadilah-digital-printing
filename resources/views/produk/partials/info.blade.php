<div class="space-y-4">
    <!-- Badges Section -->
    <div class="flex flex-wrap gap-1.5 items-center">
        @if($produk->discount_percent > 0 || $produk->discount_flat > 0)
            <span class="bg-red-50 text-red-600 border border-red-200 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg shadow-2xs">Promo Diskon</span>
        @endif
        @if($produk->created_at->gt(now()->subDays(14)))
            <span class="bg-blue-50 text-blue-600 border border-blue-200 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg shadow-2xs">Produk Baru</span>
        @endif
        @if($produk->reviews_count >= 1)
            <span class="bg-orange-50 text-orange-600 border border-orange-200 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg shadow-2xs">Best Seller</span>
        @endif
    </div>

    <!-- Product Title -->
    <h2 class="text-xl md:text-2xl font-black text-slate-800 leading-tight">
        {{ $produk->product_name }}
    </h2>

    <!-- Ratings & Stats Bar (Shopee Style) -->
    <div class="flex items-center gap-4 flex-wrap text-xs border-b border-slate-100 pb-4">
        <!-- Stars -->
        <div class="flex items-center gap-1 border-r border-slate-200 pr-4">
            <span class="font-bold text-orange-500 text-sm">
                {{ number_format($produk->average_rating ?: 5.0, 1) }}
            </span>
            <div class="flex text-orange-400 text-xs">
                @php $ratingVal = (int) round($produk->average_rating); @endphp
                @for($i=1; $i<=5; $i++)
                    <span>{{ $i <= ($ratingVal ?: 5) ? '★' : '☆' }}</span>
                @endfor
            </div>
        </div>

        <!-- Reviews Count -->
        <div class="border-r border-slate-200 pr-4">
            <span class="font-bold text-slate-800">{{ $produk->reviews_count }}</span>
            <span class="text-slate-400">Penilaian</span>
        </div>

        <!-- Sales Count Real-time -->
        <div>
            <span class="font-bold text-slate-800">{{ $produk->sales_count }}</span>
            <span class="text-slate-400">Terjual</span>
        </div>
    </div>

    <!-- Price Section Card (Shopee Style Banner) -->
    <div class="bg-slate-50/70 p-4 rounded-2xl border border-slate-200/40 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
        <div class="space-y-1">
            @if(in_array($produk->calculation_type, ['custom_size', 'quantity_custom_size']))
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-orange-500">
                        Rp {{ number_format($produk->getFinalPricePerM2Attribute(), 0, ',', '.') }}<span class="text-xs font-bold text-slate-400">/m²</span>
                    </span>
                    @if($produk->discount_percent > 0 || $produk->discount_flat > 0)
                        <span class="text-xs text-slate-400 line-through">
                            Rp {{ number_format($produk->price_per_m2, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
                <div class="mt-1 text-xs font-bold text-slate-600" x-show="lengthInput && widthInput">
                    Estimasi harga spanduk (<span x-text="(lengthInput * widthInput).toFixed(2)"></span> m²): 
                    <span class="text-orange-500 font-black" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(unitPrice)"></span>
                </div>
            @else
                <div class="flex items-baseline gap-2">
                    <!-- Real-time Price by Alpine.js -->
                    <span class="text-3xl font-black text-orange-500" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(unitPrice)"></span>
                    
                    <!-- Crossed Original Price -->
                    @if($produk->discount_percent > 0 || $produk->discount_flat > 0)
                        <span class="text-xs text-slate-400 line-through">
                            Rp {{ number_format($produk->price, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
            @endif
            
            <p class="text-[10px] text-slate-400 font-semibold">* Harga sudah termasuk PPN. Perubahan harga otomatis mengikuti ukuran/variasi yang dipilih.</p>
        </div>

        @if($produk->discount_percent > 0)
            <div class="bg-red-500 text-white font-extrabold text-[10px] uppercase tracking-wider py-1 px-2.5 rounded-lg text-center flex-shrink-0 animate-pulse">
                Potongan {{ $produk->discount_percent }}%
            </div>
        @endif
    </div>
</div>
