<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    
    {{-- Google Fonts - Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-50 text-surface-900 antialiased min-h-screen flex flex-col font-sans" 
      x-data="{ 
          searchQuery: '', 
          selectedCategory: 'all',
          activeFAQ: null,
          orderModalOpen: false,
          activeProduct: null,
          qty: 1,
          ukuran: '',
          bahan: '',
          finishing: '',
          notes: '',
          customText: '',
          openModal(product) {
              this.activeProduct = product;
              this.qty = 1;
              this.notes = '';
              this.customText = '';
              this.ukuran = product.variants.filter(v => v.variant_type === 'ukuran')[0]?.variant_name || '';
              this.bahan = product.variants.filter(v => v.variant_type === 'bahan')[0]?.variant_name || '';
              this.finishing = product.variants.filter(v => v.variant_type === 'finishing')[0]?.variant_name || '';
              this.orderModalOpen = true;
          },
          get unitPrice() {
              if (!this.activeProduct) return 0;
              let price = parseFloat(this.activeProduct.final_price);
              
              if (this.ukuran) {
                  let v = this.activeProduct.variants.find(x => x.variant_type === 'ukuran' && x.variant_name === this.ukuran);
                  if (v) price += parseFloat(v.price_modifier);
              }
              if (this.bahan) {
                  let v = this.activeProduct.variants.find(x => x.variant_type === 'bahan' && x.variant_name === this.bahan);
                  if (v) price += parseFloat(v.price_modifier);
              }
              if (this.finishing) {
                  let v = this.activeProduct.variants.find(x => x.variant_type === 'finishing' && x.variant_name === this.finishing);
                  if (v) price += parseFloat(v.price_modifier);
              }
              return price;
          },
          get subtotal() {
              return this.unitPrice * this.qty;
          }
      }">

    {{-- Premium Navy Header / Navbar --}}
    <nav class="bg-slate-900/95 backdrop-blur-md text-white sticky top-0 z-50 shadow-md border-b border-white/5 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center gap-4">
                
                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 flex-shrink-0">
                    @if(setting('company_logo'))
                        <img src="{{ setting('company_logo') }}" alt="Logo" class="h-8 w-8 shrink-0 object-contain rounded-lg">
                    @else
                        <div class="w-8 h-8 rounded-lg bg-orange-500 flex items-center justify-center text-white font-bold text-sm shadow-md">F</div>
                    @endif
                    <span class="font-bold text-base tracking-tight text-white">Fadilah <span class="text-orange-400 font-medium">Printing</span></span>
                </a>
                
                {{-- Search Bar di Tengah (Interaktif) --}}
                <div class="flex-1 max-w-lg hidden md:block">
                    <div class="relative">
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder="Cari produk percetakan..." 
                               class="w-full bg-slate-800 border border-slate-700 rounded-full py-1.5 pl-10 pr-4 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors">
                        <svg class="absolute left-3.5 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                
                {{-- Nav links & Account --}}
                <div class="flex items-center gap-4">
                    <a href="#katalog" class="text-xs font-medium text-slate-300 hover:text-white transition-colors hidden lg:block">Katalog</a>
                    <a href="#tentang" class="text-xs font-medium text-slate-300 hover:text-white transition-colors hidden lg:block">Tentang</a>
                    <a href="#faq" class="text-xs font-medium text-slate-300 hover:text-white transition-colors hidden lg:block">Bantuan & FAQ</a>
                    
                    @auth
                        @php $isAdmin = in_array(Auth::user()->role, ['admin', 'owner']); @endphp
                        @if(!$isAdmin)
                            <a href="{{ route('cart.index') }}" class="relative p-1.5 text-slate-300 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                @php $cnt = \App\Models\Cart::getItemCountForUser(Auth::id()); @endphp
                                @if($cnt > 0)
                                    <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-orange-500 text-[9px] font-bold text-white shadow-sm">
                                        {{ $cnt }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        {{-- Notification Dropdown --}}
                        <div class="relative" x-data="{ 
                            open: false, 
                            count: 0,
                            notifications: [],
                            init() {
                                this.fetchData();
                                setInterval(() => this.fetchData(), 30000); // Poll every 30s
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
                            <button @click="open = !open" class="relative p-1.5 text-slate-300 hover:text-white transition-colors focus:outline-none rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span x-show="count > 0" 
                                      class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[8px] font-black text-white"
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
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-2xl border border-slate-200 shadow-xl z-50 overflow-hidden text-slate-800"
                                 x-cloak>
                                
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                                    <span class="text-xs font-bold text-slate-800">Notifikasi</span>
                                    <form action="{{ route('notifications.read_all') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="text-[10px] font-bold text-orange-500 hover:text-orange-600 focus:outline-none">Tandai Semua Dibaca</button>
                                    </form>
                                </div>

                                <div class="max-h-64 overflow-y-auto divide-y divide-slate-100">
                                    <div x-show="notifications.length === 0" class="px-4 py-6 text-center text-xs text-slate-400 font-medium">
                                        Belum ada notifikasi
                                    </div>
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

                                            <div class="min-w-0 text-left">
                                                <p class="text-xs font-bold text-slate-800" x-text="item.title"></p>
                                                <p class="text-[10px] text-slate-500 leading-normal mt-0.5" x-text="item.content"></p>
                                                <span class="text-[9px] text-slate-400 block mt-1" x-text="item.time"></span>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                                <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-200 text-center">
                                    <a href="{{ route('notifications.index') }}" class="text-[10px] font-bold text-slate-600 hover:text-slate-900">Lihat Semua Notifikasi</a>
                                </div>
                            </div>
                        </div>

                        <a href="{{ url('/dashboard') }}" class="text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-300 hover:text-white transition-colors">Masuk</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white rounded-full font-semibold shadow-md">Daftar</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Search Bar (Hanya tampil di mobile) -->
    <div class="bg-slate-800 p-3 md:hidden">
        <div class="relative">
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="Cari produk percetakan..." 
                   class="w-full bg-slate-900 border border-slate-700 rounded-full py-1.5 pl-10 pr-4 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
            <svg class="absolute left-3.5 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>

    <!-- Banner Slider / Hero Section -->
    @if($banners->isNotEmpty())
        <div class="relative bg-slate-950 overflow-hidden" x-data="{ activeSlide: 0, timer: null }" 
             x-init="timer = setInterval(() => { activeSlide = (activeSlide + 1) % {{ $banners->count() }} }, 5000)" 
             @destroy="clearInterval(timer)">
            <div class="relative aspect-[21/9] max-h-[380px] w-full overflow-hidden">
                @foreach($banners as $index => $banner)
                    <div x-show="activeSlide === {{ $index }}" 
                          x-transition:enter="transition ease-out duration-700"
                          x-transition:enter-start="opacity-0 scale-105"
                          x-transition:enter-end="opacity-100 scale-100"
                          x-transition:leave="transition ease-in duration-700"
                          x-transition:leave-start="opacity-100"
                          x-transition:leave-end="opacity-0"
                          class="absolute inset-0 bg-cover bg-center flex items-center"
                          style="background-image: url('{{ $banner->image }}')">
                        <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent"></div>
                        <div class="relative max-w-7xl mx-auto px-8 sm:px-12 text-white space-y-4 w-full">
                            <h2 class="text-xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight max-w-xl">{{ $banner->title }}</h2>
                            @if($banner->link)
                                <a href="{{ $banner->link }}" class="inline-flex btn-sm bg-orange-500 hover:bg-orange-600 text-white rounded-full font-semibold px-5 shadow-glow">Lihat Selengkapnya</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Bullet Indicators -->
            <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2 z-10">
                @foreach($banners as $index => $banner)
                    <button @click="activeSlide = {{ $index }}" class="h-2 rounded-full transition-all duration-300" 
                            :class="activeSlide === {{ $index }} ? 'w-6 bg-orange-500' : 'w-2 bg-white/50'"></button>
                @endforeach
            </div>
        </div>
    @else
        {{-- Fallback Hero Section --}}
        <header class="relative bg-slate-900 text-white overflow-hidden py-16 sm:py-20">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 opacity-95"></div>
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-center text-center">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-slate-200 text-2xs font-semibold tracking-wider uppercase mb-6 border border-white/10">
                    Layanan Percetakan Digital Terbaik
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4 text-white">
                    Kualitas Cetak Premium, <span class="text-orange-400">Proses Cepat & Praktis.</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 max-w-xl mx-auto mb-8 leading-relaxed font-medium">
                    Pesan spanduk, stempel, plakat, dan brosur secara online. Tambahkan ke keranjang, upload file desain Anda pada halaman checkout, dan lakukan pembayaran instan.
                </p>
                <a href="#katalog" class="btn-md bg-orange-500 hover:bg-orange-600 text-white rounded-full px-8 shadow-md">Pilih Produk</a>
            </div>
        </header>
    @endif

    {{-- Main Content & Catalog --}}
    <main id="katalog" class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 w-full space-y-8">
        
        {{-- Title --}}
        <div class="text-center max-w-lg mx-auto space-y-1">
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Katalog Produk Percetakan</h2>
            <p class="text-xs text-slate-500">Pilih produk percetakan berkualitas kami untuk kebutuhan bisnis Anda</p>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg shadow-sm text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg shadow-sm text-xs font-semibold">
                {{ session('error') }}
            </div>
        @endif

        {{-- Kategori Tab & Search Filter Area --}}
        @php
            $categoriesList = $products->pluck('category')->unique('id')->filter();
        @endphp
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
            
            {{-- Tabs --}}
            <div class="flex flex-wrap gap-1">
                <button @click="selectedCategory = 'all'" 
                        :class="selectedCategory === 'all' ? 'bg-orange-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold transition-colors duration-200">
                    Semua
                </button>
                @foreach($categoriesList as $cat)
                    <button @click="selectedCategory = '{{ $cat->id }}'" 
                            :class="selectedCategory === '{{ $cat->id }}' ? 'bg-orange-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-1.5 rounded-full text-xs font-semibold transition-colors duration-200">
                        {{ $cat->category_name }}
                    </button>
                @endforeach
            </div>
            
            {{-- Category Filter Summary --}}
            <div class="text-2xs text-slate-400 font-medium">
                Menyaring produk yang sesuai kebutuhan Anda
            </div>
        </div>

        {{-- Product Grid (Shopee-style Responsive Grid) --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
             
            @forelse ($products as $item)
                {{-- Card Produk --}}
                <div x-show="(selectedCategory === 'all' || selectedCategory === '{{ $item->category_id }}') && 
                             ('{{ strtolower($item->product_name) }}'.includes(searchQuery.toLowerCase()) || 
                              '{{ strtolower($item->description) }}'.includes(searchQuery.toLowerCase()))">
                    <x-product-card :product="$item" />
                </div>
            @empty
                <div class="col-span-full py-12 text-center card bg-white">
                    <div class="empty-state">
                        <div class="empty-state-icon text-slate-300">
                            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xs font-semibold text-slate-800 mt-2">Katalog masih kosong</h3>
                        <p class="text-[11px] text-slate-400">Kembali lagi nanti setelah admin menambahkan produk.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </main>

    <!-- Tentang Kami Section -->
    <section id="tentang" class="py-16 bg-white border-y border-slate-200/60">
        <div class="max-w-4xl mx-auto px-4 text-center space-y-4">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Tentang Kami</h2>
            <div class="h-1 w-12 bg-orange-500 mx-auto rounded-full"></div>
            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed font-medium max-w-2xl mx-auto">{{ $about }}</p>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-16 bg-white border-b border-slate-200/60">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center max-w-lg mx-auto space-y-1">
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Pertanyaan Umum (FAQ)</h2>
                <p class="text-xs text-slate-500">Temukan jawaban cepat atas pertanyaan Anda</p>
                <div class="h-1 w-12 bg-orange-500 mx-auto rounded-full mt-2"></div>
            </div>
            
            <div class="space-y-4">
                @php
                    $fallbackFaq = [
                        [
                            'q' => 'Bagaimana cara melakukan pemesanan?',
                            'a' => 'Pilih produk dari katalog, pilih opsi kustomisasi seperti ukuran, bahan, dan finishing, lalu unggah file desain Anda saat checkout. Setelah checkout selesai, lakukan pembayaran instan via Midtrans Snap.'
                        ],
                        [
                            'q' => 'Berapa lama proses produksi?',
                            'a' => 'Proses produksi standar berkisar antara 1-3 hari kerja tergantung jenis produk dan volume antrian cetak di workshop kami.'
                        ],
                        [
                            'q' => 'Metode pembayaran apa saja yang didukung?',
                            'a' => 'Kami mendukung pembayaran otomatis secara real-time via Midtrans Snap (Virtual Account bank BCA, Mandiri, BNI, BRI, QRIS, GoPay, dan ShopeePay).'
                        ],
                        [
                            'q' => 'Apakah bisa cetak dengan desain sendiri?',
                            'a' => 'Bisa sekali! Anda dapat mengunggah file desain Anda sendiri (format JPG, PNG, PDF, ZIP, RAR) pada form pemesanan sebelum checkout.'
                        ],
                        [
                            'q' => 'Bagaimana cara melacak pesanan saya?',
                            'a' => 'Status pengerjaan dan pengiriman pesanan Anda dapat dilacak secara real-time melalui halaman Dashboard Customer pada tab menu Pesanan.'
                        ],
                        [
                            'q' => 'Bagaimana jika hasil cetak tidak sesuai?',
                            'a' => 'Jika ada kendala dengan hasil cetak, silakan hubungi admin kami secara instan via Live Chat internal website dengan melampirkan foto/video kendala Anda.'
                        ]
                    ];
                    
                    // Gunakan data dari setting jika ada, jika kosong gunakan fallback
                    $faqData = !empty($faq) ? $faq : $fallbackFaq;
                @endphp
                
                @foreach($faqData as $index => $item)
                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs hover:border-slate-300 transition-colors">
                        <button @click="activeFAQ = (activeFAQ === {{ $index }} ? null : {{ $index }})" 
                                class="w-full flex items-center justify-between p-5 text-left font-bold text-xs sm:text-sm text-slate-800 hover:bg-slate-50 transition-colors focus:outline-none">
                            <span>{{ $item['q'] ?? ($item['question'] ?? '') }}</span>
                            <svg class="h-4 w-4 text-slate-500 transform transition-transform duration-300 shrink-0 ml-4" 
                                 :class="activeFAQ === {{ $index }} ? 'rotate-180 text-orange-500' : ''" 
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="activeFAQ === {{ $index }}" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="p-5 pt-0 text-xs text-slate-600 leading-relaxed border-t border-slate-100 bg-slate-50/50"
                             x-cloak>
                            {!! nl2br(e($item['a'] ?? ($item['answer'] ?? ''))) !!}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-white py-12 border-t border-slate-200/60 text-slate-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                {{-- Col 1 --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        @if(setting('company_logo'))
                            <img src="{{ setting('company_logo') }}" alt="Logo" class="h-8 w-8 shrink-0 object-contain rounded-lg">
                        @else
                            <div class="w-8 h-8 rounded-lg bg-orange-500 flex items-center justify-center text-white font-bold text-sm shadow-md">F</div>
                        @endif
                        <span class="font-bold text-sm text-slate-800">Fadilah <span class="text-orange-500">Printing</span></span>
                    </div>
                    <p class="text-2xs text-slate-400 leading-relaxed">
                        Layanan percetakan digital profesional dengan kualitas cetak tinggi, pengerjaan cepat, dan harga yang bersahabat.
                    </p>
                </div>
                
                {{-- Col 2 --}}
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pengiriman & Logistik</h4>
                    <div class="flex flex-wrap gap-2 text-3xs font-semibold text-slate-400">
                        <span class="px-2.5 py-1 rounded bg-slate-100 border border-slate-200">JNE Express</span>
                        <span class="px-2.5 py-1 rounded bg-slate-100 border border-slate-200">J&T Express</span>
                        <span class="px-2.5 py-1 rounded bg-slate-100 border border-slate-200">GO-SEND</span>
                        <span class="px-2.5 py-1 rounded bg-slate-100 border border-slate-200">Ambil Sendiri</span>
                    </div>
                </div>
                
                {{-- Col 3 --}}
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Metode Pembayaran</h4>
                    <div class="flex flex-wrap gap-2 text-3xs font-semibold text-slate-400">
                        <span class="px-2.5 py-1 rounded bg-slate-100 border border-slate-200">Midtrans Gateway</span>
                        <span class="px-2.5 py-1 rounded bg-slate-100 border border-slate-200">QRIS</span>
                        <span class="px-2.5 py-1 rounded bg-slate-100 border border-slate-200">GoPay</span>
                        <span class="px-2.5 py-1 rounded bg-slate-100 border border-slate-200">Bank Transfer</span>
                    </div>
                </div>

            </div>
            
            <div class="border-t border-slate-200 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-3xs text-slate-400 font-medium">
                <p>&copy; {{ date('Y') }} Fadilah Printing. Hak Cipta Dilindungi.</p>
                <div class="flex gap-4">
                    <a href="{{ url('/') }}#katalog" class="hover:underline">Katalog</a>
                    <a href="{{ url('/') }}#tentang" class="hover:underline">Tentang Kami</a>
                    <a href="#faq" class="hover:underline">Bantuan & FAQ</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Interactive Alpine.js Modal Pemesanan (Popup Dialog) --}}
    <div x-show="orderModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
         
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl border border-slate-100 overflow-hidden flex flex-col max-h-[90vh]"
             @click.away="orderModalOpen = false">
             
            {{-- Modal Header --}}
            <div class="bg-slate-900 text-white p-5 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-[10px] uppercase tracking-wider text-orange-400">Pemesanan Cetak</h3>
                    <h2 class="text-sm font-bold truncate max-w-xs mt-0.5" x-text="activeProduct ? activeProduct.product_name : ''"></h2>
                </div>
                <button @click="orderModalOpen = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
            </div>
            
            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto space-y-4 flex-1 no-scrollbar text-xs">
                
                <form action="{{ route('cart.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="product_id" :value="activeProduct ? activeProduct.id : ''">
                    
                    {{-- Opsi Variasi (Dinamis dari Alpine.js) --}}
                    <div class="space-y-3" x-show="activeProduct && activeProduct.variants && activeProduct.variants.length > 0">
                        <h4 class="font-bold text-slate-800 border-b border-slate-100 pb-1">Spesifikasi Cetak</h4>
                        
                        {{-- Variant Ukuran --}}
                        <div x-show="activeProduct && activeProduct.variants.some(v => v.variant_type === 'ukuran')">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Ukuran Cetak</label>
                            <select name="ukuran" x-model="ukuran" class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 focus:border-orange-500 focus:ring-orange-500">
                                <template x-for="v in (activeProduct ? activeProduct.variants.filter(x => x.variant_type === 'ukuran') : [])" :key="v.id">
                                    <option :value="v.variant_name" x-text="v.variant_name + ' (+Rp ' + new Intl.NumberFormat('id-ID').format(v.price_modifier) + ')'"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Variant Bahan --}}
                        <div x-show="activeProduct && activeProduct.variants.some(v => v.variant_type === 'bahan')">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Bahan Cetak</label>
                            <select name="bahan" x-model="bahan" class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 focus:border-orange-500 focus:ring-orange-500">
                                <template x-for="v in (activeProduct ? activeProduct.variants.filter(x => x.variant_type === 'bahan') : [])" :key="v.id">
                                    <option :value="v.variant_name" x-text="v.variant_name + ' (+Rp ' + new Intl.NumberFormat('id-ID').format(v.price_modifier) + ')'"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Variant Finishing --}}
                        <div x-show="activeProduct && activeProduct.variants.some(v => v.variant_type === 'finishing')">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Finishing</label>
                            <select name="finishing" x-model="finishing" class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 focus:border-orange-500 focus:ring-orange-500">
                                <template x-for="v in (activeProduct ? activeProduct.variants.filter(x => x.variant_type === 'finishing') : [])" :key="v.id">
                                    <option :value="v.variant_name" x-text="v.variant_name + ' (+Rp ' + new Intl.NumberFormat('id-ID').format(v.price_modifier) + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Custom Text & Notes --}}
                    <div class="space-y-3">
                        <h4 class="font-bold text-slate-800 border-b border-slate-100 pb-1">Detail Custom</h4>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Teks Kustom (Nama/Tulisan Cetak)</label>
                            <input type="text" name="custom_text" x-model="customText" placeholder="Contoh: Toko Fadilah, Spanduk Promosi..."
                                   class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 focus:border-orange-500 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Catatan Desain</label>
                            <input type="text" name="notes" x-model="notes" placeholder="Misal: Dominasi warna merah, tambah logo wa..."
                                   class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 focus:border-orange-500 focus:ring-orange-500">
                        </div>
                    </div>

                    {{-- Jumlah & Price Calculator --}}
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-700">Jumlah Unit</label>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="qty = Math.max(1, qty - 1)" class="w-8 h-8 rounded-full border border-slate-300 flex items-center justify-center font-bold text-slate-600 active:bg-slate-200">-</button>
                                <input type="number" name="qty" x-model.number="qty" min="1" max="100" class="w-12 border-0 bg-transparent text-center font-bold focus:ring-0">
                                <button type="button" @click="qty = Math.min(100, qty + 1)" class="w-8 h-8 rounded-full border border-slate-300 flex items-center justify-center font-bold text-slate-600 active:bg-slate-200">+</button>
                            </div>
                        </div>
                        
                        <div class="border-t border-slate-200/60 pt-2.5 flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-500">Harga Satuan</span>
                            <span class="font-bold text-slate-800" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(unitPrice)"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm pt-0.5 border-t border-dashed border-slate-200">
                            <span class="font-bold text-slate-700">Subtotal Belanja</span>
                            <span class="font-black text-orange-500" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal)"></span>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex gap-2 justify-end pt-2">
                        <button type="button" @click="orderModalOpen = false" class="btn-sm bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold px-4 py-2">Batal</button>
                        <button type="submit" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold px-6 py-2 shadow-md">
                            Masukkan Keranjang
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
     </div>

    <!-- Floating Live Chat Widget -->
    @if(!auth()->check() || auth()->user()->role === 'customer')
        @include('chat.widget')
    @endif
</body>
</html>