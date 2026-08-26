<nav x-data="{ open: false }" class="bg-white border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-slate-800 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-sm">
                            F
                        </div>
                        <span class="font-bold text-slate-800 tracking-tight hidden sm:block">Fadilah<span class="text-slate-400 font-medium">Printing</span></span>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-slate-500 hover:text-slate-800 font-medium">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'owner')
                        <x-nav-link :href="route('kategori.index')" :active="request()->routeIs('kategori.*')" class="text-slate-500 hover:text-slate-800 font-medium">
                            {{ __('Kategori') }}
                        </x-nav-link>

                        <x-nav-link :href="route('produk.index')" :active="request()->routeIs('produk.*')" class="text-slate-500 hover:text-slate-800 font-medium">
                            {{ __('Data Barang') }}
                        </x-nav-link>

                        <x-nav-link :href="route('stok.index')" :active="request()->routeIs('stok.*')" class="text-slate-500 hover:text-slate-800 font-medium">
                            {{ __('Manajemen Stok') }}
                        </x-nav-link>

                        <x-nav-link :href="route('pesanan.index')" :active="request()->routeIs('pesanan.*')" class="text-slate-500 hover:text-slate-800 font-medium">
                            {{ __('Pesanan') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.notifications.index')" :active="request()->routeIs('admin.notifications.*')" class="text-slate-500 hover:text-slate-800 font-medium">
                            {{ __('Notifikasi') }}
                        </x-nav-link>

                        <x-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')" class="text-slate-500 hover:text-slate-800 font-medium">
                            {{ __('Laporan') }}
                        </x-nav-link>

                        <x-nav-link :href="route('transaksi.riwayat')" :active="request()->routeIs('transaksi.*')" class="text-slate-500 hover:text-slate-800 font-medium">
                            {{ __('Riwayat') }}
                        </x-nav-link>
                    @else
                        {{-- Customer: link Cart dengan badge --}}
                        <a href="{{ route('cart.index') }}"
                           class="relative inline-flex items-center gap-1.5 text-sm font-medium px-1 pt-1 border-b-2 {{ request()->routeIs('cart.*') ? 'border-slate-800 text-slate-800' : 'border-transparent text-slate-500 hover:text-slate-800' }} transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Keranjang
                            @php $navCartCount = \App\Models\Cart::getItemCountForUser(Auth::id()); @endphp
                            @if($navCartCount > 0)
                                <span class="absolute -top-1 -right-2 w-4 h-4 bg-slate-800 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                                    {{ $navCartCount > 9 ? '9+' : $navCartCount }}
                                </span>
                            @endif
                        </a>

                        <x-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')" class="text-slate-500 hover:text-slate-800 font-medium">
                            {{ __('Notifikasi Saya') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-2">
                {{-- Notification Dropdown --}}
                <div class="relative" x-data="{ 
                    open: false, 
                    count: 0,
                    notifications: [],
                    init() {
                        this.fetchData();
                        setInterval(() => this.fetchData(), 30000); // Poll every 30s as requested
                    },
                    fetchData() {
                        fetch('{{ route('notifications.navbar_list') }}')
                            .then(res => res.json())
                            .then(data => {
                                this.notifications = data.notifications;
                                this.count = data.unread_count;
                            });
                    }
                }">
                    <button @click="open = !open" class="relative p-2 text-slate-500 hover:text-slate-800 transition-colors focus:outline-none rounded-full hover:bg-slate-100 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="count > 0" 
                              class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[8px] font-black text-white"
                              x-text="count"></span>
                    </button>

                    {{-- Dropdown Card --}}
                    <div x-show="open" 
                         @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-2xl border border-slate-200 shadow-xl z-50 overflow-hidden"
                         x-cloak>
                        
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-800">Notifikasi</span>
                            <form action="{{ route('notifications.read_all') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="text-[10px] font-bold text-orange-500 hover:text-orange-600 focus:outline-none">Tandai Semua Dibaca</button>
                            </form>
                        </div>

                        <div class="max-h-64 overflow-y-auto divide-y divide-slate-100">
                            <template x-for="item in notifications" :key="item.id">
                                <a :href="item.url" 
                                   class="block px-4 py-3 hover:bg-slate-50 transition-colors flex gap-3 text-left"
                                   :class="item.is_read ? '' : 'bg-orange-50/30'">
                                    
                                    {{-- Icon block --}}
                                    <div class="flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center text-white"
                                         :class="{
                                             'bg-blue-500': item.color === 'blue',
                                             'bg-orange-500': item.color === 'orange',
                                             'bg-green-500': item.color === 'green',
                                             'bg-red-500': item.color === 'red',
                                             'bg-amber-500': item.color === 'amber',
                                             'bg-yellow-500': item.color === 'yellow',
                                             'bg-purple-500': item.color === 'purple',
                                             'bg-pink-500': item.color === 'pink',
                                             'bg-cyan-500': item.color === 'cyan',
                                             'bg-slate-500': item.color === 'slate'
                                         }">
                                        {{-- Conditional SVGs --}}
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-html="
                                            item.icon === 'shopping-bag' ? '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z\'/>' :
                                            item.icon === 'shopping-cart' ? '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z\'/>' :
                                            item.icon === 'credit-card' ? '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z\'/>' :
                                            item.icon === 'truck' ? '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 9h4l3 3v5h-2M1 3h11v12M13 9V5a1 1 0 00-1-1H9\'/>' :
                                            item.icon === 'chat' ? '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z\'/>' :
                                            item.icon === 'key' ? '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 7a2 2 0 012 2m-2-2a2 2 0 00-2 2m2-2V4a2 2 0 00-2-2h-3a2 2 0 00-2 2v3m2 3H3a2 2 0 00-2 2v3a2 2 0 002 2h3a2 2 0 002-2v-3a2 2 0 00-2-2z\'/>' :
                                            item.icon === 'tag' ? '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'/>' :
                                            item.icon === 'percent' ? '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 15l6-6m-5 6h.01M14 9h.01M3 21h18M3 10h18M3 7h18M3 4h18\'/>' :
                                            '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9\'/>'
                                        ">
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-800 truncate" x-text="item.title"></p>
                                        <p class="text-[10px] text-slate-500 line-clamp-2 mt-0.5" x-text="item.content"></p>
                                        <p class="text-[9px] text-slate-400 font-medium mt-1" x-text="item.time"></p>
                                    </div>
                                </a>
                            </template>
                            <div x-show="notifications.length === 0" class="p-6 text-center text-xs text-slate-400">
                                Belum ada notifikasi
                            </div>
                        </div>

                        <a href="{{ route('notifications.index') }}" class="block text-center py-2.5 bg-slate-50 border-t border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100 transition-colors">
                            Lihat Semua Notifikasi
                        </a>
                    </div>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-semibold rounded-full text-slate-600 bg-slate-50 hover:bg-slate-100 hover:text-slate-800 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }} 
                                <span class="text-[10px] ml-1 bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full uppercase tracking-wider font-bold">{{ Auth::user()->role }}</span>
                            </div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 11-1.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="url('/')" class="text-slate-600 hover:bg-slate-50 font-medium">
                            {{ __('Lihat Katalog') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')" class="text-slate-600 hover:bg-slate-50 font-medium">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-rose-600 hover:bg-rose-50 font-medium">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:bg-slate-100 focus:text-slate-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white">
        <div class="pt-2 pb-3 space-y-1 border-t border-slate-100">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="font-medium">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'owner')
                <x-responsive-nav-link :href="route('kategori.index')" :active="request()->routeIs('kategori.*')" class="font-medium">
                    {{ __('Kategori') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('produk.index')" :active="request()->routeIs('produk.*')" class="font-medium">
                    {{ __('Data Barang') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('stok.index')" :active="request()->routeIs('stok.*')" class="font-medium">
                    {{ __('Manajemen Stok') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pesanan.index')" :active="request()->routeIs('pesanan.*')" class="font-medium">
                    {{ __('Data Pesanan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.notifications.index')" :active="request()->routeIs('admin.notifications.*')" class="font-medium">
                    {{ __('Data Notifikasi') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('laporan.index')" :active="request()->routeIs('laporan.*')" class="font-medium">
                    {{ __('Laporan Penjualan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('transaksi.riwayat')" :active="request()->routeIs('transaksi.*')" class="font-medium">
                    {{ __('Riwayat Transaksi') }}
                </x-responsive-nav-link>
            @else
                {{-- Customer mobile menu --}}
                <x-responsive-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.*')" class="font-medium">
                    🛒 Keranjang
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')" class="font-medium flex items-center justify-between">
                    <span>🔔 Notifikasi Saya</span>
                    @php $navUnreadNotifs = Auth::user()->unreadNotifications()->count(); @endphp
                    @if($navUnreadNotifs > 0)
                        <span class="px-2 py-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full">
                            {{ $navUnreadNotifs }}
                        </span>
                    @endif
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-slate-100">
            <div class="px-4 flex items-center justify-between">
                <div>
                    <div class="font-bold text-base text-slate-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
                </div>
                <span class="text-[10px] bg-slate-200 text-slate-700 px-2 py-1 rounded-full uppercase tracking-wider font-bold">{{ Auth::user()->role }}</span>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="url('/')" class="font-medium">
                    {{ __('Lihat Katalog') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')" class="font-medium">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-rose-600 font-medium">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>