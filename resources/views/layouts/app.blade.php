<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Fadilah Digital Printing</title>
    <meta name="description" content="Sistem Informasi Manajemen Fadilah Digital Printing">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        :root {
            --sb: 260px;
            --sb-c: 76px;
            --tb: 60px;
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style:none; scrollbar-width:none; }
        [x-cloak] { display:none !important; }
        .nav-active { background:#fff7ed; color:#f97316; border:1px solid #fed7aa; }
        .nav-active svg { color:#f97316 !important; }
        .sb-label { font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.09em; color:#94a3b8; padding: 0 8px; }

        /* Modern layout using custom CSS variables */
        body {
            --sidebar-w: var(--sb);
        }
        body.sidebar-collapsed-state {
            --sidebar-w: var(--sb-c);
        }

        /* Default mobile: sidebar is drawer, content full width */
        aside {
            width: var(--sb) !important;
        }
        .main-content-wrapper {
            padding-left: 0 !important;
        }

        /* Desktop layout */
        @media (min-width: 1024px) {
            aside {
                width: var(--sidebar-w) !important;
            }
            .main-content-wrapper {
                padding-left: var(--sidebar-w) !important;
            }
        }

        /* Style page title and subtitle in topbar */
        .page-subtitle { font-size: 11px !important; color: #64748b !important; display: block !important; margin-top: 1px !important; }
        .page-title { font-size: 14px !important; font-weight: 800 !important; color: #1e293b !important; margin: 0 !important; line-height: 1.2 !important; }
        header.bg-gradient-to-r h1, header.bg-gradient-to-r h2, header.bg-gradient-to-r h3,
        header.bg-gradient-to-r p, header.bg-gradient-to-r span, header.bg-gradient-to-r a,
        header.bg-orange-500 h1, header.bg-orange-500 h2, header.bg-orange-500 h3,
        header.bg-orange-500 p, header.bg-orange-500 span, header.bg-orange-500 a {
            color: #ffffff !important;
        }
        header input:focus {
            color: #1e293b !important;
        }
        /* Ensure dropdowns inside header retain dark text */
        header .z-50 p, header .z-50 span, header .z-50 a, header .z-50 div, header .z-50 button {
            color: #1e293b !important;
        }
        header .z-50 p.text-slate-400, header .z-50 span.text-slate-400, header .z-50 p.text-slate-500 {
            color: #94a3b8 !important;
        }
    </style>
</head>
<body class="h-full bg-[#F8FAFC] antialiased"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('sb_col') === 'true',
          toggle() { this.sidebarCollapsed = !this.sidebarCollapsed; localStorage.setItem('sb_col', this.sidebarCollapsed); }
      }"
      :class="sidebarCollapsed ? 'sidebar-collapsed-state' : ''">

<div x-show="sidebarOpen"
     x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"
     class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-sm lg:hidden" x-cloak></div>

{{-- ============ SIDEBAR ============ --}}
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed inset-y-0 left-0 z-40 flex flex-col bg-white border-r border-slate-200
              transition-all duration-300 ease-in-out lg:translate-x-0 overflow-hidden">

    {{-- Logo + Collapse Btn --}}
    <div class="flex shrink-0 items-center border-b border-slate-100 bg-white transition-all duration-300"
         :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-3'"
         style="height:var(--tb)">
        <div class="flex items-center gap-2.5 min-w-0 overflow-hidden"
             x-show="!sidebarCollapsed"
             x-transition:enter="transition-all ease-out duration-200 delay-75"
             x-transition:enter-start="opacity-0 -translate-x-3"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition-all ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-3"
             x-cloak>
            @if(setting('company_logo'))
                <img src="{{ setting('company_logo') }}" alt="Logo" class="h-8 w-8 shrink-0 object-contain rounded-lg">
            @else
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-500 text-white font-black text-sm shadow-sm">F</div>
            @endif
            <div class="min-w-0 overflow-hidden">
                <p class="text-[11px] font-black text-slate-800 truncate leading-none uppercase tracking-wider">Fadilah</p>
                <p class="text-[9px] text-orange-500 mt-0.5 leading-none font-bold uppercase tracking-widest">Digital Printing</p>
            </div>
        </div>
        <button @click="window.innerWidth >= 1024 ? toggle() : (sidebarOpen = false)"
                :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-all duration-200 cursor-pointer">
            <svg x-show="!sidebarCollapsed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
            <svg x-show="sidebarCollapsed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto no-scrollbar py-2 px-2 space-y-0.5">
        @php
            $user = Auth::user();
            $isAdmin = $user ? in_array($user->role ?? '', ['admin', 'owner']) : false;
            $cnt = Auth::check() ? \App\Models\Cart::getItemCountForUser(Auth::id()) : 0;
        @endphp

        @if(!$isAdmin)
        {{-- ── CUSTOMER ── --}}
        <div class="pb-1 pt-0.5" x-show="!sidebarCollapsed" x-transition x-cloak><p class="sb-label">Menu Pelanggan</p></div>
        <div x-show="sidebarCollapsed" class="py-1.5" x-cloak><div class="h-px bg-slate-100"></div></div>

        <a href="{{ route('dashboard') }}" title="Pesanan Saya"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('dashboard') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('dashboard') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>Pesanan Saya</span>
        </a>



        <a href="{{ route('cart.index') }}" title="Keranjang"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('cart.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <span class="relative shrink-0">
                <svg style="width:17px;height:17px" class="{{ request()->routeIs('cart.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                @if($cnt > 0)<span class="absolute -top-1.5 -right-1.5 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-orange-500 text-[8px] font-bold text-white">{{ $cnt }}</span>@endif
            </span>
            <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>Keranjang</span>
        </a>



        <a href="{{ route('profile.edit') }}" title="Profil"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('profile.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('profile.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>Profil Saya</span>
        </a>

        <a href="{{ route('addresses.index') }}" title="Alamat Saya"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('addresses.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('addresses.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>Alamat Saya</span>
        </a>

        <div class="pt-1.5 mt-1 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
                        class="w-full flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold text-red-500 hover:bg-red-50 hover:text-red-700 transition-all duration-150 cursor-pointer">
                    <svg style="width:17px;height:17px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>Logout</span>
                </button>
            </form>
        </div>
        @endif

        @if($isAdmin)
        {{-- ── ADMIN ── --}}
        <a href="{{ route('dashboard') }}" title="Dashboard"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('dashboard') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('dashboard') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Dashboard</span>
        </a>

        <div class="pt-2.5 pb-0.5" x-show="!sidebarCollapsed" x-transition x-cloak><p class="sb-label">Manajemen</p></div>
        <div x-show="sidebarCollapsed" class="py-1.5" x-cloak><div class="h-px bg-slate-100"></div></div>

        <a href="{{ route('produk.index') }}" title="Produk"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('produk.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('produk.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Produk</span>
        </a>

        <a href="{{ route('kategori.index') }}" title="Kategori"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('kategori.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('kategori.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Kategori</span>
        </a>

        <a href="{{ route('stok.index') }}" title="Stok"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('stok.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('stok.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Manajemen Stok</span>
        </a>

        <div class="pt-2.5 pb-0.5" x-show="!sidebarCollapsed" x-transition x-cloak><p class="sb-label">Transaksi</p></div>
        <div x-show="sidebarCollapsed" class="py-1.5" x-cloak><div class="h-px bg-slate-100"></div></div>

        <a href="{{ route('pesanan.index') }}" title="Kelola Pesanan"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('pesanan.*') && !request()->routeIs('pesanan.cetak') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('pesanan.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Kelola Pesanan</span>
        </a>

        <a href="{{ route('admin.shipping.index') }}" title="Pengiriman"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('admin.shipping.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('admin.shipping.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0H4a1 1 0 01-1-1V6a1 1 0 011-1h8a1 1 0 011 1v10a1 1 0 01-1 1H9"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Pengiriman</span>
        </a>

        <a href="{{ route('admin.claims.index') }}" title="Kelola Komplain / Garansi"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('admin.claims.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('admin.claims.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Kelola Komplain</span>
        </a>

        <a href="{{ route('admin.chat') }}" title="Chat Bantuan Customer"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('admin.chat*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('admin.chat*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak class="flex-1 flex items-center justify-between">
                <span>Chat Bantuan CS</span>
                @php
                    $unreadChatCnt = \App\Models\ChatRoom::sum('unread_admin');
                @endphp
                @if($unreadChatCnt > 0)
                    <span class="bg-orange-500 text-white text-[9px] font-extrabold px-1.5 py-0.5 rounded-full leading-none">{{ $unreadChatCnt }}</span>
                @endif
            </span>
        </a>

        <a href="{{ route('admin.shipping_settings.index') }}" title="Pengaturan Pengiriman"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('admin.shipping_settings.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('admin.shipping_settings.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Pengaturan Pengiriman</span>
        </a>

        <a href="{{ route('transaksi.riwayat') }}" title="Riwayat"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('transaksi.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('transaksi.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Riwayat Pesanan</span>
        </a>

        <a href="{{ route('laporan.index') }}" title="Laporan"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('laporan.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('laporan.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Laporan</span>
        </a>

        <div class="pt-2.5 pb-0.5" x-show="!sidebarCollapsed" x-transition x-cloak><p class="sb-label">Situs & SEO</p></div>
        <div x-show="sidebarCollapsed" class="py-1.5" x-cloak><div class="h-px bg-slate-100"></div></div>

        <a href="{{ route('settings.index') }}" title="Settings"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('settings.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('settings.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Settings</span>
        </a>

        <a href="{{ route('banners.index') }}" title="Banner"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('banners.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('banners.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Banner</span>
        </a>

        <a href="{{ route('reviews.admin') }}" title="Feedback & Ulasan"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('reviews.admin') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('reviews.admin') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Feedback & Ulasan</span>
        </a>

        <a href="{{ route('logs.index') }}" title="Log Aktivitas"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('logs.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg style="width:17px;height:17px;flex-shrink:0" class="{{ request()->routeIs('logs.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Log Aktivitas</span>
        </a>

        <a href="{{ route('chat.index') }}" title="Chat Bantuan"
           :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
           class="flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all duration-150 group
                  {{ request()->routeIs('chat.*') ? 'nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <span class="relative shrink-0">
                <svg style="width:17px;height:17px" class="{{ request()->routeIs('chat.*') ? 'text-orange-500' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span id="chat-unread-badge-admin" class="absolute -top-1.5 -right-1.5 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-red-500 text-[8px] font-bold text-white hidden">0</span>
            </span>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Chat Bantuan</span>
        </a>

        <div class="pt-1.5 mt-1 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" :class="sidebarCollapsed ? 'justify-center' : 'px-2.5'"
                        class="w-full flex items-center gap-2.5 py-2 rounded-lg text-[11px] font-semibold text-red-500 hover:bg-red-50 hover:text-red-700 transition-all duration-150 cursor-pointer">
                    <svg style="width:17px;height:17px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition x-cloak>Logout</span>
                </button>
            </form>
        </div>
        @endif
    </nav>

    {{-- Profile Footer --}}
    <div class="shrink-0 border-t border-slate-100 py-2 px-2 bg-white">
        @auth
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" :class="sidebarCollapsed ? 'justify-center' : 'px-2'"
                    class="flex w-full items-center gap-2 rounded-lg py-1.5 text-slate-600 hover:bg-slate-50 transition-all group">
                <div class="h-7 w-7 rounded-lg bg-orange-100 text-orange-600 font-bold uppercase shrink-0 flex items-center justify-center text-[11px]">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="min-w-0 text-left flex-1" x-show="!sidebarCollapsed" x-transition x-cloak>
                    <p class="text-[11px] font-bold text-slate-800 truncate leading-none">{{ Auth::user()->name }}</p>
                    <p class="text-[9px] text-slate-400 mt-0.5 leading-none capitalize">{{ Auth::user()->role ?? 'customer' }}</p>
                </div>
                <svg class="w-3 h-3 shrink-0 text-slate-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''"
                     x-show="!sidebarCollapsed" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 @click.away="open = false" x-cloak
                 class="absolute bottom-full left-0 right-0 mb-1.5 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-50 origin-bottom">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profil Saya
                </a>
                <a href="{{ url('/') }}" class="flex items-center gap-2 px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Lihat Katalog
                </a>
            </div>
        </div>
        @else
        <a href="{{ route('login') }}" :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
           class="flex w-full items-center justify-center gap-2 rounded-lg py-2 bg-orange-500 text-white font-semibold text-[11px] hover:bg-orange-600 transition-all">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition x-cloak>Masuk / Daftar</span>
        </a>
        @endauth
    </div>
</aside>

{{-- ============ MAIN ============ --}}
<div class="main-content-wrapper flex flex-col min-h-screen transition-all duration-300 ease-in-out">

    {{-- Topbar --}}
    <header class="sticky top-0 z-20 flex shrink-0 items-center justify-between
                    {{ $isAdmin ? 'bg-gradient-to-r from-orange-500/90 via-orange-500/85 to-amber-500/90 backdrop-blur-md border-b border-orange-400/40 text-white shadow-2xs' : 'border-b border-slate-200 bg-white/95 backdrop-blur-md text-slate-800' }} px-4 sm:px-5 gap-3"
            style="height:var(--tb)">
        
        {{-- Left Topbar Area: Hamburger + Breadcrumbs + Page Title --}}
        <div class="flex items-center gap-3 min-w-0 flex-1">
            {{-- Mobile hamburger --}}
            <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden w-8 h-8 flex items-center justify-center rounded-lg {{ $isAdmin ? 'text-white hover:bg-orange-600' : 'text-slate-500 hover:bg-slate-100' }} transition-colors cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Breadcrumbs --}}
            @php
                $segments = request()->segments();
                $breadcrumbs = [];
                $currUrl = '';
                foreach ($segments as $segment) {
                    $currUrl .= '/' . $segment;
                    $breadcrumbs[] = [
                        'name' => ucwords(str_replace(['-', '_'], ' ', $segment)),
                        'url' => $currUrl
                    ];
                }
            @endphp
            <div class="hidden sm:flex items-center gap-1 text-[10px] {{ $isAdmin ? 'text-white' : 'text-slate-400' }} font-medium shrink-0">
                <a href="{{ url('/') }}" class="{{ $isAdmin ? 'text-white/90 hover:text-white' : 'hover:text-slate-600' }}">Home</a>
                @foreach($breadcrumbs as $bc)
                    <svg class="w-2.5 h-2.5 {{ $isAdmin ? 'text-white/70' : 'text-slate-350' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                    @if($loop->last)
                        <span class="{{ $isAdmin ? 'text-white font-extrabold' : 'text-slate-600 font-semibold' }} truncate max-w-[150px]">{{ $bc['name'] }}</span>
                    @else
                        <a href="{{ $bc['url'] }}" class="{{ $isAdmin ? 'text-white/90 hover:text-white font-medium' : 'hover:text-slate-600' }} truncate max-w-[150px]">{{ $bc['name'] }}</a>
                    @endif
                @endforeach
            </div>

            {{-- Separator if breadcrumb is visible --}}
            <div class="hidden sm:block h-3.5 w-px {{ $isAdmin ? 'bg-white/30' : 'bg-slate-200' }} shrink-0"></div>

            {{-- Page title slot (single line, title-only via CSS display rule on subtitles) --}}
            <div class="min-w-0 truncate text-white">
                {{ $header ?? '' }}
            </div>
        </div>

        {{-- Right Topbar Area: Search, Notification, Cart (optional), Avatar --}}
        <div class="flex items-center gap-3 shrink-0">
            {{-- Search box: 40px height --}}
            <div class="hidden md:flex items-center relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 {{ $isAdmin ? 'text-white/70' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" placeholder="Cari..."
                       class="w-40 xl:w-48 {{ $isAdmin ? 'bg-white/15 border-white/25 text-white placeholder-white/70 focus:bg-white focus:text-slate-800 focus:placeholder-slate-400' : 'bg-slate-50 border border-slate-200 text-slate-700 placeholder-slate-400 focus:border-orange-400 focus:ring-orange-400' }} rounded-lg h-9 pl-9 pr-3 text-[11px] focus:outline-none transition-colors">
            </div>

            {{-- Interactive Notification Bell & Dropdown --}}
            <div class="relative" x-data="{ 
                open: false, 
                count: 0,
                notifications: [],
                init() {
                    this.fetchData();
                    setInterval(() => this.fetchData(), 15000); // Poll every 15s
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
                <button @click="open = !open" class="relative w-9 h-9 flex items-center justify-center rounded-xl {{ $isAdmin ? 'bg-white/15 hover:bg-white/25 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }} transition-colors focus:outline-none cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    
                    {{-- Pulse animation indicator when unread exists --}}
                    <span x-show="count > 0" class="absolute -top-1 -right-1 flex h-5 w-5 pointer-events-none" x-cloak>
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-5 min-w-[20px] px-1 bg-red-600 text-white font-black text-[10px] items-center justify-center border-2 border-white shadow-md"
                              x-text="count"></span>
                    </span>
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
                                    <p class="text-[10px] text-slate-500 mt-0.5 leading-relaxed" x-text="item.content"></p>
                                    <p class="text-[9px] text-slate-400 mt-0.5 font-semibold uppercase tracking-wider" x-text="item.time"></p>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            @if(!$isAdmin)
            {{-- Cart --}}
            <a href="{{ route('cart.index') }}" class="relative w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                @if($cnt > 0)
                    <span class="absolute -top-0.5 -right-0.5 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-orange-500 text-[8px] font-bold text-white">{{ $cnt }}</span>
                @endif
            </a>
            @endif

            @auth
            <div x-data="{ topOpen: false }" class="relative">
                <button @click="topOpen = !topOpen"
                        class="h-8 w-8 rounded-full overflow-hidden border border-slate-200 bg-orange-50 hover:opacity-90 transition-opacity flex items-center justify-center cursor-pointer">
                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                </button>
                <div x-show="topOpen"
                     x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                     @click.away="topOpen = false" x-cloak
                     class="absolute right-0 mt-1.5 w-52 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-50 origin-top-right">
                    <div class="px-3 py-2 border-b border-slate-100">
                        <p class="text-[11px] font-bold text-slate-800 leading-none truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[9px] text-slate-400 mt-0.5 leading-none truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil Saya
                    </a>
                    <a href="{{ url('/') }}" class="flex items-center gap-2 px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Katalog Produk
                    </a>
                    <div class="border-t border-slate-100 my-0.5"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-[11px] text-red-500 hover:bg-red-50 transition-colors cursor-pointer font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg bg-orange-500 text-white font-semibold text-[11px] hover:bg-orange-600 transition-all shadow-sm">
                Masuk
            </a>
            @endauth
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 p-4 sm:p-5">
        <div class="max-w-screen-2xl mx-auto">
            @if(isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </div>
    </main>

    <footer class="border-t border-slate-200 px-5 py-2.5 bg-white">
        <p class="text-[10px] text-slate-400 text-center">&copy; {{ date('Y') }} Fadilah Digital Printing &mdash; Sistem Informasi Manajemen</p>
    </footer>
</div>

@if(!auth()->check() || auth()->user()->role === 'customer')
    @include('chat.widget')
@endif

@stack('scripts')
@auth
<script>
document.addEventListener('DOMContentLoaded', function () {
    let lastUnread = 0;
    const cBadge = document.getElementById('chat-unread-badge-customer');
    const aBadge = document.getElementById('chat-unread-badge-admin');
    let poll;

    function beep() {
        try {
            let ctx = new (window.AudioContext || window.webkitAudioContext)();
            let o = ctx.createOscillator(), g = ctx.createGain();
            o.connect(g); g.connect(ctx.destination);
            o.type='sine'; o.frequency.setValueAtTime(880, ctx.currentTime);
            g.gain.setValueAtTime(.3,ctx.currentTime); g.gain.exponentialRampToValueAtTime(.01,ctx.currentTime+.15);
            o.start(ctx.currentTime); o.stop(ctx.currentTime+.15);
        } catch(e){}
    }

    if (window.Notification && Notification.permission === 'default') Notification.requestPermission();

    function fetchUnread() {
        fetch('{{ route('chat.unread_badge') }}', {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
        .then(r => { if(r.status===401){clearInterval(poll);return null;} return r.json(); })
        .then(d => {
            if(!d) return;
            const c = d.unread||0;
            [cBadge, aBadge].forEach(b => { if(!b) return; if(c>0){b.textContent=c;b.classList.remove('hidden');}else{b.classList.add('hidden');} });
            if(c>lastUnread){ beep(); if(window.Notification&&Notification.permission==='granted') new Notification('Fadilah Printing Chat',{body:'Pesan baru masuk!'}); }
            lastUnread=c;
        }).catch(e=>console.error(e));
    }

    fetchUnread();
    poll = setInterval(fetchUnread, 5000);
});
</script>
@endauth
</body>
</html>
