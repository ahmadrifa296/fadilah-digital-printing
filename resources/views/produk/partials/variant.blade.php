@if(in_array($produk->calculation_type, ['custom_size', 'quantity_custom_size']))
    <div class="mt-6 pt-6 border-t border-slate-100 space-y-5">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wide">Tentukan Ukuran Spanduk (Meter)</h3>
        
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label for="custom_length" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Panjang (Meter)</label>
                <input type="number" step="any" name="custom_length" id="custom_length"
                       x-model.number="lengthInput"
                       @input="validateDimensions()"
                       class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3.5 focus:border-orange-500 focus:ring-orange-500"
                       placeholder="Min: {{ $produk->min_length }}m, Max: {{ $produk->max_length }}m">
                <p class="text-[9px] text-slate-400 font-medium">Batas: {{ $produk->min_length }}m - {{ $produk->max_length }}m</p>
            </div>
            <div class="space-y-1.5">
                <label for="custom_width" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lebar (Meter)</label>
                <input type="number" step="any" name="custom_width" id="custom_width"
                       x-model.number="widthInput"
                       @input="validateDimensions()"
                       class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3.5 focus:border-orange-500 focus:ring-orange-500"
                       placeholder="Min: {{ $produk->min_width }}m, Max: {{ $produk->max_width }}m">
                <p class="text-[9px] text-slate-400 font-medium">Batas: {{ $produk->min_width }}m - {{ $produk->max_width }}m</p>
            </div>
        </div>
        
        <!-- Warning Message -->
        <div x-show="dimensionError" class="text-[10px] text-rose-600 font-extrabold" x-text="dimensionError" x-cloak></div>
    </div>
@endif

@if($produk->variants->count() > 0)
    <div class="mt-6 pt-6 border-t border-slate-100 space-y-5">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wide">Pilihan Variasi Cetak</h3>
        
        <!-- Input Hidden untuk mengoper pilihan ke form submission -->
        <input type="hidden" name="ukuran" :value="ukuran">
        <input type="hidden" name="bahan" :value="bahan">
        <input type="hidden" name="finishing" :value="finishing">

        <!-- Variasi Ukuran (Pill Buttons) -->
        @if($produk->variants->where('variant_type', 'ukuran')->count() > 0)
            <div class="space-y-2">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ukuran Cetak</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($produk->variants->where('variant_type', 'ukuran') as $v)
                        <button type="button" 
                                @click="ukuran = '{{ $v->variant_name }}'"
                                :class="ukuran === '{{ $v->variant_name }}' ? 'border-orange-500 bg-orange-50 text-orange-600 font-bold' : 'border-slate-200 hover:border-slate-400 bg-white text-slate-700'"
                                class="text-xs px-4 py-2 rounded-xl border transition-all duration-200 shadow-3xs cursor-pointer flex items-center justify-center">
                            {{ $v->variant_name }}
                            @if($v->price_modifier > 0)
                                <span class="text-[9px] text-slate-400 font-normal ml-1">(+Rp{{ number_format($v->price_modifier, 0, ',', '.') }})</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Variasi Bahan (Pill Buttons) -->
        @if($produk->variants->where('variant_type', 'bahan')->count() > 0)
            <div class="space-y-2">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bahan Cetak</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($produk->variants->where('variant_type', 'bahan') as $v)
                        <button type="button" 
                                @click="bahan = '{{ $v->variant_name }}'"
                                :class="bahan === '{{ $v->variant_name }}' ? 'border-orange-500 bg-orange-50 text-orange-600 font-bold' : 'border-slate-200 hover:border-slate-400 bg-white text-slate-700'"
                                class="text-xs px-4 py-2 rounded-xl border transition-all duration-200 shadow-3xs cursor-pointer flex items-center justify-center">
                            {{ $v->variant_name }}
                            @if($v->price_modifier > 0)
                                <span class="text-[9px] text-slate-400 font-normal ml-1">(+Rp{{ number_format($v->price_modifier, 0, ',', '.') }})</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Variasi Finishing (Pill Buttons) -->
        @if($produk->variants->where('variant_type', 'finishing')->count() > 0)
            <div class="space-y-2">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Finishing</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($produk->variants->where('variant_type', 'finishing') as $v)
                        <button type="button" 
                                @click="finishing = '{{ $v->variant_name }}'"
                                :class="finishing === '{{ $v->variant_name }}' ? 'border-orange-500 bg-orange-50 text-orange-600 font-bold' : 'border-slate-200 hover:border-slate-400 bg-white text-slate-700'"
                                class="text-xs px-4 py-2 rounded-xl border transition-all duration-200 shadow-3xs cursor-pointer flex items-center justify-center">
                            {{ $v->variant_name }}
                            @if($v->price_modifier > 0)
                                <span class="text-[9px] text-slate-400 font-normal ml-1">(+Rp{{ number_format($v->price_modifier, 0, ',', '.') }})</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
