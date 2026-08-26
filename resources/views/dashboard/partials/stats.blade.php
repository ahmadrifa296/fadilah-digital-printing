{{-- KPI Stats — 7 Cards Compact Shopee Style --}}
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
    {{-- Total Order --}}
    <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer group"
         style="min-height:100px" @click="activeTab = 'all'">
        <div class="flex justify-between items-start">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-tight">Total<br>Order</span>
            <div class="p-1.5 rounded-lg bg-orange-50 text-orange-500 group-hover:bg-orange-100 transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>
        <span class="text-xl font-black text-slate-800 tracking-tight leading-none mt-2">{{ $totalOrdersCount }}</span>
    </div>

    {{-- Belum Bayar --}}
    <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer group"
         style="min-height:100px" @click="activeTab = 'unpaid'">
        <div class="flex justify-between items-start">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-tight">Belum<br>Bayar</span>
            <div class="p-1.5 rounded-lg bg-red-50 text-red-500 group-hover:bg-red-100 transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
        </div>
        <span class="text-xl font-black text-slate-800 tracking-tight leading-none mt-2">{{ $unpaidCount }}</span>
    </div>

    {{-- Diproses --}}
    <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer group"
         style="min-height:100px" @click="activeTab = 'processing'">
        <div class="flex justify-between items-start">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-tight">Di<br>Proses</span>
            <div class="p-1.5 rounded-lg bg-amber-50 text-amber-500 group-hover:bg-amber-100 transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <span class="text-xl font-black text-slate-800 tracking-tight leading-none mt-2">{{ $prosesCount }}</span>
    </div>

    {{-- Dikirim --}}
    <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer group"
         style="min-height:100px" @click="activeTab = 'shipping'">
        <div class="flex justify-between items-start">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-tight">Di<br>Kirim</span>
            <div class="p-1.5 rounded-lg bg-blue-50 text-blue-500 group-hover:bg-blue-100 transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
        </div>
        <span class="text-xl font-black text-slate-800 tracking-tight leading-none mt-2">{{ $kirimCount }}</span>
    </div>

    {{-- Selesai --}}
    <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer group"
         style="min-height:100px" @click="activeTab = 'done'">
        <div class="flex justify-between items-start">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-tight">Se<br>lesai</span>
            <div class="p-1.5 rounded-lg bg-green-50 text-green-500 group-hover:bg-green-100 transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <span class="text-xl font-black text-slate-800 tracking-tight leading-none mt-2">{{ $selesaiCount }}</span>
    </div>



    {{-- Keranjang --}}
    <a href="{{ route('cart.index') }}"
       class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer group"
       style="min-height:100px">
        <div class="flex justify-between items-start">
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-tight">Keran<br>jang</span>
            <div class="p-1.5 rounded-lg bg-orange-50 text-orange-500 group-hover:bg-orange-100 transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
        <span class="text-xl font-black text-slate-800 tracking-tight leading-none mt-2">{{ $totalCartsCount }}</span>
    </a>
</div>
