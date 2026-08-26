<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title text-slate-800">👤 Akun Saya</h1>
            <p class="page-subtitle text-slate-500">Kelola detail profil, alamat pengiriman, dan keamanan akun Anda</p>
        </div>
    </x-slot>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-6 items-start py-2" x-data="{ activeTab: '{{ request()->query('tab', 'profile') }}' }">
        
        <!-- Left Sidebar (Shopee/Tokopedia style) -->
        <div class="w-full lg:w-64 shrink-0 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-6">
            <!-- User Info Card -->
            <div class="flex items-center gap-3.5 pb-4 border-b border-slate-100">
                <div class="w-12 h-12 rounded-full overflow-hidden border border-slate-200 bg-orange-50 flex-shrink-0">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                </div>
                <div class="min-w-0">
                    <h4 class="font-bold text-slate-800 text-xs truncate">{{ $user->name }}</h4>
                    <p class="text-[10px] text-slate-400 font-medium truncate mt-0.5">{{ $user->email }}</p>
                </div>
            </div>

            <!-- Sidebar Menus -->
            <div class="flex flex-col gap-1">
                <!-- Profil Saya Tab -->
                <button @click="activeTab = 'profile'" 
                        :class="activeTab === 'profile' ? 'bg-orange-50 text-orange-600 border border-orange-200' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-transparent'"
                        class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2.5 cursor-pointer">
                    <span class="text-sm">👤</span> Profil Saya
                </button>

                <!-- Alamat Saya Link -->
                <a href="{{ route('addresses.index') }}" 
                   class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-transparent transition-all flex items-center gap-2.5">
                    <span class="text-sm">📍</span> Alamat Saya
                </a>

                <!-- Pesanan Saya Link -->
                <a href="{{ route('dashboard') }}" 
                   class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-transparent transition-all flex items-center gap-2.5">
                    <span class="text-sm">📦</span> Pesanan Saya
                </a>

                <!-- Pengaturan Akun Tab -->
                <button @click="activeTab = 'settings'" 
                        :class="activeTab === 'settings' ? 'bg-orange-50 text-orange-600 border border-orange-200' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-transparent'"
                        class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2.5 cursor-pointer">
                    <span class="text-sm">⚙️</span> Pengaturan Akun
                </button>
            </div>
        </div>

        <!-- Right Content Area -->
        <div class="flex-1 w-full space-y-6">
            
            <!-- TAB: PROFIL SAYA -->
            <div x-show="activeTab === 'profile'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6" x-data="profileForm()">
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Profil Saya</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5 font-semibold">Kelola informasi profil Anda untuk mengontrol, melindungi, dan mengamankan akun.</p>
                </div>

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="flex flex-col-reverse md:flex-row gap-8 items-start">
                    @csrf
                    @method('patch')

                    <!-- Left: Details Form -->
                    <div class="flex-1 space-y-4 w-full">
                        <!-- Username -->
                        <div class="space-y-1.5">
                            <label for="username" class="block text-xs font-bold text-slate-700">Username</label>
                            <input id="username" name="username" type="text" 
                                   class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 @error('username') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                   value="{{ old('username', $user->username) }}" autocomplete="username">
                            @error('username')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Name -->
                        <div class="space-y-1.5">
                            <label for="name" class="block text-xs font-bold text-slate-700">Nama Lengkap</label>
                            <input id="name" name="name" type="text" 
                                   class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 @error('name') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                   value="{{ old('name', $user->name) }}" required autocomplete="name">
                            @error('name')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label for="email" class="block text-xs font-bold text-slate-700">Email</label>
                            <input id="email" name="email" type="email" 
                                   class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 @error('email') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                   value="{{ old('email', $user->email) }}" required autocomplete="email">
                            @error('email')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Nomor HP -->
                        <div class="space-y-1.5">
                            <label for="phone_number" class="block text-xs font-bold text-slate-700">Nomor Telepon</label>
                            <input id="phone_number" name="phone_number" type="text" 
                                   class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 @error('phone_number') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                   value="{{ old('phone_number', $user->phone_number) }}">
                            @error('phone_number')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Gender / Jenis Kelamin -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700">Jenis Kelamin</label>
                            <div class="flex items-center gap-4 pt-1">
                                <label class="inline-flex items-center text-xs text-slate-700 font-semibold cursor-pointer">
                                    <input type="radio" name="gender" value="Laki-laki" class="text-orange-500 focus:ring-orange-500 border-slate-300 mr-2" {{ old('gender', $user->gender) === 'Laki-laki' ? 'checked' : '' }}>
                                    Laki-laki
                                </label>
                                <label class="inline-flex items-center text-xs text-slate-700 font-semibold cursor-pointer">
                                    <input type="radio" name="gender" value="Perempuan" class="text-orange-500 focus:ring-orange-500 border-slate-300 mr-2" {{ old('gender', $user->gender) === 'Perempuan' ? 'checked' : '' }}>
                                    Perempuan
                                </label>
                            </div>
                            @error('gender')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="space-y-1.5">
                            <label for="birth_date" class="block text-xs font-bold text-slate-700">Tanggal Lahir</label>
                            <input id="birth_date" name="birth_date" type="date" 
                                   class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 @error('birth_date') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                   value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '') }}">
                            @error('birth_date')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Bio -->
                        <div class="space-y-1.5">
                            <label for="bio" class="block text-xs font-bold text-slate-700">Bio (Opsional)</label>
                            <textarea id="bio" name="bio" rows="3"
                                      class="w-full text-xs border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 resize-none @error('bio') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror" 
                                      placeholder="Tulis bio singkat Anda...">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')<p class="text-[10px] text-rose-600 font-bold mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Save button -->
                        <div class="pt-2 flex items-center gap-3">
                            <button type="submit" class="inline-flex justify-center items-center gap-1.5 px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-xs rounded-xl shadow-md hover:shadow-orange-500/10 transition-all active:scale-[0.98] cursor-pointer uppercase tracking-wider">
                                Simpan Profil
                            </button>
                            @if (session('status') === 'profile-updated')
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)"
                                   class="text-xs font-semibold text-emerald-600">Berhasil disimpan.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Avatar Selection & Upload -->
                    <div class="w-full md:w-80 flex flex-col items-center border-b md:border-b-0 md:border-l border-slate-100 pb-6 md:pb-0 md:pl-8 space-y-6">
                        <!-- Big Avatar Preview -->
                        <div class="relative w-36 h-36 rounded-full overflow-hidden border-4 border-slate-100 shadow-md">
                            <img :src="previewUrl" alt="Avatar Preview" class="w-full h-full object-cover">
                        </div>

                        <!-- Upload Photo -->
                        <div class="w-full text-center space-y-2">
                            <input type="hidden" :name="selectedPreset ? 'avatar' : ''" :value="selectedPreset">
                            <input type="file" id="profile-upload-input" :name="!selectedPreset ? 'avatar' : ''" accept="image/*" class="hidden" @change="handleFileSelect($event)">
                            <button type="button" @click="document.getElementById('profile-upload-input').click()" 
                                    class="btn-sm bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 hover:border-slate-300 rounded-xl font-bold px-4 py-2 text-xs transition-colors cursor-pointer">
                                Pilih Gambar
                            </button>
                            <p class="text-[10px] text-slate-400 font-medium leading-relaxed max-w-[200px] mx-auto">Maksimal ukuran: 2 MB. Format: JPG, JPEG, PNG, WEBP.</p>
                        </div>

                        <!-- Preset Avatar Selector -->
                        <div class="w-full space-y-3 pt-4 border-t border-slate-100">
                            <h4 class="text-2xs font-bold text-slate-500 uppercase tracking-wide text-left">Pilih Ilustrasi Avatar</h4>
                            <div class="grid grid-cols-5 gap-2.5">
                                <template x-for="preset in presets" :key="preset">
                                    <button type="button" @click="selectPreset(preset)" 
                                            :class="selectedPreset === preset ? 'border-orange-500 ring-2 ring-orange-200' : 'border-slate-200 hover:border-orange-200'"
                                            class="aspect-square rounded-xl border-2 overflow-hidden bg-slate-50 transition-all cursor-pointer">
                                        <img :src="preset" class="w-full h-full object-cover">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TAB: PENGATURAN AKUN (Password & Deletion) -->
            <div x-show="activeTab === 'settings'" x-cloak class="space-y-6">
                <!-- Change Password Form -->
                @include('profile.partials.update-password-form')

                <!-- Delete Account Form -->
                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function profileForm() {
            return {
                previewUrl: '{{ $user->avatar_url }}',
                selectedPreset: '{{ str_starts_with($user->avatar ?? '', 'http') ? $user->avatar : '' }}',
                presets: [
                    'https://api.dicebear.com/7.x/avataaars/svg?seed=Felix',
                    'https://api.dicebear.com/7.x/avataaars/svg?seed=Aneka',
                    'https://api.dicebear.com/7.x/avataaars/svg?seed=Jack',
                    'https://api.dicebear.com/7.x/avataaars/svg?seed=Sophia',
                    'https://api.dicebear.com/7.x/avataaars/svg?seed=Buddy',
                    'https://api.dicebear.com/7.x/avataaars/svg?seed=Mimi',
                    'https://api.dicebear.com/7.x/avataaars/svg?seed=Oliver',
                    'https://api.dicebear.com/7.x/avataaars/svg?seed=Lily',
                    'https://api.dicebear.com/7.x/avataaars/svg?seed=Max',
                    'https://api.dicebear.com/7.x/avataaars/svg?seed=Bella'
                ],
                selectPreset(preset) {
                    this.selectedPreset = preset;
                    this.previewUrl = preset;
                    // Reset file input
                    const fileInput = document.getElementById('profile-upload-input');
                    if (fileInput) fileInput.value = '';
                },
                handleFileSelect(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    if (file.size > 2 * 1024 * 1024) {
                        alert("Ukuran berkas melebihi batas 2MB!");
                        e.target.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const img = new Image();
                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            const size = Math.min(img.width, img.height);
                            canvas.width = 400;
                            canvas.height = 400;

                            // Center crop to square
                            ctx.drawImage(img, (img.width - size) / 2, (img.height - size) / 2, size, size, 0, 0, 400, 400);

                            canvas.toBlob((blob) => {
                                if (blob) {
                                    const croppedFile = new File([blob], file.name, { type: file.type });
                                    const dt = new DataTransfer();
                                    dt.items.add(croppedFile);
                                    e.target.files = dt.files;

                                    this.previewUrl = URL.createObjectURL(blob);
                                    this.selectedPreset = '';
                                }
                            }, file.type);
                        };
                        img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
