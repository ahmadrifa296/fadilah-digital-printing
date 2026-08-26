<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="btn-icon btn-ghost">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="page-title">Detail Produk</h1>
                    <p class="page-subtitle">{{ $produk->category->category_name ?? 'Kategori Umum' }} &middot; {{ $produk->product_name }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert-success mb-5 animate-fade-in">
            <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-danger mb-5 animate-fade-in">
            <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-24"
         x-data="productDetail()">

        <!-- Main Product Grid: 2 Column Desktop -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
            <!-- Left Side: Gallery (Col 5) -->
            <div class="lg:col-span-5">
                @include('produk.partials.gallery')
            </div>

            <!-- Right Side: Product Info & Order Form (Col 7) -->
            <div class="lg:col-span-7">
                <form action="{{ route('cart.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $produk->id }}">

                    @include('produk.partials.info')
                    @if($produk->isCustom())
                        @include('produk.partials.variant')
                    @endif

                    <!-- Subtotal & Actions Form -->
                    <div class="mt-6 pt-6 border-t border-slate-100 space-y-4">
                        @if($produk->isCustom())
                            <!-- File Upload & Notes Placeholder -->
                            <div class="space-y-3.5">
                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wide">Data Kustom Cetak</h4>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Teks Kustom (Nama/Tulisan Cetak)</label>
                                    <input type="text" name="custom_text" placeholder="Contoh: Toko Berkah Jaya, Spanduk Jualan..."
                                           class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3.5 focus:border-orange-500 focus:ring-orange-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Catatan Cetak</label>
                                    <textarea name="notes" rows="2" placeholder="Tulis instruksi cetak (misal: Laminasi depan saja, warna dibuat lebih gelap, potong A4)..."
                                              class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3.5 focus:border-orange-500 focus:ring-orange-500"></textarea>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Upload File Desain (Maks 10MB)</label>
                                    <div class="relative border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center hover:border-orange-500 transition-colors bg-slate-50/50">
                                        <input type="file" name="design_file" accept=".jpeg,.jpg,.png,.pdf,.zip,.rar" @change="handleFileChange($event)"
                                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                        <div class="space-y-1.5 text-xs">
                                            <svg class="mx-auto h-8 w-8 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <p class="font-bold text-slate-600">Drag & drop desain Anda, atau klik untuk memilih file</p>
                                            <p class="text-[10px] text-slate-400">JPG, PNG, PDF, ZIP, RAR (Maks 10MB)</p>
                                        </div>
                                    </div>
                                    <div x-show="designFileName" class="mt-2 text-xs text-orange-600 font-bold flex items-center gap-1.5" x-cloak>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        File terpilih: <span x-text="designFileName"></span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Quantity Selector -->
                        <div class="flex items-center justify-between bg-slate-50 p-4 rounded-2xl border border-slate-200/60 mt-4">
                            <div>
                                <label class="font-bold text-slate-700 text-xs block">Jumlah Unit</label>
                                <span class="text-[10px] text-slate-400 mt-0.5" x-show="calculationType === 'fixed'">Stok tersedia: {{ $produk->stock }} pcs</span>
                                <span class="text-[10px] text-slate-400 mt-0.5" x-show="calculationType === 'quantity'">Stok: {{ $produk->stock }} &middot; Min: <span x-text="minPurchase"></span> &middot; Max: <span x-text="maxOrder"></span></span>
                                <span class="text-[10px] text-slate-400 mt-0.5" x-show="isSizeBased">Min: <span x-text="minPurchase"></span> &middot; Max: <span x-text="maxOrder"></span></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="qty = Math.max(minPurchase, qty - 1); validateDimensions();" class="w-8 h-8 rounded-full border border-slate-300 flex items-center justify-center font-bold text-slate-600 active:bg-slate-200 cursor-pointer">-</button>
                                <input type="number" name="qty" x-model.number="qty" @input="validateDimensions()" :min="minPurchase" :max="calculationType === 'fixed' ? {{ $produk->stock }} : (isSizeBased ? maxOrder : Math.min({{ $produk->stock }}, maxOrder))" class="w-12 border-0 bg-transparent text-center font-bold focus:ring-0 text-xs">
                                <button type="button" @click="qty = Math.min(calculationType === 'fixed' ? {{ $produk->stock }} : (isSizeBased ? maxOrder : Math.min({{ $produk->stock }}, maxOrder)), qty + 1); validateDimensions();" class="w-8 h-8 rounded-full border border-slate-300 flex items-center justify-center font-bold text-slate-600 active:bg-slate-200 cursor-pointer">+</button>
                            </div>
                        </div>

                        <!-- Real-time Subtotal -->
                        <div class="flex items-baseline justify-between text-xs pt-4 border-t border-dashed border-slate-200">
                            <span class="font-bold text-slate-700">Total Harga</span>
                            <span class="text-lg font-black text-orange-500" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal)"></span>
                        </div>

                        <!-- Action Buttons (Shopee Style) -->
                        <div class="flex items-center gap-3 pt-2">
                            @auth
                                <a href="{{ route('chat.index') }}?product_id={{ $produk->id }}" 
                                   @click.prevent="window.dispatchEvent(new CustomEvent('open-chat-widget'))"
                                   class="p-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl text-slate-600 hover:text-slate-900 transition-colors flex items-center justify-center flex-shrink-0"
                                   title="Chat Admin">
                                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </a>
                                <button type="submit" class="flex-1 btn-lg bg-orange-50 hover:bg-orange-100 text-orange-600 border border-orange-200 rounded-xl font-bold justify-center shadow-sm transition-colors flex items-center justify-center">
                                    Masukkan Keranjang
                                </button>
                                <button type="submit" formaction="{{ route('buy_now', $produk->id) }}" class="flex-1 btn-lg bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold justify-center shadow-md transition-colors flex items-center justify-center">
                                    Beli Sekarang
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="w-full btn-lg bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold justify-center shadow-md transition-colors text-center">
                                    Login Untuk Memesan
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Sticky Bottom Mobile Action Bar (Shopee Style) -->
                    @auth
                        <div class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 p-3.5 flex gap-3 lg:hidden shadow-2xl">
                            <button type="submit" class="flex-1 btn-md bg-orange-50 hover:bg-orange-100 text-orange-600 border border-orange-200 rounded-xl font-bold justify-center transition-colors flex items-center justify-center text-xs">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                + Keranjang
                            </button>
                            <button type="submit" formaction="{{ route('buy_now', $produk->id) }}" class="flex-1 btn-md bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold justify-center shadow-md transition-colors flex items-center justify-center text-xs">
                                Beli Sekarang
                            </button>
                        </div>
                    @endauth
                </form>
            </div>
        </div>

        <!-- Bottom Sections: Description & Reviews -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-10">
            <div class="lg:col-span-8 space-y-8">
                @include('produk.partials.description')
                @include('produk.partials.review')
            </div>
            <div class="lg:col-span-4">
                @include('produk.partials.related')
            </div>
        </div>
    </div>

    @php
        $activeImageUrl = '';
        if ($produk->image) {
            $activeImageUrl = Str::startsWith($produk->image, 'http') ? $produk->image : (Str::startsWith($produk->image, '/') ? asset($produk->image) : asset('storage/' . $produk->image));
        } elseif ($produk->images->count() > 0) {
            $firstImgPath = $produk->images->first()->image_path;
            $activeImageUrl = Str::startsWith($firstImgPath, 'http') ? $firstImgPath : (Str::startsWith($firstImgPath, '/') ? asset($firstImgPath) : asset('storage/' . $firstImgPath));
        }

        $currentLinkedProductImg = 'https://placehold.co/100';
        if ($produk->image) {
            $currentLinkedProductImg = Str::startsWith($produk->image, 'http') ? $produk->image : (Str::startsWith($produk->image, '/') ? asset($produk->image) : asset('storage/' . $produk->image));
        }
    @endphp

    @push('scripts')
    <script>
        function productDetail() {
            return {
                qty: {{ in_array($produk->calculation_type, ['custom_size', 'quantity_custom_size']) ? ($produk->minimum_order ?: 1) : 1 }},
                ukuran: @json($produk->variants->where('variant_type', 'ukuran')->first()?->variant_name ?? ''),
                bahan: @json($produk->variants->where('variant_type', 'bahan')->first()?->variant_name ?? ''),
                finishing: @json($produk->variants->where('variant_type', 'finishing')->first()?->variant_name ?? ''),
                basePrice: {{ $produk->final_price }},
                variants: @json($produk->variants),

                calculationType: '{{ $produk->calculation_type ?: ($produk->is_custom_size ? 'custom_size' : 'fixed') }}',
                pricePerM2: {{ $produk->getFinalPricePerM2Attribute() }},
                lengthInput: {{ $produk->min_length ?: 1.0 }},
                widthInput: {{ $produk->min_width ?: 1.0 }},
                minLength: {{ $produk->min_length ?: 0.1 }},
                maxLength: {{ $produk->max_length ?: 100.0 }},
                minWidth: {{ $produk->min_width ?: 0.1 }},
                maxWidth: {{ $produk->max_width ?: 100.0 }},
                minPurchase: {{ $produk->minimum_order ?: 1 }},
                maxOrder: {{ $produk->maximum_order ?: 99999 }},
                dimensionError: '',

                get isSizeBased() {
                    return ['custom_size', 'quantity_custom_size'].includes(this.calculationType);
                },

                validateDimensions() {
                    this.dimensionError = '';
                    if (this.isSizeBased) {
                        if (this.lengthInput < this.minLength || this.lengthInput > this.maxLength) {
                            this.dimensionError = 'Panjang harus antara ' + this.minLength + 'm dan ' + this.maxLength + 'm.';
                        } else if (this.widthInput < this.minWidth || this.widthInput > this.maxWidth) {
                            this.dimensionError = 'Lebar harus antara ' + this.minWidth + 'm dan ' + this.maxWidth + 'm.';
                        }
                    }
                    if (this.qty < this.minPurchase) {
                        this.qty = this.minPurchase;
                    }
                    if (this.qty > this.maxOrder) {
                        this.qty = this.maxOrder;
                    }
                },

                get unitPrice() {
                    let modifier = 0;
                    if (this.ukuran) {
                        let v = this.variants.find(x => x.variant_type === 'ukuran' && x.variant_name === this.ukuran);
                        if (v) modifier += parseInt(v.price_modifier);
                    }
                    if (this.bahan) {
                        let v = this.variants.find(x => x.variant_type === 'bahan' && x.variant_name === this.bahan);
                        if (v) modifier += parseInt(v.price_modifier);
                    }
                    if (this.finishing) {
                        let v = this.variants.find(x => x.variant_type === 'finishing' && x.variant_name === this.finishing);
                        if (v) modifier += parseInt(v.price_modifier);
                    }

                    if (this.isSizeBased) {
                        const area = this.lengthInput * this.widthInput;
                        return Math.round(area * this.pricePerM2) + this.basePrice + modifier;
                    }
                    return this.basePrice + modifier;
                },
                get subtotal() {
                    return this.unitPrice * this.qty;
                },
                activeImage: @json($activeImageUrl),
                designFileName: '',
                handleFileChange(event) {
                    const file = event.target.files[0];
                    this.designFileName = file ? file.name : '';
                }
            };
        }
    </script>
    <script>
        window.currentLinkedProduct = {
            id: {{ $produk->id }},
            name: "{{ $produk->product_name }}",
            price: {{ in_array($produk->calculation_type, ['custom_size', 'quantity_custom_size']) ? $produk->price_per_m2 : $produk->price }},
            image_url: @json($currentLinkedProductImg)
        };
    </script>
    @endpush
</x-app-layout>
