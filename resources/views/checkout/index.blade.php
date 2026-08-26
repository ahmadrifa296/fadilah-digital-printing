<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <h1 class="page-title text-slate-800">Konfirmasi Pesanan</h1>
                <p class="page-subtitle text-slate-500">Periksa kembali detail pesanan Anda sebelum membuat pembayaran</p>
            </div>
            <a href="{{ route('cart.index') }}" class="btn-sm btn-secondary flex items-center gap-1.5 rounded-full px-4 py-2 border border-slate-200 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                </svg>
                Kembali ke Keranjang
            </a>
        </div>
    </x-slot>

    {{-- Flash error --}}
    @if(session('error'))
        <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg shadow-sm text-xs font-semibold mb-5 animate-fade-in">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Check if user has shipping address --}}
    @if($addresses->isEmpty())
        <div class="card p-12 text-center bg-white rounded-3xl border border-slate-200 max-w-2xl mx-auto shadow-xl space-y-4">
            <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto text-3xl shadow-sm">
                📍
            </div>
            <h2 class="text-lg font-bold text-slate-800">Anda belum memiliki alamat pengiriman.</h2>
            <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                Anda wajib menambahkan alamat pengiriman terlebih dahulu sebelum dapat melanjutkan ke proses checkout dan pembayaran pesanan.
            </p>
            <div class="pt-2">
                <a href="{{ route('addresses.index') }}" class="btn-md bg-orange-500 hover:bg-orange-600 text-white rounded-full font-bold px-6 py-2.5 shadow-md uppercase tracking-wider inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Alamat Sekarang
                </a>
            </div>
        </div>
    @else
        <div x-data="{
            selectedAddress: {{ json_encode($addresses->first()) }},
            addressesList: {{ json_encode($addresses) }},
            chooseModalOpen: false,
            rates: [],
            selectedRate: null,
            loadingRates: false,
            errorMessage: '',
            shippingCost: 0,
            shippingEstimation: '',
            cartTotal: {{ $cartTotal }},
            shippingMethod: 'delivery',

            init() {
                if (this.shippingMethod === 'delivery') {
                    this.fetchRates();
                }
            },

            selectAddress(addr) {
                this.selectedAddress = addr;
                this.chooseModalOpen = false;
                if (this.shippingMethod === 'delivery') {
                    this.fetchRates();
                }
            },

            fetchRates() {
                if (this.shippingMethod !== 'delivery') return;
                if (!this.selectedAddress) return;
                this.loadingRates = true;
                this.errorMessage = '';
                this.rates = [];
                this.selectedRate = null;
                this.shippingCost = 0;
                this.shippingEstimation = '';

                fetch('{{ route('checkout.rates') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        address_id: this.selectedAddress.id,
                        is_direct: '{{ $isDirect ? 1 : 0 }}'
                    })
                })
                .then(r => r.json())
                .then(data => {
                    this.loadingRates = false;
                    if (data.success) {
                        this.rates = data.rates;
                    } else {
                        this.errorMessage = data.message;
                    }
                })
                .catch(err => {
                    this.loadingRates = false;
                    this.errorMessage = 'Gagal mengambil data estimasi ongkir. Silakan coba beberapa saat lagi.';
                });
            },

            selectRate(rate) {
                this.selectedRate = rate;
                this.shippingCost = rate.price;
                this.shippingEstimation = rate.duration;
            },

            formatRupiah(num) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
            }
        }">
            {{-- Form checkout — multipart/form-data wajib untuk upload file desain --}}
            <form action="{{ route('checkout.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  id="form-checkout">
                @csrf

                {{-- Hidden Input for selected address_id & Biteship shipping snapshot details --}}
                <input type="hidden" name="is_direct" value="{{ $isDirect ? '1' : '0' }}">
                <input type="hidden" name="address_id" :value="selectedAddress ? selectedAddress.id : ''">
                <input type="hidden" name="shipping_courier" :value="selectedRate ? selectedRate.company : ''">
                <input type="hidden" name="shipping_service" :value="selectedRate ? selectedRate.type : ''">
                <input type="hidden" name="shipping_cost" :value="shippingCost">
                <input type="hidden" name="shipping_estimation" :value="shippingEstimation">
                <input type="hidden" name="shipping_method" :value="shippingMethod">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Kiri: detail produk + form --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- Shopee-Style Shipping Address Card --}}
                        <div class="card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="h-1 bg-gradient-to-r from-blue-500 via-orange-500 to-indigo-500"></div>
                            
                            <div class="p-5 space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                        <span class="text-sm">📍</span> Alamat Pengiriman
                                    </h3>
                                    <button type="button" @click="chooseModalOpen = true" class="text-xs font-bold text-orange-500 hover:text-orange-600 flex items-center gap-1 cursor-pointer">
                                        Pilih Alamat Lain
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-150 space-y-2">
                                    <div class="flex items-center flex-wrap gap-2">
                                        <span class="font-bold text-xs text-slate-800" x-text="selectedAddress.receiver_name"></span>
                                        <span class="text-xs text-slate-500 font-medium" x-text="'(' + selectedAddress.phone + ')'"></span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200" x-text="selectedAddress.label"></span>
                                        <template x-if="selectedAddress.is_default">
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-orange-100 text-orange-600 border border-orange-200">Utama</span>
                                        </template>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed font-medium" 
                                       x-text="selectedAddress.full_address + ' (No. ' + selectedAddress.no_rumah + ', RT ' + selectedAddress.rt + '/RW ' + selectedAddress.rw + '), ' + selectedAddress.subdistrict + ', ' + selectedAddress.district + ', ' + selectedAddress.city + ', ' + selectedAddress.province + ' - ' + selectedAddress.postal_code"></p>
                                    
                                    <template x-if="selectedAddress.notes">
                                        <p class="text-[10px] text-slate-400 font-semibold italic mt-1" x-text="'🚚 Catatan Kurir: ' + selectedAddress.notes"></p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Daftar produk yang dipesan --}}
                        <div class="card bg-white rounded-2xl border border-slate-200 shadow-sm">
                            <div class="card-header border-b border-slate-100 px-5 py-4">
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Produk yang Dipesan</h3>
                            </div>
                            <div class="divide-y divide-slate-100">
                                @foreach($cartItems as $item)
                                    <div class="flex items-center gap-4 p-5">
                                        <div class="w-12 h-12 rounded-lg bg-slate-50 flex-shrink-0 overflow-hidden border border-slate-200">
                                            @if($item->product->image)
                                                <img src="{{ Str::startsWith($item->product->image, 'http') ? $item->product->image : (Str::startsWith($item->product->image, '/') ? asset($item->product->image) : asset('storage/' . $item->product->image)) }}"
                                                     alt="{{ $item->product->product_name }}"
                                                     class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-xs font-bold text-slate-800 truncate">{{ $item->product->product_name }}</h4>
                                            <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-500 font-medium">
                                                <span>Bahan: {{ $item->bahan ?: '-' }}</span>
                                                <span>&bull;</span>
                                                @if(isset($item->custom_length) && isset($item->custom_width))
                                                    <span>Ukuran: {{ $item->custom_length }}m x {{ $item->custom_width }}m (Custom)</span>
                                                @else
                                                    <span>Ukuran: {{ $item->ukuran ?: '-' }}</span>
                                                @endif
                                                <span>&bull;</span>
                                                <span>Finishing: {{ $item->finishing ?: '-' }}</span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">
                                                Berat: <span class="font-semibold">{{ $item->product->weight ?? 500 }} g</span>
                                            </div>
                                        </div>

                                        <div class="text-right shrink-0">
                                            <div class="text-xs font-bold text-slate-800">Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                                            <div class="text-[10px] text-slate-500 mt-0.5">x{{ $item->qty }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Pilihan Metode Pengiriman --}}
                        <div class="card bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <span>📦</span> Metode Pengiriman
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col gap-1 items-center text-center justify-center font-bold animate-fade-in"
                                       :class="shippingMethod === 'delivery' ? 'border-orange-500 bg-orange-50/10 shadow-sm' : 'border-slate-200 hover:border-slate-300'">
                                    <input type="radio" name="shipping_method_choice" value="delivery" x-model="shippingMethod" @change="shippingCost = 0; selectedRate = null; shippingEstimation = ''; fetchRates();" class="sr-only">
                                    <span class="text-lg">🚚</span>
                                    <span class="text-xs text-slate-850">Pengiriman</span>
                                </label>
                                <label class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col gap-1 items-center text-center justify-center font-bold animate-fade-in"
                                       :class="shippingMethod === 'pickup' ? 'border-orange-500 bg-orange-50/10 shadow-sm' : 'border-slate-200 hover:border-slate-300'">
                                    <input type="radio" name="shipping_method_choice" value="pickup" x-model="shippingMethod" @change="selectedRate = { company: 'pickup', type: 'Ambil di Toko', price: 0, duration: 'Gratis (Ambil Sendiri di Toko)', service: 'pickup' }; shippingCost = 0; shippingEstimation = 'Gratis (Ambil Sendiri di Toko)';" class="sr-only">
                                    <span class="text-lg">🏪</span>
                                    <span class="text-xs text-slate-850">Ambil di Toko</span>
                                </label>
                            </div>
                        </div>

                        {{-- Kurir & Layanan Pengiriman (Shopee-Style) --}}
                        <div x-show="shippingMethod === 'delivery'" x-transition class="card bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <span>🚚</span> Pilihan Kurir & Layanan Pengiriman
                            </h3>

                            {{-- Rates Loading state --}}
                            <div x-show="loadingRates" class="space-y-3 py-6" x-cloak>
                                <div class="flex items-center justify-center gap-2 text-slate-500">
                                    <svg class="animate-spin h-5 w-5 text-orange-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-xs font-semibold">Menghitung ongkos kirim real-time...</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="h-16 bg-slate-100 rounded-2xl animate-pulse"></div>
                                    <div class="h-16 bg-slate-100 rounded-2xl animate-pulse"></div>
                                </div>
                            </div>

                            {{-- Rates Error message --}}
                            <div x-show="errorMessage" class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-2xl font-semibold text-xs flex justify-between items-center" x-cloak>
                                <span x-text="errorMessage"></span>
                                <button type="button" @click="fetchRates()" class="text-xs font-bold text-rose-900 underline cursor-pointer hover:no-underline">Coba Lagi</button>
                            </div>

                            {{-- Rates List --}}
                            <div x-show="!loadingRates && !errorMessage" class="space-y-3" x-cloak>
                                <template x-if="rates.length === 0">
                                    <p class="text-xs text-slate-400 italic">Masukkan alamat pengiriman di atas untuk melihat pilihan ongkos kirim.</p>
                                </template>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <template x-for="rate in rates" :key="rate.company + '_' + rate.type">
                                        <div @click="selectRate(rate)"
                                             :class="selectedRate && selectedRate.company === rate.company && selectedRate.type === rate.type ? 'border-orange-500 bg-orange-50/10 shadow-sm' : 'border-slate-200 hover:border-slate-300'"
                                             class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex justify-between items-center gap-3">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="px-2 py-0.5 rounded-md font-bold uppercase tracking-wider text-[9px] bg-slate-900 text-white" x-text="rate.company"></span>
                                                    <span class="font-bold text-xs text-slate-800 uppercase" x-text="rate.type"></span>
                                                </div>
                                                <div class="text-[10px] text-slate-400 font-semibold mt-1 flex items-center gap-1">
                                                    <span>⏱️ Estimasi:</span>
                                                    <span x-text="rate.duration"></span>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <div class="text-xs font-extrabold text-orange-600" x-text="formatRupiah(rate.price)"></div>
                                                <div class="text-[9px] text-slate-400 font-bold uppercase mt-0.5" x-text="rate.service"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Unggah file desain --}}
                        @if($designRequired)
                            <div class="card bg-white rounded-2xl border border-slate-200 shadow-sm">
                                <div class="card-header border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Unggah File Desain</h3>
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-600 font-extrabold rounded-md text-[9px] uppercase tracking-wider">Wajib Upload</span>
                                </div>
                                <div class="p-5 space-y-4">
                                    <div>
                                        <label for="design_file" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pilih File Desain <span class="text-rose-500">*</span></label>
                                        <input type="file" id="design_file" name="design_file" accept=".jpg,.jpeg,.png,.pdf,.ai,.cdr,.psd,.zip,.rar" required
                                               class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 transition-all cursor-pointer">
                                        <p class="mt-1.5 text-[9px] text-slate-400 leading-normal">Format: JPG, JPEG, PNG, PDF, AI, CDR, PSD, ZIP, RAR (Maksimal 50 MB)</p>
                                        @error('design_file') <p class="text-rose-650 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100 flex gap-2.5">
                                        <span class="text-sm shrink-0">⚠️</span>
                                        <p class="text-[10px] text-amber-800 leading-relaxed font-semibold">
                                            Produk dalam pesanan Anda memerlukan file desain siap cetak. Silakan unggah file Anda sebelum melakukan pembayaran.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="card bg-white rounded-2xl border border-slate-200 shadow-sm">
                                <div class="card-header border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Unggah File Desain</h3>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-500 font-extrabold rounded-md text-[9px] uppercase tracking-wider">Opsional</span>
                                </div>
                                <div class="p-5 space-y-4">
                                    <div>
                                        <label for="design_file" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pilih File Desain (Jika Ada)</label>
                                        <input type="file" id="design_file" name="design_file" accept=".jpg,.jpeg,.png,.pdf,.ai,.cdr,.psd,.zip,.rar"
                                               class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 transition-all cursor-pointer">
                                        <p class="mt-1.5 text-[9px] text-slate-400 leading-normal">Format: JPG, JPEG, PNG, PDF, AI, CDR, PSD, ZIP, RAR (Maksimal 50 MB)</p>
                                        @error('design_file') <p class="text-rose-650 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-150 flex gap-2.5">
                                        <span class="text-sm shrink-0">💡</span>
                                        <p class="text-[10px] text-slate-600 leading-relaxed font-semibold">
                                            Jika Anda belum memiliki file desain siap cetak, Anda bisa mengosongkannya dan berkonsultasi langsung dengan tim desainer kami setelah pesanan terbuat.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- Kanan: ringkasan + tombol pesan --}}
                    <div class="lg:col-span-1">
                        <div class="card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm sticky top-6 space-y-4">
                            <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3 uppercase tracking-wider text-xs">Ringkasan Pembayaran</h3>

                            <div class="space-y-3 text-xs max-h-48 overflow-y-auto no-scrollbar">
                                @foreach($cartItems as $item)
                                    <div class="flex justify-between items-start text-slate-600">
                                        <span class="truncate mr-2 max-w-40 font-medium">{{ $item->product->product_name }}</span>
                                        <span class="flex-shrink-0 font-bold text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="border-t border-slate-100 pt-3 space-y-2 text-xs">
                                <div class="flex justify-between text-slate-600 font-semibold">
                                    <span>Subtotal Produk</span>
                                    <span class="text-slate-800 font-bold">Rp {{ number_format($cartTotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-slate-600 font-semibold">
                                    <span>Ongkos Kirim</span>
                                    <span class="text-slate-800 font-bold" x-text="shippingMethod === 'pickup' ? 'Gratis (Ambil Sendiri di Toko)' : (shippingCost > 0 ? formatRupiah(shippingCost) : 'Pilih kurir')"></span>
                                </div>
                            </div>

                            <div class="border-t border-slate-100 pt-4">
                                <div class="flex justify-between font-bold text-slate-900 text-sm">
                                    <span>Total Tagihan</span>
                                    <span class="text-orange-600 text-base" x-text="formatRupiah(cartTotal + shippingCost)"></span>
                                </div>
                            </div>

                            <div class="p-3.5 bg-orange-50/50 text-orange-850 border border-orange-100 rounded-xl text-[10px] flex gap-2 leading-relaxed font-semibold">
                                <span class="text-sm shrink-0">💳</span>
                                <span>Pembayaran aman diproses melalui Midtrans Payment Gateway. Anda dapat memilih metode transfer bank, e-wallet, dll.</span>
                            </div>

                            <button type="submit"
                                    form="form-checkout"
                                    :disabled="shippingMethod === 'delivery' && !selectedRate"
                                    :class="shippingMethod === 'delivery' && !selectedRate ? 'bg-slate-350 cursor-not-allowed opacity-60' : 'bg-orange-500 hover:bg-orange-600 cursor-pointer hover:scale-[1.01] active:scale-[0.99]'"
                                    class="w-full btn-lg text-white rounded-full font-bold py-3 shadow-md flex justify-center items-center gap-2 uppercase tracking-wider text-[11px] transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Buat Pesanan & Bayar
                            </button>

                            <p class="text-[9px] text-center text-slate-400 leading-normal">
                                Dengan menekan tombol ini, Anda menyetujui seluruh ketentuan layanan Fadilah Printing.
                            </p>
                        </div>
                    </div>

                </div>
            </form>

            {{-- Modal Pilih Alamat (Shopee-Style Drawer/Modal) --}}
            <div x-show="chooseModalOpen" 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
                 x-transition
                 x-cloak>
                
                <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-slate-150 overflow-hidden flex flex-col max-h-[85vh]"
                     @click.away="chooseModalOpen = false">
                    
                    {{-- Header --}}
                    <div class="bg-slate-900 text-white p-5 flex justify-between items-center shrink-0">
                        <div>
                            <h3 class="font-bold text-[10px] uppercase tracking-wider text-orange-400">Pilih Alamat</h3>
                            <h2 class="text-sm font-bold mt-0.5">Pilih alamat pengiriman untuk pesanan Anda</h2>
                        </div>
                        <button @click="chooseModalOpen = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                    </div>

                    {{-- List Alamat --}}
                    <div class="p-6 overflow-y-auto space-y-3 flex-1 no-scrollbar text-xs">
                        <template x-for="addr in addressesList" :key="addr.id">
                            <div @click="selectAddress(addr)" 
                                 :class="selectedAddress.id === addr.id ? 'border-orange-500 bg-orange-50/10' : 'border-slate-200 hover:border-slate-300'"
                                 class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-start gap-3">
                                
                                <div class="shrink-0 mt-0.5">
                                    <input type="radio" :checked="selectedAddress.id === addr.id" class="text-orange-500 focus:ring-orange-500">
                                </div>

                                <div class="space-y-1">
                                    <div class="flex items-center flex-wrap gap-1.5">
                                        <span class="font-bold text-slate-800" x-text="addr.receiver_name"></span>
                                        <span class="text-slate-500 font-semibold" x-text="'(' + addr.phone + ')'"></span>
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-bold text-[9px]" x-text="addr.label"></span>
                                        <template x-if="addr.is_default">
                                            <span class="px-1.5 py-0.5 rounded bg-orange-100 text-orange-600 font-bold text-[9px]">Utama</span>
                                        </template>
                                    </div>
                                    <p class="text-slate-600 leading-normal font-medium" 
                                       x-text="addr.full_address + ' (No. ' + addr.no_rumah + ', RT ' + addr.rt + '/RW ' + addr.rw + '), ' + addr.subdistrict + ', ' + addr.district + ', ' + addr.city + ', ' + addr.province + ' - ' + addr.postal_code"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-slate-50 px-6 py-4 flex justify-between items-center shrink-0 border-t border-slate-100">
                        <a href="{{ route('addresses.index') }}" class="text-xs font-bold text-orange-500 hover:text-orange-600 flex items-center gap-1">
                            ➕ Kelola Alamat Saya
                        </a>
                        <button @click="chooseModalOpen = false" class="btn-sm bg-slate-200 hover:bg-slate-350 text-slate-800 font-bold rounded-xl px-4 py-2">Batal</button>
                    </div>
                </div>
            </div>

        </div>
    @endif
</x-app-layout>
