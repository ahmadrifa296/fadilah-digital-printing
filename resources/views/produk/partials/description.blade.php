<div class="bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm space-y-4"
     x-data="{ activeTab: 1 }">
    
    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Informasi & Spesifikasi</h3>

    <!-- Accordion Item 1: Deskripsi & Spesifikasi -->
    <div class="border border-slate-200/60 rounded-xl overflow-hidden">
        <button type="button" 
                @click="activeTab = activeTab === 1 ? 0 : 1"
                class="w-full bg-slate-50 hover:bg-slate-100/80 px-4 py-3 flex justify-between items-center text-xs font-bold text-slate-700 transition-colors">
            <span>DESKRIPSI & SPESIFIKASI PRODUK</span>
            <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-200"
                 :class="activeTab === 1 ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="activeTab === 1" 
             x-collapse 
             class="p-4 text-xs text-slate-600 leading-relaxed space-y-3.5 border-t border-slate-200/60">
            
            <!-- Spesifikasi Grid -->
            <div class="grid grid-cols-2 gap-y-2 border-b border-slate-100 pb-3.5 max-w-md">
                <span class="text-slate-400 font-semibold">Kategori</span>
                <span class="text-slate-700 font-bold">{{ $produk->category->category_name ?? '-' }}</span>
                
                <span class="text-slate-400 font-semibold">Bahan Dominan</span>
                <span class="text-slate-700 font-bold">Standard Printing</span>
                
                <span class="text-slate-400 font-semibold">Estimasi Produksi</span>
                <span class="text-slate-700 font-bold">1-2 Hari Kerja</span>
                
                <span class="text-slate-400 font-semibold">Stok Master</span>
                <span class="text-slate-700 font-bold">{{ $produk->stock }} unit</span>
            </div>

            <!-- Real Description Content -->
            @if($produk->description)
                <div class="whitespace-pre-line font-normal">
                    {{ $produk->description }}
                </div>
            @else
                <p class="text-slate-400 italic font-normal">Tidak ada deskripsi tambahan untuk produk ini.</p>
            @endif
        </div>
    </div>

    <!-- Accordion Item 2: Cara Pemesanan -->
    <div class="border border-slate-200/60 rounded-xl overflow-hidden">
        <button type="button" 
                @click="activeTab = activeTab === 2 ? 0 : 2"
                class="w-full bg-slate-50 hover:bg-slate-100/80 px-4 py-3 flex justify-between items-center text-xs font-bold text-slate-700 transition-colors">
            <span>CARA PEMESANAN CETAK</span>
            <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-200"
                 :class="activeTab === 2 ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="activeTab === 2" 
             x-collapse 
             class="p-4 text-xs text-slate-600 leading-relaxed space-y-2.5 border-t border-slate-200/60 font-normal" x-cloak>
            <p class="font-bold text-slate-700">Ikuti langkah mudah pemesanan cetak berikut:</p>
            <ol class="list-decimal pl-4 space-y-1.5">
                <li>Pilih konfigurasi spesifikasi cetak Anda (Ukuran, Bahan, Finishing) pada tombol variasi di atas.</li>
                <li>Masukkan jumlah unit cetak yang Anda butuhkan.</li>
                <li>Tuliskan teks kustom atau instruksi khusus cetak Anda pada kolom catatan jika diperlukan.</li>
                <li>Upload file desain siap cetak Anda menggunakan drag-and-drop file uploader (Maks 10MB).</li>
                <li>Klik tombol <span class="font-bold text-orange-500">Beli Sekarang</span> atau <span class="font-bold text-orange-500">Masukkan Keranjang</span>.</li>
                <li>Lanjutkan ke halaman checkout dan selesaikan pembayaran. Tim kami akan memverifikasi file Anda sebelum cetak!</li>
            </ol>
        </div>
    </div>

    <!-- Accordion Item 3: FAQ -->
    <div class="border border-slate-200/60 rounded-xl overflow-hidden">
        <button type="button" 
                @click="activeTab = activeTab === 3 ? 0 : 3"
                class="w-full bg-slate-50 hover:bg-slate-100/80 px-4 py-3 flex justify-between items-center text-xs font-bold text-slate-700 transition-colors">
            <span>FAQ (PERTANYAAN UMUM)</span>
            <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-200"
                 :class="activeTab === 3 ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="activeTab === 3" 
             x-collapse 
             class="p-4 text-xs text-slate-600 leading-relaxed space-y-3.5 border-t border-slate-200/60 font-normal" x-cloak>
            <div>
                <p class="font-bold text-slate-800">Q: Bagaimana jika ukuran/file desain saya sangat besar dan melebihi 10MB?</p>
                <p class="text-slate-500 mt-1">A: Anda dapat mengosongkan input file uploader terlebih dahulu, lalu mengirimkan tautan Google Drive / Wetransfer atau kirim langsung ke Live Chat admin kami setelah melakukan pemesanan.</p>
            </div>
            <div>
                <p class="font-bold text-slate-800">Q: Apakah saya bisa meminta revisi desain?</p>
                <p class="text-slate-500 mt-1">A: Ya. Kami menerima pemesanan cetak plus edit desain ringan. Mohon cantumkan instruksi revisi secara lengkap di kolom Catatan Desain.</p>
            </div>
        </div>
    </div>
</div>
