@php
   // Pre-process data untuk JavaScript — hindari @json(map(...)) yang menyebabkan ParseError
   $recentOrdersJson = $recentOrdersList->map(function ($o) {
       return [
           'id'              => $o->id,
           'invoice'         => $o->invoice_number,
           'customer'        => $o->user?->name ?? 'Tamu',
           'customer_id'     => $o->user_id,
           'date'            => $o->created_at->format('d M Y H:i'),
           'status'          => $o->order_status instanceof \App\Enums\OrderStatus ? $o->order_status->value : $o->order_status,
           'status_label'    => $o->order_status_label,
           'status_badge'    => $o->order_status_badge_class,
           'total'           => $o->total_price,
           'total_formatted' => 'Rp' . number_format($o->total_price, 0, ',', '.'),
       ];
   })->values()->toArray();

   $logsJson = $activityLogsList->map(function ($l) {
       return [
           'id'          => $l->id,
           'user'        => $l->user?->name ?? 'Sistem',
           'activity'    => $l->activity,
           'description' => $l->description,
           'date'        => $l->created_at->format('d M Y H:i'),
           'ip'          => $l->ip_address,
       ];
   })->values()->toArray();

   $totalRevenueFormatted = 'Rp' . number_format($totalRevenue, 0, ',', '.');
@endphp
<x-app-layout>
   <x-slot name="header">
       <div>
           <h1 class="page-title">Dashboard Utama</h1>
           <p class="page-subtitle text-slate-500">Selamat datang, <span class="font-semibold text-slate-700">{{ Auth::user()->name }}</span> &bull; Panel kontrol BI &amp; Analisis real-time</p>
       </div>
   </x-slot>

   {{-- Root wrapper Alpine.js --}}
   <div x-data="adminDashboard()" x-init="initDashboard()" class="space-y-6 animate-fade-in text-xs">
       
       {{-- ==================== FILTER BAR & LIVE STATUS ==================== --}}
       <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex flex-wrap items-center justify-between gap-4">
           <div class="flex items-center gap-3">
               <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Filter Waktu:</span>
               <select x-model="filter" @change="fetchData()" class="text-xs border border-slate-200 rounded-xl px-3 py-1.5 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 bg-white font-bold text-slate-700">
                   <option value="today">Hari Ini</option>
                   <option value="7days">7 Hari Terakhir</option>
                   <option value="30days">30 Hari Terakhir</option>
                   <option value="month">Bulan Ini</option>
                   <option value="year">Tahun Ini</option>
                   <option value="custom">Kustom Tanggal</option>
               </select>
               
               {{-- Custom Date inputs --}}
               <div x-show="filter === 'custom'" class="flex items-center gap-2 animate-fade-in" x-cloak>
                   <input type="date" x-model="startDate" @change="fetchData()" class="text-xs border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700">
                   <span class="text-slate-400 font-bold">s/d</span>
                   <input type="date" x-model="endDate" @change="fetchData()" class="text-xs border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700">
               </div>
           </div>

           <div class="flex items-center gap-2.5">
               <span class="flex h-2 w-2 relative">
                   <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                   <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
               </span>
               <span class="text-[9px] font-black uppercase text-emerald-600 tracking-widest bg-emerald-50 border border-emerald-200/50 px-2.5 py-0.5 rounded-full">
                   Live Polling: <span x-text="countdown">30</span>s
               </span>
               <button @click="fetchData(true)" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-700 transition-colors" title="Refresh Sekarang">
                   <svg class="w-4 h-4" :class="loading ? 'animate-spin text-orange-500' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5"/></svg>
               </button>
           </div>
       </div>

       {{-- ==================== KPI CARDS GRID ==================== --}}
       {{-- ==================== BUSINESS OVERVIEW KPI CARDS ==================== --}}
       <div>
           <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Ringkasan Bisnis</span>
           <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
               {{-- KPI: Total Orders --}}
               <div @click="window.location.href = '/pesanan'" 
                    class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-24 cursor-pointer group relative overflow-hidden">
                   <div class="flex justify-between items-start">
                       <span class="text-[8px] font-bold text-slate-450 uppercase tracking-wider">Total Orders</span>
                       <div class="p-1 rounded bg-slate-50 text-slate-500 group-hover:bg-orange-50 group-hover:text-orange-500 transition-colors">
                           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                       </div>
                   </div>
                   <span class="text-xl font-black text-slate-800 tracking-tight" x-text="kpi.total_orders">0</span>
                   <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider group-hover:text-orange-500">Kelola Pesanan &rarr;</span>
               </div>

               {{-- KPI: Pending (Belum Bayar) --}}
               <div @click="window.location.href = '/pesanan?status=pending'" 
                    class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-24 cursor-pointer group relative overflow-hidden">
                   <div class="flex justify-between items-start">
                       <span class="text-[8px] font-bold text-slate-455 uppercase tracking-wider">Menunggu Bayar</span>
                       <div class="p-1 rounded bg-yellow-50 text-yellow-500 group-hover:bg-yellow-100 transition-colors">
                           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                       </div>
                   </div>
                   <span class="text-xl font-black text-slate-800 tracking-tight" x-text="kpi.pending">0</span>
                   <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider group-hover:text-yellow-600">Filter Pending &rarr;</span>
               </div>

               {{-- KPI: Total Customer --}}
               <div @click="window.location.href = '/activity-logs'" 
                    class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-24 cursor-pointer group relative overflow-hidden">
                   <div class="flex justify-between items-start">
                       <span class="text-[8px] font-bold text-slate-450 uppercase tracking-wider">Total Pelanggan</span>
                       <div class="p-1 rounded bg-purple-50 text-purple-500 group-hover:bg-purple-100 transition-colors">
                           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                       </div>
                   </div>
                   <span class="text-xl font-black text-slate-800 tracking-tight" x-text="kpi.total_customers">0</span>
                   <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider group-hover:text-purple-600">Lihat Logs &rarr;</span>
               </div>

               {{-- KPI: Total Pendapatan --}}
               <div @click="window.location.href = '/laporan-penjualan'" 
                    class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-24 cursor-pointer group relative overflow-hidden border-r-4 border-r-orange-500">
                   <div class="flex justify-between items-start">
                       <span class="text-[8px] font-bold text-slate-450 uppercase tracking-wider">Total Pendapatan</span>
                       <div class="p-1 rounded bg-orange-500 text-white shadow-3xs">
                           Rp
                       </div>
                   </div>
                   <span class="text-lg font-black text-orange-500 tracking-tight truncate" x-text="kpi.total_revenue_formatted">Rp0</span>
                   <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider group-hover:text-orange-600">Laporan Penjualan &rarr;</span>
               </div>
           </div>
       </div>

       {{-- ==================== WORKFLOW PIPELINE KPI CARDS ==================== --}}
       <div>
           <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Alur Kerja Percetakan</span>
           <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
               {{-- Stage 1: Diproses --}}
               <div @click="window.location.href = '/pesanan'" 
                    class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-24 cursor-pointer group relative overflow-hidden">
                   <div class="flex justify-between items-start">
                       <span class="text-[8px] font-bold text-orange-650 uppercase tracking-wider">1. Diproses</span>
                       <div class="p-1 rounded bg-orange-50 text-orange-500">
                           ⚙️
                       </div>
                   </div>
                   <span class="text-xl font-black text-slate-800 tracking-tight" x-text="kpi.processing">0</span>
                   <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Antrian &rarr;</span>
               </div>

               {{-- Stage 2: Sedang Dicetak --}}
               <div @click="window.location.href = '/pesanan'" 
                    class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-24 cursor-pointer group relative overflow-hidden">
                   <div class="flex justify-between items-start">
                       <span class="text-[8px] font-bold text-blue-600 uppercase tracking-wider">2. Dicetak</span>
                       <div class="p-1 rounded bg-blue-50 text-blue-500">
                           🖨️
                       </div>
                   </div>
                   <span class="text-xl font-black text-slate-800 tracking-tight" x-text="kpi.sedang_dicetak">0</span>
                   <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Sedang Dicetak &rarr;</span>
               </div>

               {{-- Stage 3: Dikemas --}}
               <div @click="window.location.href = '/pesanan'" 
                    class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-24 cursor-pointer group relative overflow-hidden">
                   <div class="flex justify-between items-start">
                       <span class="text-[8px] font-bold text-purple-650 uppercase tracking-wider">3. Dikemas</span>
                       <div class="p-1 rounded bg-purple-50 text-purple-500">
                           📦
                       </div>
                   </div>
                   <span class="text-xl font-black text-slate-800 tracking-tight" x-text="kpi.dikemas">0</span>
                   <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Siap Kirim &rarr;</span>
               </div>

               {{-- Stage 4: Dikirim --}}
               <div @click="window.location.href = '/pesanan'" 
                    class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-24 cursor-pointer group relative overflow-hidden">
                   <div class="flex justify-between items-start">
                       <span class="text-[8px] font-bold text-cyan-600 uppercase tracking-wider">4. Dikirim</span>
                       <div class="p-1 rounded bg-cyan-50 text-cyan-500">
                           🚚
                       </div>
                   </div>
                   <span class="text-xl font-black text-slate-800 tracking-tight" x-text="kpi.dikirim">0</span>
                   <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Sedang Dikirim &rarr;</span>
               </div>

               {{-- Stage 5: Selesai --}}
               <div @click="window.location.href = '/pesanan'" 
                    class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-24 cursor-pointer group relative overflow-hidden">
                   <div class="flex justify-between items-start">
                       <span class="text-[8px] font-bold text-green-600 uppercase tracking-wider">5. Selesai</span>
                       <div class="p-1 rounded bg-green-50 text-green-500">
                           🏁
                       </div>
                   </div>
                   <span class="text-xl font-black text-slate-800 tracking-tight" x-text="kpi.done">0</span>
                   <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Selesai &rarr;</span>
               </div>
           </div>
       </div>

       {{-- ==================== 6 GRAFIK INTERAKTIF GRID ==================== --}}
       <div class="space-y-6">
           
           {{-- Row 1: Pendapatan & Status Order --}}
           <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
               {{-- Chart 1: Pendapatan Bulanan --}}
               <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between">
                   <div>
                       <h3 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider mb-0.5">📈 Grafik Pendapatan Bulanan (Line Chart)</h3>
                       <p class="text-[10px] text-slate-400 font-semibold mb-4">Klik titik data grafik untuk langsung membuka laporan bulan tersebut</p>
                   </div>
                   <div class="relative h-72">
                       <canvas id="revenueChart"></canvas>
                   </div>
               </div>

               {{-- Chart 2: Status Pesanan --}}
               <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between">
                   <div>
                       <h3 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider mb-0.5">📦 Grafik Status Pesanan (Doughnut Chart)</h3>
                       <p class="text-[10px] text-slate-400 font-semibold mb-4">Klik segmen status untuk memfilter pesanan berdasarkan status tersebut</p>
                   </div>
                   <div class="relative h-72">
                       <canvas id="orderStatusChart"></canvas>
                   </div>
               </div>
           </div>

           {{-- Row 2: Order Bulanan, Produk Terlaris, Customer Baru, Kategori --}}
           <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
               {{-- Chart 3: Order Bulanan (Bar Chart) --}}
               <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between">
                   <div>
                       <h3 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider mb-0.5">📊 Grafik Order Bulanan</h3>
                       <p class="text-[10px] text-slate-400 font-semibold mb-4">Klik batang untuk menampilkan daftar order bulan itu</p>
                   </div>
                   <div class="relative h-64">
                       <canvas id="salesVolumeChart"></canvas>
                   </div>
               </div>

               {{-- Chart 4: Produk Terlaris (Pie Chart) --}}
               <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between">
                   <div>
                       <h3 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider mb-0.5">🥧 Pie Chart Produk Terlaris</h3>
                       <p class="text-[10px] text-slate-400 font-semibold mb-4">Klik segmen produk untuk membuka detail produk tersebut</p>
                   </div>
                   <div class="relative h-64">
                       <canvas id="topProductsPieChart"></canvas>
                   </div>
               </div>

               {{-- Chart 5: Customer Baru (Line Chart) --}}
               <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between">
                   <div>
                       <h3 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider mb-0.5">📈 Grafik Customer Baru</h3>
                       <p class="text-[10px] text-slate-400 font-semibold mb-4">Klik titik bulan untuk melihat logs registrasi bulan tersebut</p>
                   </div>
                   <div class="relative h-64">
                       <canvas id="customerGrowthChart"></canvas>
                   </div>
               </div>

               {{-- Chart 6: Penjualan per Kategori (Horizontal Bar Chart) --}}
               <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between">
                   <div>
                       <h3 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider mb-0.5">📊 Penjualan per Kategori</h3>
                       <p class="text-[10px] text-slate-400 font-semibold mb-4">Klik kategori untuk membuka daftar produk kategori itu</p>
                   </div>
                   <div class="relative h-64">
                       <canvas id="categorySalesChart"></canvas>
                   </div>
               </div>
           </div>

       </div>

       {{-- ==================== QUICK ACTION PANEL ==================== --}}
       <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
           <h3 class="font-black text-slate-800 text-xs uppercase tracking-wider border-b border-slate-100 pb-3">⚡ Quick Action Panel</h3>
           <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
               <a href="{{ route('produk.index') }}#tambah" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-150 hover:bg-orange-50 hover:text-orange-500 hover:border-orange-200 transition-all font-bold text-center gap-1.5">
                   <span>📦</span> Tambah Produk
               </a>
               <a href="{{ route('kategori.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-150 hover:bg-orange-50 hover:text-orange-500 hover:border-orange-200 transition-all font-bold text-center gap-1.5">
                   <span>🏷️</span> Tambah Kategori
               </a>
               <a href="{{ route('banners.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-150 hover:bg-orange-50 hover:text-orange-500 hover:border-orange-200 transition-all font-bold text-center gap-1.5">
                   <span>🖼️</span> Tambah Banner
               </a>
               <a href="{{ route('pesanan.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-150 hover:bg-orange-50 hover:text-orange-500 hover:border-orange-200 transition-all font-bold text-center gap-1.5">
                   <span>🚚</span> Kelola Pesanan
               </a>
               <a href="{{ route('laporan.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-150 hover:bg-orange-50 hover:text-orange-500 hover:border-orange-200 transition-all font-bold text-center gap-1.5">
                   <span>📊</span> Laporan
               </a>
               <a href="{{ route('settings.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-150 hover:bg-orange-50 hover:text-orange-500 hover:border-orange-200 transition-all font-bold text-center gap-1.5">
                   <span>⚙️</span> CMS Settings
               </a>
               <a href="{{ route('logs.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-150 hover:bg-orange-50 hover:text-orange-500 hover:border-orange-200 transition-all font-bold text-center gap-1.5">
                   <span>📜</span> Log Aktivitas
               </a>
           </div>
       </div>

       {{-- ==================== BOTTOM PANELS: RECENT ORDERS & ACTIVITY LOGS ==================== --}}
       <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
           
           {{-- Panel Left: Recent Orders --}}
           <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
               <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                   <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">📦 Recent Orders</h3>
                   <a href="{{ route('pesanan.index') }}" class="text-[9px] font-black text-orange-500 uppercase tracking-wider hover:underline">Semua Pesanan &rarr;</a>
               </div>
               <div class="overflow-x-auto flex-1">
                   <table class="w-full text-left text-[11px]">
                       <thead class="bg-slate-50 text-[9px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                           <tr>
                               <th class="px-4 py-2.5">Invoice</th>
                               <th class="px-4 py-2.5">Customer</th>
                               <th class="px-4 py-2.5 text-center">Status</th>
                               <th class="px-4 py-2.5 text-right">Total</th>
                           </tr>
                       </thead>
                       <tbody class="divide-y divide-slate-100 bg-white">
                           <template x-for="o in recentOrders" :key="o.id">
                               <tr class="hover:bg-slate-50/40 transition-colors">
                                   <td class="px-4 py-3 font-bold text-slate-800">
                                       <a :href="'/pesanan?search=' + o.invoice" class="hover:text-orange-500 hover:underline" x-text="'#' + o.invoice"></a>
                                   </td>
                                   <td class="px-4 py-3">
                                       <a :href="'/pesanan?search=' + encodeURIComponent(o.customer)" class="font-semibold text-slate-600 hover:text-orange-500 hover:underline" x-text="o.customer"></a>
                                   </td>
                                   <td class="px-4 py-3 text-center">
                                       <span :class="o.status_badge" class="inline-flex px-2 py-0.5 rounded-full text-[8px] font-black uppercase border border-current" x-text="o.status_label"></span>
                                   </td>
                                   <td class="px-4 py-3 text-right font-black text-slate-800">
                                       <a :href="'/pesanan?search=' + o.invoice" class="hover:text-orange-500 hover:underline" x-text="o.total_formatted"></a>
                                   </td>
                               </tr>
                           </template>
                       </tbody>
                   </table>
               </div>
           </div>

           {{-- Panel Right: Live Activity Logs --}}
           <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
               <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                   <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">📜 Activity Logs (Real-time)</h3>
                   <a href="{{ route('logs.index') }}" class="text-[9px] font-black text-orange-500 uppercase tracking-wider hover:underline">Semua Log &rarr;</a>
               </div>
               <div class="overflow-x-auto flex-1">
                   <table class="w-full text-left text-[11px]">
                       <thead class="bg-slate-50 text-[9px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                           <tr>
                               <th class="px-4 py-2.5">User</th>
                               <th class="px-4 py-2.5">Aktivitas</th>
                               <th class="px-4 py-2.5">Deskripsi</th>
                               <th class="px-4 py-2.5">Waktu</th>
                           </tr>
                       </thead>
                       <tbody class="divide-y divide-slate-100 bg-white">
                           <template x-for="l in logs" :key="l.id">
                               <tr class="hover:bg-slate-50/40 transition-colors">
                                   <td class="px-4 py-3 font-bold text-slate-800">
                                       <a :href="'/activity-logs?search=' + encodeURIComponent(l.user)" class="hover:text-orange-500 hover:underline" x-text="l.user"></a>
                                   </td>
                                   <td class="px-4 py-3 text-slate-500 font-semibold" x-text="l.activity"></td>
                                   <td class="px-4 py-3 text-slate-500 italic max-w-[150px] truncate" :title="l.description">
                                       <a :href="'/activity-logs?search=' + encodeURIComponent(l.description)" class="hover:text-orange-500 hover:underline" x-text="l.description"></a>
                                   </td>
                                   <td class="px-4 py-3 text-slate-400 font-medium" x-text="l.date"></td>
                               </tr>
                           </template>
                       </tbody>
                   </table>
               </div>
           </div>

       </div>

       {{-- Critical Products Table --}}
       <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
           <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2 bg-slate-50/50">
               <div class="flex items-center gap-2.5">
                   <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                   <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Produk Perlu Restock</h3>
                   @if($criticalProducts->count() > 0)
                       <span class="badge-danger bg-rose-50 text-rose-600 border border-rose-200 px-2.5 py-0.5 text-[9px] rounded-full font-black uppercase tracking-wider">{{ $criticalProducts->count() }} produk</span>
                   @endif
               </div>
               <a href="{{ route('stok.index') }}" class="inline-flex items-center gap-1.5 btn-xs bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-xl px-3.5 py-1.5 font-bold">
                   <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                   Kelola Stok
               </a>
           </div>
           
           <div class="overflow-x-auto">
               <table class="w-full text-xs text-left">
                   <thead class="bg-slate-50 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                       <tr>
                           <th class="px-5 py-3">Produk</th>
                           <th class="px-5 py-3">Kategori</th>
                           <th class="px-5 py-3 text-center">Stok</th>
                           <th class="px-5 py-3 text-center">Status</th>
                           <th class="px-5 py-3 text-center">Aksi</th>
                       </tr>
                   </thead>
                   <tbody class="divide-y divide-slate-100 bg-white">
                       @forelse($criticalProducts as $prod)
                           <tr class="hover:bg-slate-50/40">
                               <td class="px-5 py-3.5 font-bold text-slate-800">
                                   <a href="{{ route('produk.show', $prod->id) }}" class="hover:text-orange-500 hover:underline">{{ $prod->product_name }}</a>
                               </td>
                               <td class="px-5 py-3.5 text-slate-500">{{ $prod->category->category_name ?? '—' }}</td>
                               <td class="px-5 py-3.5 text-center">
                                   <span class="font-extrabold {{ $prod->stock == 0 ? 'text-rose-600' : 'text-amber-500' }}">
                                       {{ $prod->stock }} unit
                                   </span>
                               </td>
                               <td class="px-5 py-3.5 text-center">
                                   @if($prod->stock == 0)
                                       <span class="inline-flex px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-200 text-[8px] font-black uppercase">Habis</span>
                                   @else
                                       <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 border border-amber-200 text-[8px] font-black uppercase">Menipis</span>
                                   @endif
                               </td>
                               <td class="px-5 py-3.5 text-center">
                                   <a href="{{ route('stok.index') }}" class="inline-flex btn-xs bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-lg px-2.5 py-1 text-[9px] uppercase tracking-wider">
                                       Restock
                                   </a>
                               </td>
                           </tr>
                       @empty
                           <tr>
                               <td colspan="5" class="p-8 text-center text-slate-400">
                                   <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-2 border border-emerald-100 shadow-3xs">
                                       ✓
                                   </div>
                                   <p class="text-xs font-bold text-slate-800">Semua Stok Aman!</p>
                                   <p class="text-[10px] mt-0.5">Seluruh produk memiliki persediaan di atas 10 unit.</p>
                               </td>
                           </tr>
                       @endforelse
                   </tbody>
               </table>
           </div>
       </div>

   </div>

   @push('scripts')
       <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
       <script>
           function adminDashboard() {
               return {
                   filter: 'month',
                   startDate: '',
                   endDate: '',
                   countdown: 30,
                   loading: false,
                   countdownInterval: null,
                   
                   kpi: {
                       pending: {{ $pendingCount }},
                       processing: {{ $prosesCount }},
                       sedang_dicetak: {{ $sedangDicetakCount }},
                       dikemas: {{ $dikemasCount }},
                       dikirim: {{ $dikirimCount }},
                       done: {{ $selesaiCount }},
                       total_orders: {{ $totalOrdersCount }},
                       total_customers: {{ $totalCustomersCount }},
                       total_revenue: {{ $totalRevenue }},
                       total_revenue_formatted: @json($totalRevenueFormatted),
                       total_products: {{ $totalProduk }},
                       stock_menipis: {{ $stokMenipis }},
                       stock_habis: {{ $stokHabis }}
                   },
                   
                   recentOrders: @json($recentOrdersJson),

                   logs: @json($logsJson),

                   charts: {},

                   initDashboard() {
                       this.initCharts();
                       
                       // Setup live polling 30-60 detik
                       this.countdownInterval = setInterval(() => {
                           if (this.countdown <= 1) {
                               this.fetchData(true);
                           } else {
                               this.countdown--;
                           }
                       }, 1000);
                   },

                   initCharts() {
                       const brandOrange = '#F97316';
                       const colorSlate = '#64748B';
                       const colorEmerald = '#10B981';
                       const colorAmber = '#F59E0B';
                       const colorRose = '#EF4444';

                       const globalOptions = {
                           responsive: true,
                           maintainAspectRatio: false,
                           plugins: {
                               legend: {
                                   labels: { font: { family: 'Inter', size: 9, weight: 'bold' }, boxWidth: 10 }
                               },
                               tooltip: {
                                   bodyFont: { family: 'Inter', size: 10 },
                                   titleFont: { family: 'Inter', size: 10, weight: 'bold' }
                               }
                           }
                       };

                       // 1. Line Chart: Pendapatan Bulanan
                       const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
                       this.charts.revenue = new Chart(ctxRevenue, {
                           type: 'line',
                           data: {
                               labels: @json($chartMonths),
                               datasets: [{
                                   label: 'Pendapatan (Rp)',
                                   data: @json($chartSales),
                                   borderColor: brandOrange,
                                   backgroundColor: 'rgba(249, 115, 22, 0.1)',
                                   borderWidth: 3,
                                   fill: true,
                                   tension: 0.3
                               }]
                           },
                           options: {
                               ...globalOptions,
                               onClick: (e, activeEls) => {
                                   if (activeEls.length > 0) {
                                       const idx = activeEls[0].index;
                                       const label = this.charts.revenue.data.labels[idx];
                                       window.location.href = `/laporan?month=` + encodeURIComponent(label);
                                   }
                               },
                               scales: {
                                   y: { ticks: { callback: v => 'Rp' + v.toLocaleString() } }
                               }
                           }
                       });

                       // 2. Doughnut Chart: Status Order
                       const ctxStatus = document.getElementById('orderStatusChart').getContext('2d');
                       this.charts.status = new Chart(ctxStatus, {
                           type: 'doughnut',
                           data: {
                               labels: @json($chartStatusLabels),
                               datasets: [{
                                   data: @json($chartStatusValues),
                                   backgroundColor: [colorAmber, brandOrange, colorEmerald, colorRose]
                               }]
                           },
                           options: {
                               ...globalOptions,
                               onClick: (e, activeEls) => {
                                   if (activeEls.length > 0) {
                                       const idx = activeEls[0].index;
                                       const label = this.charts.status.data.labels[idx];
                                       window.location.href = `/pesanan?status=` + label.toLowerCase();
                                   }
                               }
                           }
                       });

                       // 3. Bar Chart: Volume Order
                       const ctxSales = document.getElementById('salesVolumeChart').getContext('2d');
                       this.charts.sales = new Chart(ctxSales, {
                           type: 'bar',
                           data: {
                               labels: @json($chartMonths),
                               datasets: [{
                                   label: 'Jumlah Order',
                                   data: @json($chartOrders),
                                   backgroundColor: colorSlate
                               }]
                           },
                           options: {
                               ...globalOptions,
                               onClick: (e, activeEls) => {
                                   if (activeEls.length > 0) {
                                       const idx = activeEls[0].index;
                                       const label = this.charts.sales.data.labels[idx];
                                       window.location.href = `/pesanan?month=` + encodeURIComponent(label);
                                   }
                               }
                           }
                       });

                       // 4. Pie Chart: Produk Terlaris
                       const ctxTop = document.getElementById('topProductsPieChart').getContext('2d');
                       const topIds = @json($topProductIds);
                       this.charts.topProducts = new Chart(ctxTop, {
                           type: 'pie',
                           data: {
                               labels: @json($topProductLabels),
                               datasets: [{
                                   data: @json($topProductValues),
                                   backgroundColor: [brandOrange, '#fb923c', '#fdba74', '#fed7aa', '#ffedd5']
                               }]
                           },
                           options: {
                               ...globalOptions,
                               onClick: (e, activeEls) => {
                                   if (activeEls.length > 0) {
                                       const idx = activeEls[0].index;
                                       const pId = topIds[idx];
                                       if (pId) window.location.href = `/produk/` + pId;
                                   }
                               }
                           }
                       });

                       // 5. Line Chart: Customer Baru
                       const ctxGrowth = document.getElementById('customerGrowthChart').getContext('2d');
                       this.charts.growth = new Chart(ctxGrowth, {
                           type: 'line',
                           data: {
                               labels: @json($customerGrowthMonths),
                               datasets: [{
                                   label: 'Registrasi Baru',
                                   data: @json($customerGrowthValues),
                                   borderColor: colorEmerald,
                                   tension: 0.3
                               }]
                           },
                           options: {
                               ...globalOptions,
                               onClick: (e, activeEls) => {
                                   if (activeEls.length > 0) {
                                       const idx = activeEls[0].index;
                                       const label = this.charts.growth.data.labels[idx];
                                       window.location.href = `/activity-logs?search=` + encodeURIComponent(label);
                                   }
                               }
                           }
                       });

                       // 6. Horizontal Bar Chart: Penjualan per Kategori
                       const ctxCat = document.getElementById('categorySalesChart').getContext('2d');
                       const catIds = @json($categoryIds);
                       this.charts.categories = new Chart(ctxCat, {
                           type: 'bar',
                           data: {
                               labels: @json($categoryLabels),
                               datasets: [{
                                   label: 'Unit Terjual',
                                   data: @json($categoryValues),
                                   backgroundColor: colorAmber
                               }]
                           },
                           options: {
                               ...globalOptions,
                               indexAxis: 'y',
                               onClick: (e, activeEls) => {
                                   if (activeEls.length > 0) {
                                       const idx = activeEls[0].index;
                                       const cId = catIds[idx];
                                       if (cId) window.location.href = `/produk?category_id=` + cId;
                                   }
                               }
                           }
                       });
                   },

                   fetchData(isLive = false) {
                       this.loading = true;
                       
                       let url = `/dashboard?filter=${this.filter}&ajax=1`;
                       if (this.filter === 'custom') {
                           url += `&start_date=${this.startDate}&end_date=${this.endDate}`;
                       }

                       fetch(url, {
                           headers: { 'X-Requested-With': 'XMLHttpRequest' }
                       })
                           .then(res => res.json())
                           .then(data => {
                               // 1. Map KPI
                               this.kpi = data.kpi;

                               // 2. Map Charts
                               this.updateChartData(this.charts.revenue, data.charts.revenue);
                               this.updateChartData(this.charts.sales, data.charts.orders);
                               this.updateChartData(this.charts.status, data.charts.status);
                               this.updateChartData(this.charts.topProducts, data.charts.top_products);
                               this.updateChartData(this.charts.growth, data.charts.growth);
                               this.updateChartData(this.charts.categories, data.charts.categories);

                               // Update Top Product Ids & Category Ids references
                               if (data.charts.top_products.ids) {
                                   this.topProductIds = data.charts.top_products.ids;
                               }
                               if (data.charts.categories.ids) {
                                   this.catIds = data.charts.categories.ids;
                               }

                               // 3. Map Recent Orders & Logs
                               this.recentOrders = data.recent_orders;
                               this.logs = data.logs;

                               this.countdown = 30; // Reset countdown
                           })
                           .catch(err => console.error("Error refreshing dashboard:", err))
                           .finally(() => {
                               this.loading = false;
                           });
                   },

                   updateChartData(chart, source) {
                       if (!chart) return;
                       chart.data.labels = source.labels;
                       chart.data.datasets[0].data = source.values;
                       chart.update();
                   }
               };
           }
       </script>
   @endpush
</x-app-layout>