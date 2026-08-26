<div class="bg-white rounded-2xl border border-slate-200/60 p-12 text-center shadow-sm space-y-4 max-w-lg mx-auto transition-all duration-300 hover:shadow-md">
    <div class="w-20 h-20 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center mx-auto border border-orange-100 shadow-sm animate-bounce">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
    </div>
    <div class="space-y-1.5">
        <h4 class="font-bold text-slate-800 text-sm">Pesanan Tidak Ditemukan</h4>
        <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">
            Belum ada pesanan terdaftar untuk kategori ini atau hasil pencarian tidak cocok dengan kata kunci Anda.
        </p>
    </div>
    <div class="pt-2">
        <a href="{{ url('/') }}" class="inline-flex btn-md bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold px-8 py-2.5 shadow-md hover:shadow-orange-500/20 active:scale-[0.98] transition-all">
            Mulai Belanja
        </a>
    </div>
</div>
