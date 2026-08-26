<x-guest-layout>

    {{-- Success / Resend Link Sent Alert --}}
    @if (session('status') == 'verification-link-sent')
        <div id="alert-status"
             role="alert"
             class="mb-4 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-emerald-800 backdrop-blur-sm transition-all duration-300">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-semibold leading-relaxed">
                Tautan verifikasi baru telah dikirimkan ke alamat email terdaftar Anda.
            </p>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- LOGO & HEADING                                     --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <div class="flex flex-col items-center text-center mb-5 space-y-1">
        <div class="mb-2">
            @if(setting('company_logo'))
                <img src="{{ setting('company_logo') }}" 
                     alt="Logo {{ setting('company_name', 'Fadilah Printing') }}" 
                     class="h-10 w-auto object-contain rounded-2xl shadow-sm border border-slate-100 p-1 bg-slate-50">
            @else
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl
                            bg-orange-500 text-white font-black text-lg shadow-lg shadow-orange-500/30">
                    F
                </div>
            @endif
        </div>
        <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">
            Verifikasi Email Anda
        </h1>
        <p class="text-2xs text-slate-500 max-w-xs leading-relaxed">
            Terima kasih telah mendaftar! Klik tautan verifikasi di email Anda untuk mengaktifkan akun.
        </p>
    </div>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- ACTIONS                                            --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <div class="space-y-4" x-data="{ 
        loading: false,
        submitForm() {
            this.loading = true;
        }
    }">
        {{-- Kirim Ulang Tautan Form --}}
        <form method="POST" action="{{ route('verification.send') }}" @submit="submitForm">
            @csrf
            <button type="submit"
                    id="btn-resend-verification"
                    :disabled="loading"
                    class="w-full h-12 bg-orange-500 hover:bg-orange-600 text-white rounded-2xl font-bold flex items-center justify-center shadow-lg shadow-orange-500/20 transition-all duration-200 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-75 disabled:cursor-not-allowed">
                
                {{-- Normal State --}}
                <span x-show="!loading" class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Kirim Ulang Email Verifikasi
                </span>

                {{-- Loading State --}}
                <span x-show="loading" x-cloak class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Mengirim Ulang...
                </span>
            </button>
        </form>

        {{-- Keluar / Logout Form --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full h-12 flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white
                           text-xs font-bold text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow-md hover:scale-105 active:scale-[0.99] focus:outline-none">
                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar dari Akun
            </button>
        </form>
    </div>

</x-guest-layout>
