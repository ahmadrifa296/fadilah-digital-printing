<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('produk.index') }}" class="btn-icon btn-ghost">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="page-title">Edit Produk</h1>
                <p class="page-subtitle">Ubah informasi dan detail produk: {{ $produk->product_name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto py-2">
        <form action="{{ route('produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data"
              id="product-form"
              x-data="{ 
                  productType: '{{ old('product_type', $produk->product_type) }}', 
                  calculationType: '{{ old('calculation_type', $produk->calculation_type ?? 'fixed') }}',
                  get isSizeBased() { return ['custom_size','quantity_custom_size'].includes(this.calculationType); },
                  variants: @json($variantsPayload),
                  imagesList: @json($imagesPayload),
                  draggedIndex: null,

                  addFiles(files) {
                      for (let i = 0; i < files.length; i++) {
                          const file = files[i];
                          if (file.size > 2 * 1024 * 1024) {
                              alert('Ukuran file ' + file.name + ' melebihi batas 2MB!');
                              continue;
                          }
                          const reader = new FileReader();
                          reader.onload = (e) => {
                              this.imagesList.push({
                                  id: Date.now() + Math.random(),
                                  type: 'new',
                                  file: file,
                                  previewUrl: e.target.result,
                                  is_primary: this.imagesList.length === 0
                              });
                              this.syncFiles();
                          };
                          reader.readAsDataURL(file);
                      }
                  },

                  removeImage(index) {
                      const wasPrimary = this.imagesList[index].is_primary;
                      this.imagesList.splice(index, 1);
                      if (wasPrimary && this.imagesList.length > 0) {
                          this.imagesList[0].is_primary = true;
                      }
                      this.syncFiles();
                  },

                  setPrimary(index) {
                      this.imagesList.forEach((img, i) => {
                          img.is_primary = (i === index);
                      });
                  },

                  moveImage(fromIndex, toIndex) {
                      if (toIndex < 0 || toIndex >= this.imagesList.length) return;
                      const element = this.imagesList.splice(fromIndex, 1)[0];
                      this.imagesList.splice(toIndex, 0, element);
                      this.syncFiles();
                  },

                  syncFiles() {
                      const dt = new DataTransfer();
                      this.imagesList.forEach(img => {
                          if (img.file) {
                              dt.items.add(img.file);
                          }
                      });
                      document.getElementById('gallery-file-input').files = dt.files;
                  },

                  getImagesMeta() {
                      let fileIndex = 0;
                      return this.imagesList.map(img => {
                          if (img.type === 'existing') {
                              return {
                                  type: 'existing',
                                  id: img.id,
                                  is_primary: img.is_primary
                              };
                          } else {
                              const meta = {
                                  type: 'new',
                                  index: fileIndex,
                                  is_primary: img.is_primary
                              };
                              fileIndex++;
                              return meta;
                          }
                      });
                  }
              }"
              @submit="document.getElementById('images-meta-input').value = JSON.stringify(getImagesMeta())">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Form inputs -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="card p-5 bg-white border border-slate-200 rounded-2xl shadow-sm">
                        <h2 class="text-sm font-black text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-3 mb-4">Informasi Produk</h2>
                        <div class="space-y-4">
                            {{-- Jenis Produk --}}
                            <div class="form-group">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Produk</label>
                                <div class="flex items-center gap-6 pt-1">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" name="product_type" value="custom" x-model="productType" class="text-orange-500 focus:ring-orange-500 border-slate-350 mr-2" required>
                                        <span class="text-xs font-bold text-slate-700">Custom Printing</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" name="product_type" value="ready" x-model="productType" class="text-orange-500 focus:ring-orange-500 border-slate-350 mr-2" required>
                                        <span class="text-xs font-bold text-slate-700">Produk Ready Stock</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Tipe Perhitungan Harga --}}
                            <div class="form-group">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Perhitungan Harga</label>
                                <select name="calculation_type" x-model="calculationType"
                                        class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 form-select" required>
                                    <option value="fixed">Harga Tetap (Fixed)</option>
                                    <option value="quantity">Harga × Jumlah (Quantity)</option>
                                    <option value="custom_size">Harga per m² (Custom Ukuran)</option>
                                    <option value="quantity_custom_size">Harga per m² × Jumlah</option>
                                </select>
                                <p class="text-[9px] text-slate-400 mt-1">
                                    <span x-show="calculationType === 'fixed'">Total = Harga Satuan × Qty</span>
                                    <span x-show="calculationType === 'quantity'">Total = Harga Satuan × Qty (min/max qty berlaku)</span>
                                    <span x-show="calculationType === 'custom_size'">Total = Panjang × Lebar × Harga/m² × Qty</span>
                                    <span x-show="calculationType === 'quantity_custom_size'">Total = Panjang × Lebar × Harga/m² × Qty</span>
                                </p>
                                @error('calculation_type')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Kategori --}}
                            <div class="form-group">
                                <label for="category_id" class="block text-xs font-bold text-slate-700 mb-1">Kategori</label>
                                <select name="category_id" id="category_id"
                                        class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('category_id', $produk->category_id) == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Nama Produk --}}
                            <div class="form-group">
                                <label for="product_name" class="block text-xs font-bold text-slate-700 mb-1">Nama Produk</label>
                                <input type="text" name="product_name" id="product_name"
                                       value="{{ old('product_name', $produk->product_name) }}" required
                                       placeholder="Contoh: Spanduk Flexi Korea"
                                       class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                @error('product_name')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                            </div>

                            {{-- Harga Satuan & Stok (untuk fixed & quantity) --}}
                            <div class="grid grid-cols-2 gap-4" x-show="!isSizeBased">
                                <div class="form-group">
                                    <label for="base_price" class="block text-xs font-bold text-slate-700 mb-1">Harga Satuan (Rp)</label>
                                    <input type="number" name="base_price" id="base_price"
                                           value="{{ old('base_price', $produk->base_price) }}"
                                           x-bind:required="!isSizeBased" :disabled="isSizeBased" min="0"
                                           placeholder="0"
                                           class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    @error('base_price')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="stock" class="block text-xs font-bold text-slate-700 mb-1">Stok</label>
                                    <input type="number" name="stock" id="stock"
                                           value="{{ old('stock', $produk->stock) }}"
                                           x-bind:required="!isSizeBased" :disabled="isSizeBased" min="0"
                                           placeholder="0"
                                           class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    @error('stock')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            {{-- Harga per m² (untuk custom_size & quantity_custom_size) --}}
                            <div class="grid grid-cols-2 gap-4" x-show="isSizeBased" x-cloak>
                                <div class="form-group">
                                    <label for="price_per_square_meter" class="block text-xs font-bold text-slate-700 mb-1">Harga per m² (Rp)</label>
                                    <input type="number" name="price_per_square_meter" id="price_per_square_meter"
                                           value="{{ old('price_per_square_meter', $produk->price_per_square_meter) }}"
                                           x-bind:required="isSizeBased" :disabled="!isSizeBased" min="0"
                                           placeholder="Contoh: 25000"
                                           class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    @error('price_per_square_meter')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="base_price_size" class="block text-xs font-bold text-slate-700 mb-1">Harga Dasar Tambahan (Rp, opsional)</label>
                                    <input type="number" name="base_price" id="base_price_size"
                                           value="{{ old('base_price', $produk->base_price ?: 0) }}"
                                           :disabled="!isSizeBased"
                                           min="0" placeholder="0"
                                           class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                </div>
                            </div>

                            {{-- Min/Max Order --}}
                            <div class="grid grid-cols-2 gap-4" x-show="calculationType !== 'fixed'" x-cloak>
                                <div class="form-group">
                                    <label for="minimum_order" class="block text-xs font-bold text-slate-700 mb-1">Minimal Pembelian (Qty)</label>
                                    <input type="number" name="minimum_order" id="minimum_order"
                                           value="{{ old('minimum_order', $produk->minimum_order ?: 1) }}"
                                           :disabled="calculationType === 'fixed'"
                                           min="1"
                                           class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    @error('minimum_order')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="maximum_order" class="block text-xs font-bold text-slate-700 mb-1">Maksimal Pembelian (Qty)</label>
                                    <input type="number" name="maximum_order" id="maximum_order"
                                           value="{{ old('maximum_order', $produk->maximum_order ?: 100) }}"
                                           :disabled="calculationType === 'fixed'"
                                           min="1"
                                           class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    @error('maximum_order')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            {{-- Batas Ukuran Kustom --}}
                            <div class="grid grid-cols-2 gap-4 p-4 bg-slate-50 border border-slate-200/60 rounded-xl" x-show="isSizeBased" x-cloak>
                                <div class="col-span-2">
                                    <h4 class="text-2xs font-bold text-slate-500 uppercase tracking-wider mb-2">Batasan Dimensi Produk (Meter)</h4>
                                </div>
                                <div class="form-group">
                                    <label for="min_length" class="block text-[10px] font-bold text-slate-500">Panjang Minimal</label>
                                    <input type="number" step="any" name="min_length" id="min_length" value="{{ old('min_length', $produk->min_length ?: 0.1) }}" :disabled="!isSizeBased" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-1.5 mt-1 focus:outline-none focus:border-orange-500">
                                </div>
                                <div class="form-group">
                                    <label for="max_length" class="block text-[10px] font-bold text-slate-500">Panjang Maksimal</label>
                                    <input type="number" step="any" name="max_length" id="max_length" value="{{ old('max_length', $produk->max_length ?: 100.0) }}" :disabled="!isSizeBased" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-1.5 mt-1 focus:outline-none focus:border-orange-500">
                                </div>
                                <div class="form-group">
                                    <label for="min_width" class="block text-[10px] font-bold text-slate-500">Lebar Minimal</label>
                                    <input type="number" step="any" name="min_width" id="min_width" value="{{ old('min_width', $produk->min_width ?: 0.1) }}" :disabled="!isSizeBased" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-1.5 mt-1 focus:outline-none focus:border-orange-500">
                                </div>
                                <div class="form-group">
                                    <label for="max_width" class="block text-[10px] font-bold text-slate-500">Lebar Maksimal</label>
                                    <input type="number" step="any" name="max_width" id="max_width" value="{{ old('max_width', $produk->max_width ?: 100.0) }}" :disabled="!isSizeBased" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-1.5 mt-1 focus:outline-none focus:border-orange-500">
                                </div>
                            </div>

                            {{-- Deskripsi --}}
                            <div class="form-group">
                                <label for="description" class="block text-xs font-bold text-slate-700 mb-1">Deskripsi</label>
                                <textarea name="description" id="description" rows="4"
                                          placeholder="Tuliskan spesifikasi produk..."
                                          class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 resize-none">{{ old('description', $produk->description) }}</textarea>
                                @error('description')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Spesifikasi Variasi (untuk Custom Printing) --}}
                    <div class="card p-5 bg-white border border-slate-200 rounded-2xl shadow-sm" x-show="productType === 'custom'" x-transition x-cloak>
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Variasi Cetak</h3>
                            <button type="button" @click="variants.push({ type: 'ukuran', name: '', price_modifier: 0, stock: 100 })"
                                    class="text-xs font-extrabold text-orange-500 hover:text-orange-600 cursor-pointer">
                                + Tambah Baris
                            </button>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(v, index) in variants" :key="index">
                                <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200/60 items-center">
                                    <div class="col-span-3">
                                        <select :name="'variants['+index+'][type]'" x-model="v.type" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-1.5 focus:outline-none focus:border-orange-500 form-select">
                                            <option value="ukuran">Ukuran</option>
                                            <option value="bahan">Bahan</option>
                                            <option value="finishing">Finishing</option>
                                        </select>
                                    </div>
                                    <div class="col-span-4">
                                        <input type="text" :name="'variants['+index+'][name]'" x-model="v.name" required
                                               placeholder="Nama (e.g. Art Paper)" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-1.5 focus:outline-none focus:border-orange-500">
                                    </div>
                                    <div class="col-span-3">
                                        <input type="number" :name="'variants['+index+'][price_modifier]'" x-model="v.price_modifier" required
                                               placeholder="+/- Harga" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-1.5 focus:outline-none focus:border-orange-500">
                                    </div>
                                    <div class="col-span-1.5">
                                        <input type="number" :name="'variants['+index+'][stock]'" x-model="v.stock" required
                                               placeholder="Stok" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-1.5 focus:outline-none focus:border-orange-500">
                                    </div>
                                    <div class="col-span-0.5 text-right">
                                        <button type="button" @click="variants.splice(index, 1)" class="text-rose-600 hover:text-rose-700 p-1 cursor-pointer">
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="variants.length === 0" class="text-center py-6 text-[10px] text-slate-400 bg-slate-50 border border-dashed border-slate-200 rounded-xl font-medium">
                                Belum ada spesifikasi ditambahkan. Klik "+ Tambah Baris" untuk menambahkan opsi.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Images upload & settings -->
                <div class="space-y-6">
                    {{-- Upload Foto --}}
                    <div class="card p-5 bg-white border border-slate-200 rounded-2xl shadow-sm">
                        <h2 class="text-sm font-black text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-3 mb-4">Galeri Foto Produk</h2>
                        
                        <!-- Drag & Drop Zone -->
                        <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-orange-400 hover:bg-orange-50/5 transition-all cursor-pointer relative"
                             @dragover.prevent=""
                             @drop.prevent="addFiles($event.dataTransfer.files)"
                             @click="document.getElementById('gallery-file-input').click()">
                            <svg class="w-8 h-8 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-[11px] font-bold text-slate-600">Seret foto baru ke sini, atau klik untuk memilih</p>
                            <p class="text-[9px] text-slate-400 mt-1">Maksimal file: 2 MB (JPG, PNG, WEBP)</p>
                            <input type="hidden" name="images_meta" id="images-meta-input">
                            <input type="file" id="gallery-file-input" name="gallery[]" multiple accept="image/*" class="hidden" @change="addFiles($event.target.files)">
                        </div>

                        <!-- Thumbnails list -->
                        <div class="mt-5 space-y-3">
                            <h4 class="text-2xs font-black text-slate-400 uppercase tracking-wider">Urutan Foto (Drag / Klik panah untuk merubah)</h4>
                            <div class="space-y-2">
                                <template x-for="(img, idx) in imagesList" :key="img.id || img.url">
                                    <div class="flex items-center gap-3 p-2 bg-slate-50 border border-slate-200 rounded-xl relative group"
                                         draggable="true"
                                         @dragstart="draggedIndex = idx"
                                         @dragover.prevent=""
                                         @drop="
                                             if (draggedIndex !== null && draggedIndex !== idx) {
                                                 moveImage(draggedIndex, idx);
                                                 draggedIndex = null;
                                             }
                                         ">
                                        <!-- Thumbnail preview -->
                                        <div class="w-12 h-12 rounded-lg overflow-hidden border border-slate-200 bg-white shrink-0">
                                            <img :src="img.url || img.previewUrl" class="w-full h-full object-cover">
                                        </div>

                                        <!-- Meta & Controls -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" @click="setPrimary(idx)"
                                                        :class="img.is_primary ? 'bg-orange-500 text-white border-transparent' : 'bg-white hover:bg-slate-100 text-slate-500 border-slate-200'"
                                                        class="px-2 py-0.5 rounded text-[8px] font-extrabold border uppercase tracking-wider cursor-pointer transition-colors">
                                                    <span x-text="img.is_primary ? '★ Utama' : 'Set Utama'"></span>
                                                </button>
                                            </div>
                                            <!-- Position buttons -->
                                            <div class="flex items-center gap-1 mt-1 text-[9px]">
                                                <button type="button" @click="moveImage(idx, idx - 1)" :disabled="idx === 0" class="text-slate-400 hover:text-slate-700 disabled:opacity-30 cursor-pointer">▲ Naik</button>
                                                <span class="text-slate-300">|</span>
                                                <button type="button" @click="moveImage(idx, idx + 1)" :disabled="idx === imagesList.length - 1" class="text-slate-400 hover:text-slate-700 disabled:opacity-30 cursor-pointer">▼ Turun</button>
                                            </div>
                                        </div>

                                        <!-- Delete button -->
                                        <button type="button" @click="removeImage(idx)" class="text-rose-600 hover:text-rose-700 p-1 shrink-0 cursor-pointer">
                                            🗑️
                                        </button>
                                    </div>
                                </template>
                                <div x-show="imagesList.length === 0" class="text-center py-6 text-[10px] text-slate-400 italic">
                                    Belum ada foto produk.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- File Desain --}}
                    <div class="card p-5 bg-white border border-slate-200 rounded-2xl shadow-sm">
                        <h2 class="text-sm font-black text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-3 mb-4">Persyaratan File Desain</h2>
                        <div class="flex items-center py-1">
                            <input type="checkbox" id="requires_design_file" name="requires_design_file" value="1" {{ old('requires_design_file', $produk->requires_design_file) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-orange-650 focus:ring-orange-550 cursor-pointer">
                            <label for="requires_design_file" class="ml-2 block text-xs text-slate-750 font-bold select-none cursor-pointer">
                                Wajib Upload File Desain
                            </label>
                        </div>
                    </div>

                    {{-- Pengiriman Biteship --}}
                    <div class="card p-5 bg-white border border-slate-200 rounded-2xl shadow-sm">
                        <h2 class="text-sm font-black text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-3 mb-4">Berat & Dimensi (Biteship)</h2>
                        <div class="space-y-3.5">
                            <div class="form-group">
                                <label for="weight" class="block text-xs font-bold text-slate-700 mb-1">Berat (gram)</label>
                                <input type="number" name="weight" id="weight" value="{{ old('weight', $produk->weight) }}" required min="1" class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2 focus:outline-none focus:border-orange-500">
                                @error('weight') <p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="form-group">
                                    <label for="length" class="block text-[10px] font-bold text-slate-500">Panjang (cm)</label>
                                    <input type="number" name="length" id="length" value="{{ old('length', $produk->length) }}" required min="1" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-1.5 mt-1 focus:outline-none focus:border-orange-500">
                                </div>
                                <div class="form-group">
                                    <label for="width" class="block text-[10px] font-bold text-slate-500">Lebar (cm)</label>
                                    <input type="number" name="width" id="width" value="{{ old('width', $produk->width) }}" required min="1" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-1.5 mt-1 focus:outline-none focus:border-orange-500">
                                </div>
                                <div class="form-group">
                                    <label for="height" class="block text-[10px] font-bold text-slate-500">Tinggi (cm)</label>
                                    <input type="number" name="height" id="height" value="{{ old('height', $produk->height) }}" required min="1" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-1.5 mt-1 focus:outline-none focus:border-orange-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 border-t border-slate-200 pt-5">
                <button type="submit" class="inline-flex justify-center items-center gap-1.5 px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-xs rounded-xl shadow-md hover:shadow-orange-500/10 transition-all active:scale-[0.98] cursor-pointer uppercase tracking-wider">
                    Simpan Perubahan
                </button>
                <a href="{{ route('produk.index') }}" class="btn-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl px-6 py-2.5 transition-all">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>