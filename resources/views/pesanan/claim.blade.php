@extends('layouts.app')

@section('content')
<div class="py-8 bg-slate-50/60 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        
        <!-- Navigation Back -->
        <div class="mb-6">
            <a href="{{ route('dashboard', ['tab' => 'pesanan']) }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-orange-500 transition-colors uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Dashboard Pesanan
            </a>
        </div>

        <!-- Header Title -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-md bg-orange-100 text-orange-700 font-extrabold text-[10px] uppercase tracking-wider">Laporan Garansi</span>
                    <span class="text-xs font-mono font-bold text-slate-400">#{{ $order->invoice_number }}</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Ajukan Klaim Garansi & Refund</h1>
                <p class="text-xs text-slate-500 mt-1">Garansi purna jual & pengembalian dana untuk pesanan cetak Anda.</p>
            </div>
            <div class="shrink-0 bg-slate-50 px-4 py-2.5 rounded-2xl border border-slate-200/70 text-right">
                <span class="block text-[9px] font-bold text-slate-400 uppercase">Total Pesanan</span>
                <span class="text-sm font-black text-slate-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-rose-900 mb-1">Terdapat Kesalahan Pengisian:</h4>
                    <ul class="list-disc pl-4 text-xs font-medium space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Card Form -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            
            <!-- Professional Syarat & Ketentuan Banner -->
            <div class="bg-slate-900 p-6 text-white relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-orange-500/10 rounded-full blur-2xl"></div>
                <div class="flex gap-4 items-start relative z-10">
                    <div class="w-10 h-10 rounded-2xl bg-orange-500/20 border border-orange-500/30 flex items-center justify-center shrink-0 text-orange-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-xs uppercase tracking-wider text-orange-400">Ketentuan Klaim Garansi Fadilah Printing</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mt-1">
                            Wajib menyertakan <strong>video unboxing</strong> paket yang menunjukkan kerusakan atau ketidaksesuaian barang secara jelas. Tim verifikator akan memeriksa klaim dalam kurun waktu 1x24 jam kerja.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Body -->
            <form action="{{ route('pesanan.claim.store', $order->id) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
                @csrf

                <!-- Grid Alasan & Jenis -->
                <div class="space-y-4">
                    <div>
                        <label for="reason" class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-2">Alasan Pengajuan Garansi <span class="text-rose-500">*</span></label>
                        <select id="reason" name="reason" required class="w-full rounded-2xl border-slate-200 text-xs py-3 px-4 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/50 font-semibold text-slate-700 transition-colors">
                            <option value="" disabled selected>-- Pilih Kategori Masalah --</option>
                            <option value="Produk Rusak" {{ old('reason') === 'Produk Rusak' ? 'selected' : '' }}>🚨 Produk Rusak / Cacat Cetak / Fisik Defek</option>
                            <option value="Produk Tidak Sesuai" {{ old('reason') === 'Produk Tidak Sesuai' ? 'selected' : '' }}>📐 Produk Tidak Sesuai Ukuran / Spesifikasi Pesanan</option>
                            <option value="Lainnya" {{ old('reason') === 'Lainnya' ? 'selected' : '' }}>⚠️ Kendala Lainnya</option>
                        </select>
                    </div>

                    <!-- Penjelasan Detail -->
                    <div>
                        <label for="description" class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-2">Deskripsi Detail Kerusakan / Keluhan <span class="text-rose-500">*</span></label>
                        <textarea id="description" name="description" required rows="4" placeholder="Tuliskan kronologi singkat dan bagian cetakan mana yang bermasalah secara detail..."
                                  class="w-full rounded-2xl border-slate-200 text-xs py-3 px-4 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/50 text-slate-700 leading-relaxed font-medium transition-colors h-32">{{ old('description') }}</textarea>
                        <p class="text-[10px] text-slate-400 font-semibold mt-1.5">Penjelasan detail mempercepat tim verifikasi kami memproses penyetujuan klaim.</p>
                    </div>
                </div>

                <!-- Informasi Rekening Refund Card -->
                <div class="bg-gradient-to-br from-slate-50 to-orange-50/30 border border-slate-200 p-5 sm:p-6 rounded-3xl space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-200/80 pb-3">
                        <span class="text-base">💳</span>
                        <h3 class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">
                            Rekening / E-Wallet Tujuan Pengembalian Dana (Refund)
                        </h3>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Nama Bank / E-Wallet -->
                        <div>
                            <label for="bank_name" class="block text-[10px] font-bold text-slate-600 uppercase mb-1.5">Bank / E-Wallet <span class="text-rose-500">*</span></label>
                            <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name') }}" required placeholder="BCA / Mandiri / Dana"
                                   class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-white font-semibold">
                        </div>
                        
                        <!-- Nomor Rekening / Akun -->
                        <div>
                            <label for="bank_account_number" class="block text-[10px] font-bold text-slate-600 uppercase mb-1.5">No. Rekening / HP <span class="text-rose-500">*</span></label>
                            <input type="text" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number') }}" required placeholder="1234567890"
                                   class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-white font-mono font-semibold">
                        </div>
                        
                        <!-- Nama Pemilik Akun -->
                        <div>
                            <label for="bank_account_name" class="block text-[10px] font-bold text-slate-600 uppercase mb-1.5">Nama Pemilik <span class="text-rose-500">*</span></label>
                            <input type="text" id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name') }}" required placeholder="Sesuai Akun Bank"
                                   class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-white font-semibold">
                        </div>
                    </div>
                </div>

                <!-- Unggah Video Unboxing Bukti -->
                <div x-data="{ videoPreview: null, fileName: '' }">
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-2">
                        Upload Video Unboxing Bukti Garansi <span class="text-rose-500">*</span>
                    </label>
                    
                    <div class="mt-1 flex justify-center px-6 pt-6 pb-6 border-2 border-slate-200 border-dashed rounded-3xl hover:border-orange-500/70 transition-all bg-slate-50/40 relative">
                        <div class="space-y-3 text-center">
                            
                            <!-- Video Player Preview -->
                            <div x-show="videoPreview" class="mb-3" x-cloak>
                                <video :src="videoPreview" controls class="mx-auto h-48 w-full max-w-md rounded-2xl shadow-md border border-slate-300 bg-black"></video>
                                <p class="text-[11px] font-mono text-slate-600 mt-2 font-bold" x-text="fileName"></p>
                            </div>

                            <!-- Upload Icon if empty -->
                            <div x-show="!videoPreview" class="w-12 h-12 rounded-2xl bg-orange-50 border border-orange-200 text-orange-500 flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>

                            <div class="flex text-xs text-slate-600 justify-center">
                                <label for="proof_video" class="relative cursor-pointer bg-orange-500 hover:bg-orange-600 text-white font-extrabold px-5 py-2.5 rounded-xl shadow-sm transition-all text-xs uppercase tracking-wider inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span x-text="videoPreview ? 'Ganti Video Unboxing' : 'Pilih File Video Unboxing'">Pilih File Video Unboxing</span>
                                    <input id="proof_video" name="proof_video" type="file" accept="video/*" required class="sr-only"
                                           @change="
                                               const file = $event.target.files[0];
                                               if (file) {
                                                   fileName = file.name;
                                                   videoPreview = URL.createObjectURL(file);
                                               }
                                           ">
                                </label>
                            </div>
                            <p class="text-[10px] text-slate-400 font-semibold">Format MP4, MOV, AVI, MKV (Maksimal 50MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('dashboard', ['tab' => 'pesanan']) }}" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs text-center transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-black text-xs uppercase tracking-wider shadow-md shadow-orange-500/20 active:scale-[0.98] transition-all cursor-pointer inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim Laporan Klaim Garansi
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection

