<div class="space-y-4">
    <!-- Big Main Image (Shopee Style with Aspect Square & Zoom on Hover) -->
    <div class="relative bg-slate-50 rounded-2xl border border-slate-200/60 overflow-hidden shadow-sm aspect-square flex items-center justify-center group cursor-zoom-in">
        <template x-if="activeImage">
            <img :src="activeImage" 
                 class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" 
                 alt="{{ $produk->product_name }}">
        </template>
        <template x-if="!activeImage">
            <div class="text-slate-300 flex flex-col items-center justify-center p-6">
                <svg class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-xs font-bold text-slate-400 mt-3 uppercase tracking-wide">Tidak Ada Foto Utama</span>
            </div>
        </template>

        <!-- Badge Diskon di Atas Foto Utama -->
        @if($produk->discount_percent > 0)
            <div class="absolute top-4 left-4 bg-orange-500 text-white font-extrabold text-xs px-3.5 py-1.5 rounded-full shadow-md uppercase tracking-wider animate-pulse">
                Diskon {{ $produk->discount_percent }}%
            </div>
        @endif
    </div>

    <!-- Thumbnail Gallery List -->
    @if($produk->images->count() > 0 || $produk->image)
        <div class="flex gap-2.5 overflow-x-auto py-1 scrollbar-thin scrollbar-thumb-slate-200">
            <!-- Primary Image Thumbnail -->
            @if($produk->image)
                @php
                    $mainUrl = Str::startsWith($produk->image, 'http') ? $produk->image : (Str::startsWith($produk->image, '/') ? asset($produk->image) : asset('storage/' . $produk->image));
                @endphp
                <button type="button" 
                        @click="activeImage = '{{ $mainUrl }}'" 
                        :class="activeImage === '{{ $mainUrl }}' ? 'border-orange-500 ring-2 ring-orange-100 scale-95' : 'border-slate-200 hover:border-slate-400'"
                        class="w-20 h-20 rounded-xl border bg-white overflow-hidden shrink-0 transition-all duration-200 shadow-sm flex items-center justify-center">
                    <img src="{{ $mainUrl }}" class="w-full h-full object-cover">
                </button>
            @endif
            
            <!-- Additional Gallery Images -->
            @foreach($produk->images as $img)
                @php
                    $galUrl = Str::startsWith($img->image_path, 'http') ? $img->image_path : (Str::startsWith($img->image_path, '/') ? asset($img->image_path) : asset('storage/' . $img->image_path));
                @endphp
                <button type="button" 
                        @click="activeImage = '{{ $galUrl }}'" 
                        :class="activeImage === '{{ $galUrl }}' ? 'border-orange-500 ring-2 ring-orange-100 scale-95' : 'border-slate-200 hover:border-slate-400'"
                        class="w-20 h-20 rounded-xl border bg-white overflow-hidden shrink-0 transition-all duration-200 shadow-sm flex items-center justify-center">
                    <img src="{{ $galUrl }}" class="w-full h-full object-cover">
                </button>
            @endforeach
        </div>
    @endif
</div>
