<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $seoTitle ?? 'Bantuan & FAQ - Fadilah Digital Printing' }}</title>
    <meta name="description" content="{{ $seoDescription ?? 'Pusat Bantuan dan FAQ Fadilah Digital Printing.' }}">
    
    {{-- Google Fonts - Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-50 text-surface-900 antialiased min-h-screen flex flex-col font-sans"
      x-data="{ 
          activeTab: 'faq',
          activeFAQ: null
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
                
                {{-- Nav links & Account --}}
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}#katalog" class="text-xs font-medium text-slate-300 hover:text-white transition-colors">Katalog</a>
                    <a href="{{ url('/') }}#tentang" class="text-xs font-medium text-slate-300 hover:text-white transition-colors">Tentang</a>
                    <a href="{{ route('help') }}" class="text-xs font-medium text-white transition-colors">Bantuan</a>
                    
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

    {{-- Hero Header --}}
    <header class="relative bg-slate-900 text-white overflow-hidden py-12">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 opacity-95"></div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-orange-400 text-2xs font-bold tracking-wider uppercase border border-white/10">
                Pusat Bantuan & Layanan Pelanggan
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                Ada yang Bisa Kami Bantu?
            </h1>
            <p class="text-xs text-slate-400 max-w-xl mx-auto leading-relaxed">
                Temukan panduan pemesanan, pembayaran, lacak pesanan, dan jawaban atas pertanyaan yang sering diajukan.
            </p>
        </div>
    </header>

    {{-- Main Content Section --}}
    <main class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex-1 w-full grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Left Sidebar: Tabs --}}
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/60 p-3 shadow-sm flex flex-col space-y-1">
                <button @click="activeTab = 'faq'"
                        :class="activeTab === 'faq' ? 'bg-orange-500 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-50'"
                        class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2.5">
                    <span>❓</span> FAQ & Tanya Jawab
                </button>
                
                <button @click="activeTab = 'pemesanan'"
                        :class="activeTab === 'pemesanan' ? 'bg-orange-500 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-50'"
                        class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2.5">
                    <span>🖨</span> Cara Pemesanan
                </button>
                
                <button @click="activeTab = 'pembayaran'"
                        :class="activeTab === 'pembayaran' ? 'bg-orange-500 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-50'"
                        class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2.5">
                    <span>💳</span> Cara Pembayaran
                </button>
                
                <button @click="activeTab = 'pelacakan'"
                        :class="activeTab === 'pelacakan' ? 'bg-orange-500 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-50'"
                        class="w-full text-left px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2.5">
                    <span>🚚</span> Cara Melacak Pesanan
                </button>
            </div>

            {{-- Live Chat Callout --}}
            <div class="bg-slate-900 text-white rounded-2xl p-5 shadow-sm space-y-4 border border-slate-800">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">💬</span>
                    <h4 class="font-bold text-xs uppercase tracking-wider text-orange-400">Live Chat Web</h4>
                </div>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Website kami memiliki fitur Live Chat mandiri. Butuh komunikasi langsung dengan tim admin percetakan kami secara instan?
                </p>
                @auth
                    <a href="{{ route('chat.index') }}" class="w-full h-9 flex items-center justify-center bg-orange-500 hover:bg-orange-600 text-white rounded-full font-bold text-xs shadow-sm transition active:scale-95 text-center">
                        Mulai Chat Sekarang
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full h-9 flex items-center justify-center bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-full font-bold text-xs shadow-sm transition active:scale-95 text-center">
                        Masuk untuk Chat
                    </a>
                @endauth
            </div>
        </div>

        {{-- Right Content Area --}}
        <div class="lg:col-span-9 bg-white rounded-2xl border border-slate-200/60 p-6 shadow-sm min-h-[300px]">
            
            {{-- Tab 1: FAQ --}}
            <div x-show="activeTab === 'faq'" class="space-y-4">
                <div class="border-b pb-3 mb-2">
                    <h2 class="text-sm font-extrabold text-slate-900">Pertanyaan yang Sering Diajukan (FAQ)</h2>
                    <p class="text-[11px] text-slate-400">Temukan solusi instan atas pertanyaan umum Anda.</p>
                </div>
                
                @if(!empty($faq))
                    <div class="space-y-3">
                        @foreach($faq as $index => $item)
                            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                                <button @click="activeFAQ = (activeFAQ === {{ $index }} ? null : {{ $index }})" 
                                        class="w-full flex items-center justify-between p-4 text-left font-bold text-xs text-slate-800 hover:bg-slate-50 transition-colors">
                                    <span>{{ $item['q'] }}</span>
                                    <svg class="h-3.5 w-3.5 transform transition-transform duration-200" :class="activeFAQ === {{ $index }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="activeFAQ === {{ $index }}" class="p-4 pt-0 text-[11px] text-slate-500 leading-relaxed border-t border-slate-100 bg-slate-50/50">
                                    {{ $item['a'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[11px] text-slate-400 italic">Daftar FAQ belum dikonfigurasi oleh admin.</p>
                @endif
            </div>

            {{-- Tab 2: Cara Pemesanan --}}
            <div x-show="activeTab === 'pemesanan'" class="space-y-4" x-cloak>
                <div class="border-b pb-3 mb-4">
                    <h2 class="text-sm font-extrabold text-slate-900">Langkah Mudah Memesan Cetakan</h2>
                    <p class="text-[11px] text-slate-400">Ikuti panduan berikut untuk melakukan pemesanan secara online.</p>
                </div>
                
                <div class="relative border-l border-slate-200 ml-3.5 pl-6 space-y-6">
                    {{-- Step 1 --}}
                    <div class="relative">
                        <span class="absolute -left-9.5 top-0 flex items-center justify-center w-7 h-7 bg-orange-100 text-orange-600 rounded-full font-bold text-xs border border-orange-200">1</span>
                        <h4 class="font-bold text-xs text-slate-800">Pilih Produk Cetakan</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Buka halaman katalog produk pada Landing Page kami. Kami menyediakan cetak spanduk, brosur, banner, stempel, plakat akrilik, dan lainnya.
                        </p>
                    </div>
                    
                    {{-- Step 2 --}}
                    <div class="relative">
                        <span class="absolute -left-9.5 top-0 flex items-center justify-center w-7 h-7 bg-orange-100 text-orange-600 rounded-full font-bold text-xs border border-orange-200">2</span>
                        <h4 class="font-bold text-xs text-slate-800">Tentukan Pilihan & Kustomisasi</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Pilih kustomisasi seperti pilihan ukuran, bahan, dan finishing. Masukkan juga nama/teks kustom serta catatan instruksi khusus cetak Anda.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="relative">
                        <span class="absolute -left-9.5 top-0 flex items-center justify-center w-7 h-7 bg-orange-100 text-orange-600 rounded-full font-bold text-xs border border-orange-200">3</span>
                        <h4 class="font-bold text-xs text-slate-800">Upload Desain Anda</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Unggah berkas desain Anda (format JPG, PNG, PDF, ZIP, RAR dengan batas file maksimum 10MB) pada formulir pemesanan produk kustom sebelum checkout.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="relative">
                        <span class="absolute -left-9.5 top-0 flex items-center justify-center w-7 h-7 bg-orange-100 text-orange-600 rounded-full font-bold text-xs border border-orange-200">4</span>
                        <h4 class="font-bold text-xs text-slate-800">Checkout & Pembayaran</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
                            Periksa keranjang belanja Anda dan lakukan checkout. Lakukan pembayaran instan melalui gerbang pembayaran Midtrans yang aman.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tab 3: Cara Pembayaran --}}
            <div x-show="activeTab === 'pembayaran'" class="space-y-4" x-cloak>
                <div class="border-b pb-3 mb-4">
                    <h2 class="text-sm font-extrabold text-slate-900">Metode Pembayaran Aman & Instan</h2>
                    <p class="text-[11px] text-slate-400">Pembayaran otomatis diverifikasi secara real-time melalui Midtrans.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-slate-200 p-4 rounded-2xl bg-slate-50/50 space-y-2">
                        <span class="text-lg">🏦</span>
                        <h4 class="font-bold text-xs text-slate-800">Transfer Virtual Account</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Mendukung transfer virtual account (VA) dari berbagai bank utama (BCA, Mandiri, BNI, BRI) yang aktif 24 jam dengan proses verifikasi langsung.
                        </p>
                    </div>

                    <div class="border border-slate-200 p-4 rounded-2xl bg-slate-50/50 space-y-2">
                        <span class="text-lg">📱</span>
                        <h4 class="font-bold text-xs text-slate-800">E-Wallet & QRIS</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Mendukung pembayaran digital e-wallet favorit Anda (GoPay, OVO, ShopeePay) serta Scan QRIS instan dari semua aplikasi e-wallet & mobile banking Indonesia.
                        </p>
                    </div>
                </div>

                <div class="bg-orange-50 border border-orange-100 p-4 rounded-2xl text-[11px] text-orange-800 leading-relaxed">
                    <strong>Catatan Penting:</strong> Anda tidak perlu melakukan konfirmasi transfer atau upload bukti pembayaran manual. Setelah transaksi di Midtrans sukses, sistem kami langsung memperbarui status pembayaran Anda menjadi <strong>Lunas</strong> secara otomatis.
                </div>
            </div>

            {{-- Tab 4: Cara Melacak Pesanan --}}
            <div x-show="activeTab === 'pelacakan'" class="space-y-4" x-cloak>
                <div class="border-b pb-3 mb-4">
                    <h2 class="text-sm font-extrabold text-slate-900">Melacak Proses Produksi & Pengiriman</h2>
                    <p class="text-[11px] text-slate-400">Anda dapat memantau setiap tahap pesanan Anda dengan transparansi penuh.</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs border border-emerald-200 shrink-0">1</div>
                        <div>
                            <h4 class="font-bold text-xs text-slate-800">Buka Halaman Dashboard Saya</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed mt-0.5">
                                Setelah Anda login, masuk ke menu <strong>Dashboard Saya</strong> dan klik tab menu <strong>Pesanan</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs border border-emerald-200 shrink-0">2</div>
                        <div>
                            <h4 class="font-bold text-xs text-slate-800">Lihat Status Pelacakan Cetak</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed mt-0.5">
                                Anda dapat melihat status detail:
                                <br>• <strong>Antrian Cetak</strong>: Pesanan sedang diverifikasi.
                                <br>• <strong>Didesain/Dicetak</strong>: Desain sedang diproses / mesin sedang berproduksi.
                                <br>• <strong>Siap Diambil/Dikirim</strong>: Produk sudah selesai diproduksi.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs border border-emerald-200 shrink-0">3</div>
                        <div>
                            <h4 class="font-bold text-xs text-slate-800">Nomor Resi Pengiriman</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed mt-0.5">
                                Jika Anda memilih metode pengiriman kurir (JNE/J&T), nomor resi kurir akan dimasukkan oleh admin dan tercantum langsung di riwayat pesanan Anda sehingga dapat dilacak di situs web kurir resmi.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-white py-12 border-t border-slate-200/60 text-slate-600 mt-auto">
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
                    <a href="{{ route('help') }}" class="hover:underline">Bantuan & FAQ</a>
                </div>
            </div>
        </div>
    </footer>

    @if(!auth()->check() || auth()->user()->role === 'customer')
        @include('chat.widget')
    @endif
</body>
</html>
