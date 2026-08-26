<div class="bg-white rounded-xl border border-slate-200 px-4 py-3.5 shadow-sm flex items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
        {{-- Avatar --}}
        <div class="h-11 w-11 rounded-full overflow-hidden border border-slate-200 bg-orange-50 flex items-center justify-center shadow-sm shrink-0">
            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
        </div>
        <div>
            <h2 class="text-[13px] font-bold text-slate-800 leading-none tracking-tight">{{ Auth::user()->name }}</h2>
            <p class="text-[10px] text-slate-400 font-medium mt-0.5 leading-none">{{ Auth::user()->email }}</p>
            <div class="mt-1.5 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-orange-50 text-orange-600 border border-orange-200">
                    👤 Pelanggan
                </span>
            </div>
        </div>
    </div>
    <a href="{{ route('profile.edit') }}"
       class="shrink-0 text-[10px] font-semibold text-slate-500 hover:text-orange-500 hover:bg-orange-50 px-3 py-1.5 rounded-lg border border-slate-200 hover:border-orange-200 transition-all">
        Edit Profil
    </a>
</div>
