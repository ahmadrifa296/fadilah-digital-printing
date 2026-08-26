<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <h1 class="page-title text-slate-800">Alamat Saya</h1>
                <p class="page-subtitle text-slate-500">Kelola daftar alamat pengiriman belanja Anda</p>
            </div>
            <button @click="$dispatch('open-add-modal')" class="btn-sm btn-primary flex items-center gap-1.5 shadow-md rounded-full px-4 py-2 cursor-pointer transition-transform hover:scale-[1.02]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Alamat Baru
            </button>
        </div>
    </x-slot>

    <div x-data="addressManager()" 
         @open-add-modal.window="openAddModal()"
         class="space-y-6"
         @keydown.escape.window="modalOpen = false">
        
        {{-- Flash notifications --}}
        <div x-show="alert.show" 
             x-transition 
             :class="alert.type === 'success' ? 'bg-emerald-50 border-emerald-500 text-emerald-800' : 'bg-rose-50 border-rose-500 text-rose-800'"
             class="p-4 border-l-4 rounded-r-lg shadow-sm text-xs font-semibold flex items-center justify-between"
             x-cloak>
            <span x-text="alert.message"></span>
            <button @click="alert.show = false" class="text-lg font-bold leading-none">&times;</button>
        </div>

        {{-- Alamat Cards --}}
        <div class="grid grid-cols-1 gap-4">
            <template x-if="addressesList.length === 0">
                <div class="card p-12 text-center bg-white rounded-2xl border border-slate-200 shadow-sm">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="text-sm font-bold text-slate-800">Belum Ada Alamat Pengiriman</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Silakan tambahkan alamat pengiriman Anda agar dapat melanjutkan proses checkout belanja.</p>
                </div>
            </template>

            <template x-for="addr in addressesList" :key="addr.id">
                <div class="card bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center flex-wrap gap-2">
                            <span class="font-bold text-xs text-slate-800 tracking-tight" x-text="addr.receiver_name"></span>
                            <span class="text-xs text-slate-450 font-medium" x-text="'(' + addr.phone + ')'"></span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200" x-text="addr.label"></span>
                            <template x-if="addr.is_default">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-600 border border-orange-200">Alamat Utama</span>
                            </template>
                        </div>
                        
                        <p class="text-xs text-slate-600 leading-relaxed max-w-2xl font-medium">
                            <span x-text="addr.full_address"></span>
                            <span class="text-slate-500" x-text="'(No. ' + addr.no_rumah + ', RT ' + addr.rt + '/RW ' + addr.rw + ')'"></span>
                            <span x-text="', ' + addr.subdistrict + ', ' + addr.district + ', ' + addr.city + ', ' + addr.province + ' - ' + addr.postal_code"></span>
                        </p>
                        
                        <template x-if="addr.patokan">
                            <p class="text-[10px] text-slate-400 font-semibold italic flex items-center gap-1">
                                <span>📍 Patokan:</span>
                                <span x-text="addr.patokan"></span>
                            </p>
                        </template>
                        
                        <template x-if="addr.notes">
                            <p class="text-[11px] text-slate-400 font-semibold flex items-center gap-1.5">
                                <span>🚚 Catatan Kurir:</span>
                                <span class="italic font-medium" x-text="'&ldquo;' + addr.notes + '&rdquo;'"></span>
                            </p>
                        </template>
                    </div>

                    <div class="flex flex-row md:flex-col items-start md:items-end justify-between md:justify-center gap-2.5 shrink-0 border-t md:border-t-0 pt-3 md:pt-0 border-slate-100">
                        <div class="flex items-center gap-2">
                            <button @click="openEditModal(addr)" class="text-xs font-bold text-blue-600 hover:text-blue-700 cursor-pointer">Ubah</button>
                            <span class="text-slate-300">|</span>
                            <button @click="deleteAddress(addr.id)" class="text-xs font-bold text-red-650 hover:text-red-750 cursor-pointer">Hapus</button>
                        </div>
                        
                        <template x-if="!addr.is_default">
                            <button @click="setDefault(addr.id)" class="btn-xs border border-orange-500 text-orange-500 hover:bg-orange-50 rounded-lg font-bold text-[10px] px-2.5 py-1 cursor-pointer transition-colors">Set Utama</button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Add & Edit Modal --}}
        <div x-show="modalOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
             x-transition
             x-cloak>
            
            <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-slate-150 overflow-hidden flex flex-col max-h-[90vh]"
                 @click.away="modalOpen = false">
                
                {{-- Modal Header --}}
                <div class="bg-slate-900 text-white p-5 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="font-bold text-[10px] uppercase tracking-wider text-orange-400" x-text="isEdit ? 'Ubah Alamat' : 'Alamat Baru Pengiriman'"></h3>
                        <h2 class="text-sm font-bold mt-0.5" x-text="isEdit ? 'Perbarui alamat yang sudah ada' : 'Tambahkan alamat pengiriman baru'"></h2>
                    </div>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>
                
                {{-- Modal Body --}}
                <div class="p-6 overflow-y-auto space-y-4 flex-1 no-scrollbar text-xs">
                    
                    {{-- Form Validation Error alerts --}}
                    <div x-show="errors.length > 0" class="p-3.5 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-xl font-semibold space-y-1" x-cloak>
                        <template x-for="err in errors" :key="err">
                            <p x-text="err"></p>
                        </template>
                    </div>

                    <form @submit.prevent="saveAddress()" class="space-y-4" id="address-form">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Penerima</label>
                                <input type="text" x-model="form.receiver_name" required placeholder="Nama lengkap penerima"
                                       class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nomor HP</label>
                                <input type="text" x-model="form.phone" required placeholder="Contoh: 08123456789"
                                       class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Label Alamat</label>
                                <select x-model="form.label" required class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">Pilih Label Alamat</option>
                                    <option value="Rumah">Rumah</option>
                                    <option value="Kantor">Kantor</option>
                                    <option value="Kos">Kos</option>
                                    <option value="Orang Tua">Orang Tua</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kode Pos</label>
                                <input type="text" x-model="form.postal_code" required placeholder="Kode Pos (5 digit)"
                                       class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                            </div>
                        </div>

                        {{-- Dropdown Bertingkat --}}
                        <div class="space-y-3">
                            <h4 class="font-bold text-slate-800 border-b border-slate-100 pb-1">Wilayah Indonesia</h4>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Provinsi</label>
                                <select id="province_select" x-model="form.province" @change="provinceChanged()" required class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">Pilih Provinsi</option>
                                    <template x-for="p in provincesList" :key="p.id">
                                        <option :value="p.name" :data-id="p.id" x-text="p.name" :selected="form.province === p.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kabupaten/Kota</label>
                                    <select id="city_select" x-model="form.city" @change="cityChanged()" required class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                                        <option value="">Pilih Provinsi Dahulu</option>
                                        <template x-for="c in citiesList" :key="c.id">
                                            <option :value="c.name" :data-id="c.id" x-text="c.name" :selected="form.city === c.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kecamatan</label>
                                    <select id="district_select" x-model="form.district" @change="districtChanged()" required class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                                        <option value="">Pilih Kabupaten Dahulu</option>
                                        <template x-for="d in districtsList" :key="d.id">
                                            <option :value="d.name" :data-id="d.id" x-text="d.name" :selected="form.district === d.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kelurahan/Desa</label>
                                <select id="subdistrict_select" x-model="form.subdistrict" required class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                                    <option value="">Pilih Kecamatan Dahulu</option>
                                    <template x-for="s in subdistrictsList" :key="s.id">
                                        <option :value="s.name" x-text="s.name" :selected="form.subdistrict === s.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- RT, RW, Nomor Rumah --}}
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">RT</label>
                                <input type="text" x-model="form.rt" required placeholder="Contoh: 002"
                                       class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">RW</label>
                                <input type="text" x-model="form.rw" required placeholder="Contoh: 015"
                                       class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">No. Rumah</label>
                                <input type="text" x-model="form.no_rumah" required placeholder="Contoh: No. 12B"
                                       class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Patokan Alamat (Opsional)</label>
                                <input type="text" x-model="form.patokan" placeholder="Contoh: Depan warung kopi"
                                       class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Catatan Kurir (Opsional)</label>
                                <input type="text" x-model="form.notes" placeholder="Titip satpam, taruh pagar..."
                                       class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Alamat Lengkap</label>
                            <textarea x-model="form.full_address" required rows="3" placeholder="Nama jalan, gedung, blok, RT/RW"
                                      class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500"></textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is_default" x-model="form.is_default" class="rounded text-orange-500 focus:ring-orange-500">
                            <label for="is_default" class="text-xs font-semibold text-slate-700 select-none">Jadikan Alamat Utama</label>
                        </div>

                        {{-- Footer Buttons --}}
                        <div class="flex gap-2 justify-end pt-3 border-t border-slate-100">
                            <button type="button" @click="modalOpen = false" class="btn-sm bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold px-4 py-2 cursor-pointer">Batal</button>
                            <button type="submit" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold px-6 py-2 shadow-md uppercase tracking-wider text-[10px] cursor-pointer">Simpan Alamat</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function addressManager() {
            return {
                modalOpen: false,
                isEdit: false,
                addressesList: @json($addresses),
                provincesList: [],
                citiesList: [],
                districtsList: [],
                subdistrictsList: [],
                errors: [],
                alert: {
                    show: false,
                    type: 'success',
                    message: ''
                },
                form: {
                    id: '',
                    label: '',
                    receiver_name: '',
                    phone: '',
                    province: '',
                    city: '',
                    district: '',
                    subdistrict: '',
                    postal_code: '',
                    rt: '',
                    rw: '',
                    no_rumah: '',
                    patokan: '',
                    full_address: '',
                    notes: '',
                    is_default: false
                },

                init() {
                    this.loadProvinces();
                },

                showAlert(type, message) {
                    this.alert.show = true;
                    this.alert.type = type;
                    this.alert.message = message;
                    setTimeout(() => {
                        this.alert.show = false;
                    }, 5000);
                },

                openAddModal() {
                    this.isEdit = false;
                    this.errors = [];
                    this.form = {
                        id: '',
                        label: '',
                        receiver_name: '',
                        phone: '',
                        province: '',
                        city: '',
                        district: '',
                        subdistrict: '',
                        postal_code: '',
                        rt: '',
                        rw: '',
                        no_rumah: '',
                        patokan: '',
                        full_address: '',
                        notes: '',
                        is_default: false
                    };
                    this.citiesList = [];
                    this.districtsList = [];
                    this.subdistrictsList = [];
                    this.modalOpen = true;
                },

                openEditModal(addr) {
                    this.isEdit = true;
                    this.errors = [];
                    this.form = {
                        id: addr.id,
                        label: addr.label,
                        receiver_name: addr.receiver_name,
                        phone: addr.phone,
                        province: addr.province,
                        city: addr.city,
                        district: addr.district,
                        subdistrict: addr.subdistrict,
                        postal_code: addr.postal_code,
                        rt: addr.rt,
                        rw: addr.rw,
                        no_rumah: addr.no_rumah,
                        patokan: addr.patokan || '',
                        full_address: addr.full_address,
                        notes: addr.notes || '',
                        is_default: !!addr.is_default
                    };
                    
                    // Pre-populate dropdown list elements as plain text arrays to prevent lock
                    this.citiesList = [{ id: '', name: addr.city }];
                    this.districtsList = [{ id: '', name: addr.district }];
                    this.subdistrictsList = [{ id: '', name: addr.subdistrict }];
                    
                    this.modalOpen = true;

                    // Fetch actual ID lists asynchronously in background
                    this.restoreDropdowns(addr);
                },

                loadProvinces() {
                    fetch('/api/address/provinces')
                        .then(r => r.json())
                        .then(data => {
                            this.provincesList = data;
                        })
                        .catch(err => console.error('Gagal memuat provinsi:', err));
                },

                provinceChanged() {
                    this.citiesList = [];
                    this.districtsList = [];
                    this.subdistrictsList = [];
                    this.form.city = '';
                    this.form.district = '';
                    this.form.subdistrict = '';

                    const select = document.getElementById('province_select');
                    const opt = select.options[select.selectedIndex];
                    const provId = opt.dataset.id;

                    if (!provId) return;

                    fetch(`/api/address/regencies/${provId}`)
                        .then(r => r.json())
                        .then(data => {
                            this.citiesList = data;
                        });
                },

                cityChanged() {
                    this.districtsList = [];
                    this.subdistrictsList = [];
                    this.form.district = '';
                    this.form.subdistrict = '';

                    const select = document.getElementById('city_select');
                    const opt = select.options[select.selectedIndex];
                    const cityId = opt.dataset.id;

                    if (!cityId) return;

                    fetch(`/api/address/districts/${cityId}`)
                        .then(r => r.json())
                        .then(data => {
                            this.districtsList = data;
                        });
                },

                districtChanged() {
                    this.subdistrictsList = [];
                    this.form.subdistrict = '';

                    const select = document.getElementById('district_select');
                    const opt = select.options[select.selectedIndex];
                    const distId = opt.dataset.id;

                    if (!distId) return;

                    fetch(`/api/address/villages/${distId}`)
                        .then(r => r.json())
                        .then(data => {
                            this.subdistrictsList = data;
                        });
                },

                restoreDropdowns(addr) {
                    // Try to find province ID
                    fetch('/api/address/provinces')
                        .then(r => r.json())
                        .then(provinces => {
                            this.provincesList = provinces;
                            const provObj = provinces.find(p => p.name.toUpperCase() === addr.province.toUpperCase());
                            if (provObj) {
                                // Fetch cities
                                return fetch(`/api/address/regencies/${provObj.id}`);
                            }
                        })
                        .then(r => r ? r.json() : [])
                        .then(cities => {
                            if (cities.length > 0) this.citiesList = cities;
                            const cityObj = cities.find(c => c.name.toUpperCase() === addr.city.toUpperCase());
                            if (cityObj) {
                                // Fetch districts
                                return fetch(`/api/address/districts/${cityObj.id}`);
                            }
                        })
                        .then(r => r ? r.json() : [])
                        .then(districts => {
                            if (districts.length > 0) this.districtsList = districts;
                            const distObj = districts.find(d => d.name.toUpperCase() === addr.district.toUpperCase());
                            if (distObj) {
                                // Fetch subdistricts
                                return fetch(`/api/address/villages/${distObj.id}`);
                            }
                        })
                        .then(r => r ? r.json() : [])
                        .then(subdistricts => {
                            if (subdistricts.length > 0) this.subdistrictsList = subdistricts;
                        })
                        .catch(err => console.error('Gagal me-restore dropdown alamat:', err));
                },

                saveAddress() {
                    this.errors = [];
                    const url = this.isEdit ? `/addresses/${this.form.id}` : '/addresses';
                    const method = this.isEdit ? 'PUT' : 'POST';

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: method,
                            label: this.form.label,
                            receiver_name: this.form.receiver_name,
                            phone: this.form.phone,
                            province: this.form.province,
                            city: this.form.city,
                            district: this.form.district,
                            subdistrict: this.form.subdistrict,
                            postal_code: this.form.postal_code,
                            rt: this.form.rt,
                            rw: this.form.rw,
                            no_rumah: this.form.no_rumah,
                            patokan: this.form.patokan,
                            full_address: this.form.full_address,
                            notes: this.form.notes,
                            is_default: this.form.is_default ? 1 : 0
                        })
                    })
                    .then(r => r.json().then(data => ({ status: r.status, body: data })))
                    .then(res => {
                        if (res.status === 422) {
                            this.errors = Object.values(res.body.errors).flat();
                        } else if (res.status === 200 || res.status === 201) {
                            this.addressesList = res.body.addresses;
                            this.modalOpen = false;
                            this.showAlert('success', res.body.message);
                        } else {
                            this.errors = ['Terjadi kesalahan sistem. Silakan coba lagi.'];
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        this.errors = ['Gagal menghubungi server.'];
                    });
                },

                deleteAddress(id) {
                    if (!confirm('Apakah Anda yakin ingin menghapus alamat ini?')) return;

                    fetch(`/addresses/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: 'DELETE'
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.addressesList = data.addresses;
                        this.showAlert('success', data.message);
                    })
                    .catch(err => {
                        console.error(err);
                        this.showAlert('error', 'Gagal menghapus alamat.');
                    });
                },

                setDefault(id) {
                    fetch(`/addresses/${id}/default`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.addressesList = data.addresses;
                        this.showAlert('success', data.message);
                    })
                    .catch(err => {
                        console.error(err);
                        this.showAlert('error', 'Gagal mengatur alamat utama.');
                    });
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
