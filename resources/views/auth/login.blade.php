<x-guest-layout>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- FLASH MESSAGES & ALERTS                            --}}
    {{-- ══════════════════════════════════════════════════ --}}
    
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

    {{-- Error Alert --}}
    @if (session('error'))
        <div id="alert-error"
             role="alert"
             class="mb-4 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50/80 px-4 py-3 text-rose-800 backdrop-blur-sm transition-all duration-300">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-semibold leading-relaxed">{{ session('error') }}</p>
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
            Selamat Datang Kembali
        </h1>
        <p class="text-2xs text-slate-500 max-w-xs leading-relaxed">
            Masuk ke akun Anda untuk mengelola pesanan cetak digital
        </p>
    </div>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- LOGIN FORM                                         --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <form id="login-form"
          method="POST"
          action="{{ route('login') }}"
          class="space-y-3.5"
          x-data="{ 
              loading: false,
              showPass: false,
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
                       autocomplete="username"
                       placeholder="nama@email.com"
                       class="w-full rounded-2xl border py-3 px-4 text-xs transition-all duration-200
                              placeholder-slate-400 text-slate-900 bg-slate-50/50
                              focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500
                              {{ $errors->has('email')
                                  ? 'border-rose-400 focus:ring-rose-500/20 focus:border-rose-500'
                                  : 'border-slate-200 hover:border-slate-300' }}"
                       aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
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

        {{-- ── Password Input ── --}}
        <div class="space-y-1">
            <div class="flex items-center justify-between">
                <label for="password" class="block text-xs font-bold text-slate-700 tracking-wide">
                    Kata Sandi
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs font-bold text-orange-500 hover:text-orange-600 transition-colors duration-150">
                        Lupa Password?
                    </a>
                @endif
            </div>

            <div class="relative">
                <input id="password"
                       :type="showPass ? 'text' : 'password'"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="Masukkan kata sandi"
                       class="w-full rounded-2xl border py-3 pl-4 pr-12 text-xs transition-all duration-200
                              placeholder-slate-400 text-slate-900 bg-slate-50/50
                              focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500
                              {{ $errors->has('password')
                                  ? 'border-rose-400 focus:ring-rose-500/20 focus:border-rose-500'
                                  : 'border-slate-200 hover:border-slate-300' }}">

                {{-- Eye Toggle Button --}}
                <button type="button"
                        @click="showPass = !showPass"
                        :aria-label="showPass ? 'Sembunyikan password' : 'Tampilkan password'"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                    {{-- Eye Icon --}}
                    <svg x-show="!showPass" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{-- Eye-Off Icon --}}
                    <svg x-show="showPass" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 01-1.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <div class="flex items-center gap-1.5 mt-1 text-rose-600 animate-[fadeInDown_0.2s_ease-out]">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-[11px] font-semibold">{{ $message }}</p>
                </div>
            @enderror
        </div>

        {{-- ── Remember Me Checkbox ── --}}
        <div class="flex items-center gap-2.5 pt-0.5">
            <input id="remember_me"
                   type="checkbox"
                   name="remember"
                   class="h-4 w-4 rounded border-slate-300 text-orange-500
                          focus:ring-2 focus:ring-orange-500 focus:ring-offset-0
                          transition-colors duration-150 cursor-pointer">
            <label for="remember_me"
                   class="cursor-pointer select-none text-xs font-medium text-slate-600 hover:text-slate-800 transition-colors duration-150">
                Ingat saya di perangkat ini
            </label>
        </div>

        {{-- ── Submit Button ── --}}
        <button type="submit"
                id="btn-email-login"
                :disabled="loading"
                class="w-full h-12 bg-orange-500 hover:bg-orange-600 text-white rounded-2xl font-bold flex items-center justify-center shadow-lg shadow-orange-500/20 transition-all duration-200 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-75 disabled:cursor-not-allowed z-20">
            
            {{-- Normal State --}}
            <span x-show="!loading" class="flex items-center justify-center gap-2">
                Masuk ke Akun
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

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- DIVIDER                                            --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <div class="relative my-4 flex items-center">
        <div class="flex-grow border-t border-slate-200"></div>
        <span class="mx-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            atau
        </span>
        <div class="flex-grow border-t border-slate-200"></div>
    </div>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- TOMBOL GOOGLE                                      --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <div x-data="{ googleLoading: false }">
        <a href="{{ route('auth.google') }}"
           id="btn-google-login"
           @click="googleLoading = true"
           class="group flex w-full h-12 items-center justify-center gap-3 rounded-2xl
                  border border-slate-200 bg-white px-4
                  text-xs font-semibold text-slate-700 shadow-sm
                  transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:scale-105
                  active:scale-[0.99] active:translate-y-0
                  focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2"
           :class="{ 'opacity-70 pointer-events-none': googleLoading }">

            {{-- Normal State --}}
            <span x-show="!googleLoading" class="flex items-center gap-2">
                {{-- Official Google SVG Logo (w-5 h-5) --}}
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span>Masuk dengan Google</span>
            </span>

            {{-- Loading State --}}
            <span x-show="googleLoading" x-cloak class="flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin text-slate-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="text-slate-500">Menghubungkan...</span>
            </span>
        </a>
    </div>

    {{-- ── Register Link ── --}}
    <div class="mt-5 text-center text-xs">
        <span class="text-slate-500">Belum memiliki akun?</span>
        <a href="{{ route('register') }}"
           class="ml-1 font-bold text-orange-500 hover:text-orange-600 transition-colors duration-150">
            Daftar Sekarang
        </a>
    </div>

</x-guest-layout>
