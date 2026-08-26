<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Pengaturan Pengiriman (Biteship)</h1>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Page Action Toolbar Card --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span>Integrasi API Ongkir & Kurir</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Hubungkan website Anda dengan akun Biteship untuk kalkulasi ongkos kirim real-time dan tracking status kurir otomatis.</p>
            </div>
            <div>
                <a href="{{ route('admin.shipping.index') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-[11px] rounded-xl transition-all border border-slate-200 inline-flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Pengiriman</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-2xs flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-xs font-extrabold">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-2xs flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                <p class="text-xs font-extrabold">{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="bg-slate-900 text-white px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-orange-400">Konfigurasi API Biteship</span>
                </div>
                
                {{-- Status Koneksi Bawaan --}}
                @if(!empty($settings['biteship_api_key']))
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-wider inline-flex items-center gap-1">
                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        API Key Terisi
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-500/20 text-rose-300 border border-rose-500/30 uppercase tracking-wider inline-flex items-center gap-1">
                        <svg class="w-3 h-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Belum Terhubung
                    </span>
                @endif
            </div>

            <form action="{{ route('admin.shipping_settings.update') }}" method="POST" class="p-5 space-y-5" id="settings-form" x-data="{ showApiKey: false }">
                @csrf
                
                <div class="space-y-4">
                    {{-- Base URL --}}
                    <div>
                        <label for="biteship_base_url" class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Base URL API Biteship</label>
                        <input type="url" id="biteship_base_url" name="biteship_base_url" 
                               value="{{ old('biteship_base_url', $settings['biteship_base_url']) }}" required
                               class="w-full rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50 focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all py-2 px-3.5">
                        <p class="text-[10px] text-slate-400 mt-1.5 leading-normal font-medium">
                            Gunakan <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-slate-700">https://api.biteship.com</code> untuk Production (Live) maupun Testing (Sandbox Biteship menggunakan test API keys pada host yang sama).
                        </p>
                    </div>

                    {{-- API Key --}}
                    <div>
                        <label for="biteship_api_key" class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Biteship API Key</label>
                        <div class="relative">
                            <input :type="showApiKey ? 'text' : 'password'" id="biteship_api_key" name="biteship_api_key" 
                                   value="{{ old('biteship_api_key', $settings['biteship_api_key']) }}" placeholder="biteship_live_..."
                                   class="w-full rounded-xl border border-slate-200 text-xs font-mono text-slate-800 bg-slate-50 focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all py-2 pl-3.5 pr-10">
                            <button type="button" @click="showApiKey = !showApiKey" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                                <svg x-show="!showApiKey" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showApiKey" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a8.959 8.959 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m-6.398-4.406a3 3 0 01-4.243-4.243m4.243 4.243L3 3l18 18"/></svg>
                            </button>
                        </div>
                        @if(!empty($settings['biteship_api_key']))
                            <p class="text-[10px] text-emerald-600 font-bold mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>API Key telah tersimpan dengan aman di database.</span>
                            </p>
                        @endif
                    </div>

                    {{-- Origin ID --}}
                    <div>
                        <label for="biteship_origin_id" class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Origin ID Biteship (Wilayah Asal)</label>
                        <input type="text" id="biteship_origin_id" name="biteship_origin_id" 
                               value="{{ old('biteship_origin_id', $settings['biteship_origin_id']) }}" placeholder="62a84aa27c13cb24e64f7b60"
                               class="w-full rounded-xl border border-slate-200 text-xs font-mono text-slate-800 bg-slate-50 focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all py-2 px-3.5">
                        <p class="text-[10px] text-slate-400 mt-1.5 leading-normal font-medium">
                            Origin ID adalah kode area Biteship untuk lokasi Gudang Utama pengiriman Anda.
                        </p>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 flex flex-wrap gap-3 justify-between items-center">
                    <div>
                        {{-- Tombol Test Connection --}}
                        <button type="submit" formaction="{{ route('admin.shipping_settings.test') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-200 text-xs font-extrabold text-slate-700 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 rounded-xl transition-all cursor-pointer shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Test Connection</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.shipping.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold rounded-xl text-xs transition-colors border border-slate-200">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-5 py-2 bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-xl shadow-2xs uppercase tracking-wider text-xs cursor-pointer active:scale-[0.98] inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Pengaturan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
