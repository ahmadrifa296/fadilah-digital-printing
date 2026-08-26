<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Kelola Klaim Garansi & Refund</h1>
    </x-slot>

    <div class="space-y-5" x-data="{ activeTab: 'all', search: '' }">
        
        <!-- Top Toolbar Header -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md bg-orange-50 text-orange-700 font-extrabold text-[10px] uppercase tracking-wider border border-orange-200">Garansi Purna Jual</span>
                    <span class="text-[11px] text-slate-400 font-medium">&bull; Verifikasi Komplain Pelanggan</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Pemeriksaan bukti video unboxing, evaluasi kerusakan cetak, dan persetujuan pengembalian dana.</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('pesanan.index') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-[11px] transition-colors flex items-center gap-1.5 border border-slate-200">
                    <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Kelola Pesanan</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs animate-fade-in">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    <p class="text-xs font-bold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @php
            $pendingCount = $claims->filter(fn($c) => $c->status === 'pending')->count();
            $approvedCount = $claims->filter(fn($c) => $c->status === 'approved')->count();
            $rejectedCount = $claims->filter(fn($c) => $c->status === 'rejected')->count();
            $totalCount = $claims->count();
        @endphp

        <!-- KPI Summary Cards Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            <!-- Total Klaim -->
            <div @click="activeTab = 'all'" class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-2xs cursor-pointer hover:border-orange-300 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Laporan</span>
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-black uppercase">Semua</span>
                </div>
                <div class="mt-1.5 flex items-baseline justify-between">
                    <span class="text-xl font-black text-slate-900 tracking-tight">{{ $totalCount }}</span>
                    <span class="text-[10px] text-slate-400 font-semibold">Seluruh Klaim</span>
                </div>
            </div>

            <!-- Pending -->
            <div @click="activeTab = 'pending'" class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-2xs cursor-pointer hover:border-amber-400 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Menunggu Verifikasi</span>
                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[10px] font-black uppercase">Pending</span>
                </div>
                <div class="mt-1.5 flex items-baseline justify-between">
                    <span class="text-xl font-black text-amber-600 tracking-tight">{{ $pendingCount }}</span>
                    <span class="text-[10px] text-amber-600 font-semibold">Perlu Evaluasi</span>
                </div>
            </div>

            <!-- Approved -->
            <div @click="activeTab = 'approved'" class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-2xs cursor-pointer hover:border-emerald-400 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Disetujui (Refund)</span>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase">Disetujui</span>
                </div>
                <div class="mt-1.5 flex items-baseline justify-between">
                    <span class="text-xl font-black text-emerald-600 tracking-tight">{{ $approvedCount }}</span>
                    <span class="text-[10px] text-emerald-600 font-semibold">Pengembalian Dana</span>
                </div>
            </div>

            <!-- Rejected -->
            <div @click="activeTab = 'rejected'" class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-2xs cursor-pointer hover:border-rose-400 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Ditolak</span>
                    <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 text-[10px] font-black uppercase">Ditolak</span>
                </div>
                <div class="mt-1.5 flex items-baseline justify-between">
                    <span class="text-xl font-black text-rose-600 tracking-tight">{{ $rejectedCount }}</span>
                    <span class="text-[10px] text-rose-600 font-semibold">Tidak Valid</span>
                </div>
            </div>
        </div>

        <!-- Controls: Tab Bar & Search -->
        <div class="bg-white p-2 rounded-2xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 shadow-2xs">
            <!-- Tabs -->
            <div class="flex flex-wrap items-center gap-1">
                <button @click="activeTab = 'all'" 
                        :class="activeTab === 'all' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                        class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Semua Klaim</span>
                    <span class="bg-slate-900/10 px-1.5 py-0.5 rounded-md text-[10px] font-mono">{{ $totalCount }}</span>
                </button>

                <button @click="activeTab = 'pending'" 
                        :class="activeTab === 'pending' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                        class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Pending</span>
                    <span class="bg-slate-900/10 px-1.5 py-0.5 rounded-md text-[10px] font-mono">{{ $pendingCount }}</span>
                </button>

                <button @click="activeTab = 'approved'" 
                        :class="activeTab === 'approved' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                        class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Disetujui</span>
                    <span class="bg-slate-900/10 px-1.5 py-0.5 rounded-md text-[10px] font-mono">{{ $approvedCount }}</span>
                </button>

                <button @click="activeTab = 'rejected'" 
                        :class="activeTab === 'rejected' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                        class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Ditolak</span>
                    <span class="bg-slate-900/10 px-1.5 py-0.5 rounded-md text-[10px] font-mono">{{ $rejectedCount }}</span>
                </button>
            </div>

            <!-- Search -->
            <div class="relative sm:w-60">
                <input type="text" x-model="search" placeholder="Cari invoice / pelanggan..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1.5 pl-8 pr-3 text-[11px] text-slate-700 focus:bg-white focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-all font-medium">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h2 class="text-[11px] font-extrabold text-slate-800 uppercase tracking-wider">
                    Daftar Laporan Komplain Garansi
                </h2>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Urutan Terbaru</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-600 font-extrabold text-[10px] uppercase tracking-wider border-b border-slate-200/80 whitespace-nowrap">
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Invoice & Customer</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Alasan Klaim</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Rincian Keluhan</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Tanggal</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Status Garansi</th>
                            <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($claims as $claim)
                            <tr class="hover:bg-orange-50/20 transition-colors"
                                x-show="(activeTab === 'all' || '{{ $claim->status }}' === activeTab) && ('{{ strtolower($claim->order->invoice_number) }}'.includes(search.toLowerCase()) || '{{ strtolower($claim->order->user->name ?? 'Tamu') }}'.includes(search.toLowerCase()))">
                                
                                <td class="px-3.5 py-2.5 whitespace-nowrap">
                                    <div class="font-black text-slate-900 font-mono text-[11px] whitespace-nowrap">#{{ $claim->order->invoice_number }}</div>
                                    <div class="text-[10px] text-slate-500 font-semibold whitespace-nowrap">{{ $claim->order->user->name ?? 'Tamu' }}</div>
                                </td>

                                <td class="px-3.5 py-2.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap">
                                        {{ $claim->reason }}
                                    </span>
                                </td>

                                <td class="px-3.5 py-2.5 max-w-xs">
                                    <div class="truncate text-slate-800 font-medium max-w-[240px]" title="{{ $claim->description }}">{{ $claim->description }}</div>
                                    <div class="text-[9px] text-slate-400 mt-0.5">
                                        {{ $claim->proof_video ? 'Video Bukti Terlampir' : 'Tanpa Video' }}
                                    </div>
                                </td>

                                <td class="px-3.5 py-2.5 font-mono text-slate-600 text-[10px]">
                                    {{ $claim->created_at->format('d/m/Y H:i') }}
                                </td>

                                <td class="px-3.5 py-2.5">
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider border inline-block
                                        {{ $claim->status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                        {{ $claim->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                        {{ $claim->status === 'rejected' ? 'bg-rose-50 text-rose-700 border-rose-200' : '' }}
                                    ">
                                        {{ $claim->status === 'pending' ? 'Pending' : ($claim->status === 'approved' ? 'Disetujui' : 'Ditolak') }}
                                    </span>
                                </td>

                                <td class="px-3.5 py-2.5 text-center">
                                    <a href="{{ route('admin.claims.show', $claim->id) }}" class="inline-flex items-center bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-lg px-3 py-1 shadow-2xs text-[10px] uppercase tracking-wider transition-all active:scale-[0.97]">
                                        Periksa Bukti
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Empty States --}}
                        <tr x-show="activeTab === 'all' && {{ $totalCount }} === 0">
                            <td colspan="6" class="p-12 text-center text-slate-400 italic font-medium">
                                Belum ada pengajuan komplain garansi masuk.
                            </td>
                        </tr>
                        <tr x-show="activeTab === 'pending' && {{ $pendingCount }} === 0">
                            <td colspan="6" class="p-12 text-center text-slate-400 italic font-medium">
                                Tidak ada pengajuan garansi berstatus pending.
                            </td>
                        </tr>
                        <tr x-show="activeTab === 'approved' && {{ $approvedCount }} === 0">
                            <td colspan="6" class="p-12 text-center text-slate-400 italic font-medium">
                                Tidak ada klaim garansi yang disetujui.
                            </td>
                        </tr>
                        <tr x-show="activeTab === 'rejected' && {{ $rejectedCount }} === 0">
                            <td colspan="6" class="p-12 text-center text-slate-400 italic font-medium">
                                Tidak ada klaim garansi yang ditolak.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>

