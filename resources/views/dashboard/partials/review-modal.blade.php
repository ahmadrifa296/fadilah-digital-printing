<div x-show="reviewModalOpen" 
     class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-fade-in" 
     x-cloak>
    <div class="bg-white rounded-3xl p-6 max-w-md w-full border border-slate-100 shadow-2xl space-y-5 animate-[fadeInUp_0.25s_ease-out]" @click.away="reviewModalOpen = false">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3.5">
            <div>
                <h4 class="text-[9px] font-black uppercase text-orange-500 tracking-wider">Penilaian Pelanggan</h4>
                <h3 class="font-extrabold text-slate-800 text-xs mt-0.5">Beri Ulasan Hasil Cetak</h3>
            </div>
            <button @click="reviewModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold transition-colors">&times;</button>
        </div>
        
        <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="order_id" :value="activeOrder">
            <input type="hidden" name="product_id" :value="activeProduct">
            <input type="hidden" name="rating" :value="rating">
            
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Pilih Bintang</label>
                <div class="flex items-center gap-2.5 bg-slate-50/70 p-3 rounded-2xl border border-slate-200/50">
                    <template x-for="i in 5">
                        <button type="button" 
                                @click="rating = i" 
                                @mouseenter="hoverRating = i" 
                                @mouseleave="hoverRating = 0"
                                class="text-2xl transition-all duration-150 transform hover:scale-115 focus:outline-none cursor-pointer">
                            <span :class="(hoverRating ? i <= hoverRating : i <= rating) ? 'text-orange-400' : 'text-slate-200'" x-text="'★'"></span>
                        </button>
                    </template>
                    <span class="text-[9px] font-extrabold text-slate-500 ml-2 uppercase tracking-widest bg-white border border-slate-200 px-2.5 py-0.5 rounded-full" 
                          x-text="rating === 5 ? 'Sangat Puas' : rating === 4 ? 'Puas' : rating === 3 ? 'Cukup' : rating === 2 ? 'Kurang' : 'Buruk'">
                    </span>
                </div>
            </div>
            
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Ulasan & Komentar</label>
                <textarea name="comment" rows="4" required class="w-full rounded-2xl border-slate-200 text-xs py-2.5 px-3.5 focus:border-orange-500 focus:ring-orange-500 placeholder:text-slate-300" placeholder="Ceritakan kepuasan hasil cetak, kualitas bahan, dan pengiriman..."></textarea>
            </div>
            
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Foto Produk (Opsional)</label>
                <div class="relative border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center hover:border-orange-500 transition-colors bg-slate-50/50">
                    <input type="file" name="photo" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-1 text-slate-400">
                        <svg class="mx-auto h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-[10px] font-bold">Pilih foto atau ambil gambar</p>
                    </div>
                </div>
            </div>
            
            <div class="pt-3 flex gap-2 justify-end">
                <button type="button" @click="reviewModalOpen = false" class="btn-sm bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold px-4 py-2 cursor-pointer">Batal</button>
                <button type="submit" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold px-6 py-2 shadow-md cursor-pointer">Kirim Ulasan</button>
            </div>
        </form>
    </div>
</div>
