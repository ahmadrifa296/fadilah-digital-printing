<x-guest-layout>

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
            Konfirmasi Kata Sandi
        </h1>
        <p class="text-2xs text-slate-500 max-w-xs leading-relaxed">
            Ini adalah area aman. Silakan konfirmasikan kata sandi Anda untuk melanjutkan.
        </p>
    </div>

    {{-- ══════════════════════════════════════════════════ --}}
    {{-- CONFIRM PASSWORD FORM                              --}}
    {{-- ══════════════════════════════════════════════════ --}}
    <form method="POST"
          action="{{ route('password.confirm') }}"
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

        {{-- ── Password Input ── --}}
        <div class="space-y-1">
            <label for="password" class="block text-xs font-bold text-slate-700 tracking-wide">
                Kata Sandi
            </label>
            <div class="relative">
                <input id="password"
                       :type="showPass ? 'text' : 'password'"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="Masukkan kata sandi Anda"
                       class="w-full rounded-2xl border py-3 pl-4 pr-12 text-xs transition-all duration-200
                              placeholder-slate-400 text-slate-900 bg-slate-50/50
                              focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500
                              {{ $errors->has('password')
                                  ? 'border-rose-400 focus:ring-rose-500/20 focus:border-rose-500'
                                  : 'border-slate-200 hover:border-slate-300' }}">

                {{-- Eye Toggle --}}
                <button type="button"
                        @click="showPass = !showPass"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                    <svg x-show="!showPass" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
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

        {{-- ── Submit Button ── --}}
        <button type="submit"
                id="btn-confirm-password"
                :disabled="loading"
                class="w-full h-12 bg-orange-500 hover:bg-orange-600 text-white rounded-2xl font-bold flex items-center justify-center shadow-lg shadow-orange-500/20 transition-all duration-200 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-75 disabled:cursor-not-allowed">
            
            {{-- Normal State --}}
            <span x-show="!loading" class="flex items-center justify-center gap-2">
                Konfirmasi
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

</x-guest-layout>
