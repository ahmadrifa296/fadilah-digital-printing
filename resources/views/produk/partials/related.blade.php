@php
    // Query langsung dari Blade agar tidak mengubah controller
    $relatedProducts = \App\Models\Product::where('category_id', $produk->category_id)
        ->where('id', '!=', $produk->id)
        ->latest()
        ->take(4)
        ->get();

    $recommendedProducts = \App\Models\Product::where('id', '!=', $produk->id)
        ->inRandomOrder()
        ->take(4)
        ->get();
@endphp

<div class="space-y-8">
    <!-- Section 1: Produk Serupa -->
    <div class="bg-white rounded-2xl border border-slate-200/60 p-5 shadow-sm space-y-4">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-2.5">Produk Serupa</h3>
        
        @if($relatedProducts->isEmpty())
            <p class="text-[10px] text-slate-400 italic">Tidak ada produk serupa lainnya.</p>
        @else
            <div class="grid grid-cols-2 lg:grid-cols-1 gap-3">
                @foreach($relatedProducts as $p)
                    <x-product-card :product="$p" />
                @endforeach
            </div>
        @endif
    </div>

    <!-- Section 2: Rekomendasi Produk Lainnya -->
    <div class="bg-white rounded-2xl border border-slate-200/60 p-5 shadow-sm space-y-4">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-2.5">Rekomendasi Lainnya</h3>
        
        @if($recommendedProducts->isEmpty())
            <p class="text-[10px] text-slate-400 italic">Tidak ada rekomendasi produk saat ini.</p>
        @else
            <div class="grid grid-cols-2 lg:grid-cols-1 gap-3">
                @foreach($recommendedProducts as $p)
                    <x-product-card :product="$p" />
                @endforeach
            </div>
        @endif
    </div>
</div>
