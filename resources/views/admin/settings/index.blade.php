@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Pengaturan Website & SEO</h1>
                <p class="mt-2 text-sm text-gray-600">Kelola identitas website, meta tag SEO, FAQ, dan lakukan backup database berkala.</p>
            </div>
            <div>
                <form action="{{ route('settings.backup') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Backup Database SQL
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg">
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg">
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Identitas Web Card -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
                        <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3">Identitas & Kontak Website</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="web_name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Nama Toko/Website</label>
                                <input type="text" id="web_name" name="web_name" value="{{ old('web_name', $settings['web_name'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>
                                @error('web_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="web_phone" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Nomor WhatsApp/Telepon</label>
                                <input type="text" id="web_phone" name="web_phone" value="{{ old('web_phone', $settings['web_phone'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>
                                @error('web_phone') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="web_email" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Alamat Email Resmi</label>
                                <input type="email" id="web_email" name="web_email" value="{{ old('web_email', $settings['web_email'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>
                                @error('web_email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="web_address" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Alamat Fisik Percetakan</label>
                                <textarea id="web_address" name="web_address" rows="3" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>{{ old('web_address', $settings['web_address'] ?? '') }}</textarea>
                                @error('web_address') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="web_about" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Profil Singkat Halaman Tentang Kami</label>
                                <textarea id="web_about" name="web_about" rows="4" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>{{ old('web_about', $settings['web_about'] ?? '') }}</textarea>
                                @error('web_about') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Data Gudang Asal Pengiriman (Biteship) Card -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
                        <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3">Asal Pengiriman (Gudang Utama)</h2>
                        <p class="text-xs text-gray-500 mt-1">Konfigurasi ini digunakan sebagai titik asal perhitungan ongkir Biteship API.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="warehouse_name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Nama Gudang/Toko</label>
                                <input type="text" id="warehouse_name" name="warehouse_name" value="{{ old('warehouse_name', $settings['warehouse_name'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>
                                @error('warehouse_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="warehouse_biteship_origin_id" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Origin ID Biteship (Opsional)</label>
                                <input type="text" id="warehouse_biteship_origin_id" name="warehouse_biteship_origin_id" value="{{ old('warehouse_biteship_origin_id', $settings['warehouse_biteship_origin_id'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm">
                                @error('warehouse_biteship_origin_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                @if(empty($settings['warehouse_biteship_origin_id'] ?? ''))
                                    <p class="text-amber-600 text-[10px] font-semibold mt-1">
                                        ⚠️ Peringatan: Origin ID kosong! Perhitungan ongkir Biteship tidak dapat bekerja tanpa ID wilayah asal.
                                    </p>
                                @endif
                            </div>

                            <div>
                                <label for="warehouse_province" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Provinsi</label>
                                <input type="text" id="warehouse_province" name="warehouse_province" value="{{ old('warehouse_province', $settings['warehouse_province'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>
                                @error('warehouse_province') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="warehouse_city" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kabupaten/Kota</label>
                                <input type="text" id="warehouse_city" name="warehouse_city" value="{{ old('warehouse_city', $settings['warehouse_city'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>
                                @error('warehouse_city') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="warehouse_district" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kecamatan</label>
                                <input type="text" id="warehouse_district" name="warehouse_district" value="{{ old('warehouse_district', $settings['warehouse_district'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>
                                @error('warehouse_district') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="warehouse_subdistrict" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kelurahan/Desa</label>
                                <input type="text" id="warehouse_subdistrict" name="warehouse_subdistrict" value="{{ old('warehouse_subdistrict', $settings['warehouse_subdistrict'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>
                                @error('warehouse_subdistrict') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="warehouse_postal_code" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kode Pos Gudang</label>
                                <input type="text" id="warehouse_postal_code" name="warehouse_postal_code" value="{{ old('warehouse_postal_code', $settings['warehouse_postal_code'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>
                                @error('warehouse_postal_code') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="warehouse_latitude" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Latitude</label>
                                    <input type="text" id="warehouse_latitude" name="warehouse_latitude" value="{{ old('warehouse_latitude', $settings['warehouse_latitude'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm">
                                    @error('warehouse_latitude') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="warehouse_longitude" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Longitude</label>
                                    <input type="text" id="warehouse_longitude" name="warehouse_longitude" value="{{ old('warehouse_longitude', $settings['warehouse_longitude'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm">
                                    @error('warehouse_longitude') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="warehouse_address" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Alamat Lengkap Gudang</label>
                                <textarea id="warehouse_address" name="warehouse_address" rows="3" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>{{ old('warehouse_address', $settings['warehouse_address'] ?? '') }}</textarea>
                                @error('warehouse_address') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Config -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3">Daftar Tanya Jawab (FAQ) Halaman Bantuan</h2>
                        <p class="text-xs text-gray-500">Isi data FAQ dalam format struktur JSON untuk mempermudah pemeliharaan.</p>
                        
                        <div>
                            <textarea id="web_faq" name="web_faq" rows="8" class="w-full rounded-xl border-gray-200 font-mono focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-xs">{{ old('web_faq', $settings['web_faq'] ?? '') }}</textarea>
                            @error('web_faq') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- SEO Management Card -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
                        <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3">Optimasi SEO Halaman Utama</h2>
                        
                        <div>
                            <label for="seo_title" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Meta Title Default</label>
                            <input type="text" id="seo_title" name="seo_title" value="{{ old('seo_title', $settings['seo_title'] ?? '') }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>
                            @error('seo_title') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="seo_description" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Meta Description</label>
                            <textarea id="seo_description" name="seo_description" rows="4" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>{{ old('seo_description', $settings['seo_description'] ?? '') }}</textarea>
                            @error('seo_description') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="seo_keywords" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Keywords (Pisahkan dengan koma)</label>
                            <textarea id="seo_keywords" name="seo_keywords" rows="3" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm text-sm" required>{{ old('seo_keywords', $settings['seo_keywords'] ?? '') }}</textarea>
                            @error('seo_keywords') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Logo Perusahaan Card -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
                        <h2 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3">Logo Resmi Perusahaan</h2>
                        
                        {{-- Preview --}}
                        <div class="flex flex-col items-center justify-center space-y-3">
                            <div class="relative w-[120px] h-[120px] rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center shadow-sm overflow-hidden group">
                                @if(isset($settings['company_logo']) && $settings['company_logo'])
                                    <img src="{{ $settings['company_logo'] }}" alt="Logo Perusahaan" class="w-full h-full object-contain">
                                @else
                                    <div class="text-center p-2">
                                        <svg class="w-8 h-8 text-slate-300 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Belum Ada Logo</span>
                                    </div>
                                @endif
                            </div>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Preview Logo (120x120)</span>
                        </div>

                        {{-- File Input --}}
                        <div>
                            <label for="company_logo" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Upload / Ganti Logo</label>
                            <input type="file" id="company_logo" name="company_logo" accept="image/jpeg,image/png,image/jpg,image/svg+xml,image/webp"
                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 transition-all cursor-pointer">
                            <p class="mt-1.5 text-[9px] text-slate-400 leading-normal">Format: JPG, JPEG, PNG, SVG, WEBP (Maks. 2MB)</p>
                            @error('company_logo') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Delete Button if logo exists --}}
                        @if(isset($settings['company_logo']) && $settings['company_logo'])
                            <div class="pt-2 border-t border-slate-100 flex justify-end">
                                <button type="button" 
                                        onclick="confirmDeleteLogo()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-red-200 text-[10px] font-bold uppercase text-red-500 hover:bg-red-50 rounded-lg transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus Logo
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Action Save Card -->
                    <div class="bg-slate-50 rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-semibold rounded-xl shadow-sm text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors duration-200 cursor-pointer">
                            Simpan Perubahan Pengaturan
                        </button>
                    </div>
                </div>

            </div>
        </form>

        {{-- Hidden Delete Form --}}
        @if(isset($settings['company_logo']) && $settings['company_logo'])
            <form id="delete-logo-form" action="{{ route('settings.delete_logo') }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            <script>
                function confirmDeleteLogo() {
                    if (confirm('Apakah Anda yakin ingin menghapus logo perusahaan?')) {
                        document.getElementById('delete-logo-form').submit();
                    }
                }
            </script>
        @endif

    </div>
</div>
@endsection
