<x-guest-layout>

    {{-- Success Status Alert --}}
    @if (session('status'))
        <div id="alert-status"
             role="alert"
             class="mb-4 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-emerald-800 backdrop-blur-sm transition-all duration-300">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-semibold leading-relaxed">{{ session('status') }}</p>
        </div>
    @endif

    {{-- Mail Log Warning Alert --}}
    @if (config('mail.default') === 'log')
        <div class="mb-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-amber-800 backdrop-blur-sm transition-all duration-300">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-[11px] font-semibold leading-relaxed">
                <strong>Simulasi Log Aktif</strong>: Tautan reset akan ditulis ke file log <code class="bg-amber-100/50 px-1 py-0.5 rounded text-amber-900">storage/logs/laravel.log</code>.
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
            Lupa Kata Sandi?
        </h1>
        <p class="text-2xs text-slate-500 max-w-xs leading-relaxed">
            Masukkan email terdaftar Anda, kami akan mengirimkan tautan pemulihan akun
        </p>
    </div>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- FORGOT PASSWORD FORM                               --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <form id="forgot-password-form"
          method="POST"
          action="{{ route('password.email') }}"
          class="space-y-3.5"
          x-data="{ 
              loading: false,
              submitForm() {
                  this.loading = true;
              }
          }"
          @submit="submitForm">
        @csrf

        {{-- ── Email Input ── --}}
        <div class="space-y-1">
            <label for="email" class="block text-xs font-bold text-slate-700 tracking-wide">
                Alamat Email
            </label>
            <div class="relative">
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       placeholder="nama@email.com"
                       class="w-full rounded-2xl border py-3 px-4 text-xs transition-all duration-200
                              placeholder-slate-400 text-slate-900 bg-slate-50/50
                              focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500
                              {{ $errors->has('email')
                                  ? 'border-rose-400 focus:ring-rose-500/20 focus:border-rose-500'
                                  : 'border-slate-200 hover:border-slate-300' }}">
            </div>
            @error('email')
                <div class="flex items-center gap-1.5 mt-1 text-rose-600 animate-[fadeInDown_0.2s_ease-out]">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-[11px] font-semibold">{{ $message }}</p>
                </div>
            @enderror
        </div>

        {{-- ── Submit Button ── --}}
        <button type="submit"
                id="btn-forgot"
                :disabled="loading"
                class="w-full h-12 bg-orange-500 hover:bg-orange-600 text-white rounded-2xl font-bold flex items-center justify-center shadow-lg shadow-orange-500/20 transition-all duration-200 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-75 disabled:cursor-not-allowed">
            
            {{-- Normal State --}}
            <span x-show="!loading" class="flex items-center justify-center gap-2">
                Kirim Tautan Atur Ulang
            </span>

            {{-- Loading State --}}
            <span x-show="loading" x-cloak class="flex items-center justify-center gap-2">
                <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Memproses...
            </span>
        </button>
    </form>

    {{-- ── Back to Login Link ── --}}
    <div class="mt-5 text-center text-xs">
        <a href="{{ route('login') }}"
           class="font-bold text-orange-500 hover:text-orange-600 transition-colors duration-150 flex items-center justify-center gap-1.5">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Halaman Masuk
        </a>
    </div>

</x-guest-layout>
