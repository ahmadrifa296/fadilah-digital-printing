<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Manajemen Pengiriman & Resi Kurir</h1>
    </x-slot>

    <div class="space-y-5" x-data="{ ...shippingAdmin(), activeTab: 'siap_kirim' }">
        
        <!-- Top Toolbar Header -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md bg-orange-50 text-orange-700 font-extrabold text-[10px] uppercase tracking-wider border border-orange-200">Logistik & Ekspedisi</span>
                    <span class="text-[11px] text-slate-400 font-medium">&bull; Integration Biteship</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Kelola pengemasan, cetak label resi Biteship, simulasi pickup, dan lacak status pengiriman barang.</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.shipping_settings.index') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-[11px] transition-colors flex items-center gap-1.5 border border-slate-200 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Pengaturan Kurir API</span>
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

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-xs animate-fade-in">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    <p class="text-xs font-bold">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @php
            $siapKirimCount = $orders->filter(fn($o) => (($o->order_status?->value ?? $o->order_status) === 'dikemas' || ($o->shipping_courier === 'pickup' && ($o->order_status?->value ?? $o->order_status) === 'siap_dikemas')) && !$o->shipment)->count();
            $sedangDikirimCount = $orders->filter(fn($o) => ($o->order_status?->value ?? $o->order_status) === 'dikirim' || ($o->order_status?->value ?? $o->order_status) === 'siap_diambil')->count();
            $selesaiCount = $orders->filter(fn($o) => ($o->order_status?->value ?? $o->order_status) === 'selesai')->count();
        @endphp

        <!-- Shipping Tab Controls -->
        <div class="bg-white p-2 rounded-2xl border border-slate-200/80 flex flex-wrap items-center gap-1 shadow-2xs">
            <button @click="activeTab = 'siap_kirim'" 
                    :class="activeTab === 'siap_kirim' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                    class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Siap Kirim / Buat Resi</span>
                <span class="bg-slate-900/10 text-[10px] px-1.5 py-0.5 rounded-md font-mono">{{ $siapKirimCount }}</span>
            </button>

            <button @click="activeTab = 'sedang_dikirim'" 
                    :class="activeTab === 'sedang_dikirim' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                    class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4z"/></svg>
                <span>Dalam Pengiriman / Pickup</span>
                <span class="bg-slate-900/10 text-[10px] px-1.5 py-0.5 rounded-md font-mono">{{ $sedangDikirimCount }}</span>
            </button>

            <button @click="activeTab = 'selesai'" 
                    :class="activeTab === 'selesai' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                    class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Pengiriman Selesai</span>
                <span class="bg-slate-900/10 text-[10px] px-1.5 py-0.5 rounded-md font-mono">{{ $selesaiCount }}</span>
            </button>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap gap-4 items-center justify-between bg-slate-50/50">
                <h2 class="text-[11px] font-extrabold text-slate-800 uppercase tracking-wider" x-text="activeTab === 'siap_kirim' ? 'Daftar Transaksi Siap Kirim & Buat Resi' : (activeTab === 'sedang_dikirim' ? 'Daftar Paket Sedang Dalam Pengiriman' : 'Riwayat Pengiriman Paket Selesai')"></h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[11px]">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-600 font-extrabold text-[10px] uppercase tracking-wider border-b border-slate-200/80 whitespace-nowrap">
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Invoice / Customer</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Tujuan Pengiriman</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Kurir & Layanan</th>
                            <th class="px-3.5 py-2.5 text-right whitespace-nowrap">Ongkir</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">No. Resi / Biteship ID</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Status</th>
                            <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @foreach($orders as $order)
                            <tr class="hover:bg-orange-50/20 transition-colors"
                                x-show="(activeTab === 'siap_kirim' && ('{{ $order->order_status?->value ?? $order->order_status }}' === 'dikemas' || ('{{ $order->shipping_courier }}' === 'pickup' && '{{ $order->order_status?->value ?? $order->order_status }}' === 'siap_dikemas')) && !{{ $order->shipment ? 'true' : 'false' }}) ||
                                       (activeTab === 'sedang_dikirim' && ('{{ $order->order_status?->value ?? $order->order_status }}' === 'dikirim' || '{{ $order->order_status?->value ?? $order->order_status }}' === 'siap_diambil')) ||
                                       (activeTab === 'selesai' && '{{ $order->order_status?->value ?? $order->order_status }}' === 'selesai')">
                                <td class="px-3.5 py-2.5 space-y-0.5 whitespace-nowrap">
                                    <div class="font-mono text-xs font-black text-slate-900 whitespace-nowrap">#{{ $order->invoice_number }}</div>
                                    <div class="text-[10px] text-slate-500 font-semibold whitespace-nowrap">{{ $order->user->name ?? 'Tamu' }}</div>
                                    <div class="text-[9px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-100 font-bold inline-block uppercase tracking-wider whitespace-nowrap">{{ $order->order_status_label }}</div>
                                </td>
                                <td class="px-3.5 py-2.5 max-w-xs">
                                    <div class="font-bold text-slate-800 truncate max-w-[200px]" title="{{ $order->full_address }}">{{ $order->receiver_name }}</div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[220px]" title="{{ $order->full_address }}">{{ $order->full_address }}</div>
                                </td>
                                <td class="px-3.5 py-2.5 whitespace-nowrap">
                                    @if($order->shipping_courier === 'pickup')
                                        <span class="px-2 py-0.5 rounded font-black text-[9px] bg-slate-800 text-white uppercase tracking-wider whitespace-nowrap">AMBIL DI TOKO</span>
                                    @else
                                        <div class="flex items-center gap-1.5 whitespace-nowrap">
                                            <span class="px-1.5 py-0.5 rounded font-black text-[9px] bg-slate-900 text-white uppercase shrink-0">{{ $order->shipping_courier ?: 'biteship' }}</span>
                                            <span class="font-bold text-slate-800 uppercase shrink-0">{{ $order->shipping_service ?? '-' }}</span>
                                        </div>
                                        <div class="text-[9px] text-slate-400 font-semibold uppercase mt-0.5 whitespace-nowrap">Est: {{ $order->shipping_estimation ?? '-' }}</div>
                                    @endif
                                </td>
                                <td class="px-3.5 py-2.5 text-right font-black text-slate-900 text-xs whitespace-nowrap">
                                    Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                                </td>
                                <td class="px-3.5 py-2.5 space-y-0.5 whitespace-nowrap">
                                    @if($order->shipping_courier === 'pickup')
                                        <span class="text-slate-400 italic whitespace-nowrap">Ambil di Toko</span>
                                    @elseif($order->shipment)
                                        <div class="font-mono font-bold text-slate-800 text-[11px] whitespace-nowrap">{{ $order->shipment->tracking_number }}</div>
                                        @if($order->shipment->shipment_id)
                                            <div class="text-[8px] text-slate-400 font-mono whitespace-nowrap">Biteship: {{ $order->shipment->shipment_id }}</div>
                                        @else
                                            <div class="text-[8px] text-orange-400 font-semibold font-mono whitespace-nowrap">Resi Manual</div>
                                        @endif
                                    @else
                                        <div class="text-[10px] text-slate-400 italic whitespace-nowrap">Belum dibuat</div>
                                    @endif
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold border uppercase tracking-wider whitespace-nowrap inline-block {{ $order->order_status_badge_class }}">
                                        {{ $order->order_status_label }}
                                    </span>
                                    @if($order->shipment)
                                        <div class="text-[8px] text-slate-400 font-bold uppercase mt-1 whitespace-nowrap">Status: {{ $order->shipment->status }}</div>
                                    @endif
                                </td>
                                <td class="p-4 text-center whitespace-nowrap">
                                    <div class="flex flex-wrap items-center justify-center gap-1.5 whitespace-nowrap">
                                        @if($order->shipping_courier === 'pickup')
                                            @if(in_array(($order->order_status?->value ?? $order->order_status), ['dikemas', 'siap_dikemas']))
                                                <form action="{{ route('admin.shipping.shipments.create', $order->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-lg px-2.5 py-1 cursor-pointer transition-colors shadow-2xs text-[10px] uppercase tracking-wider whitespace-nowrap inline-flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        <span>Siap Diambil</span>
                                                    </button>
                                                </form>
                                            @elseif(($order->order_status?->value ?? $order->order_status) === 'siap_diambil')
                                                <form action="{{ route('pesanan.update', $order->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="order_status" value="selesai">
                                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-lg px-2.5 py-1 cursor-pointer transition-colors shadow-2xs text-[10px] uppercase tracking-wider whitespace-nowrap inline-flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        <span>Sudah Diambil</span>
                                                    </button>
                                                </form>
                                            @elseif(($order->order_status?->value ?? $order->order_status) === 'selesai')
                                                <span class="text-xs text-emerald-600 font-bold whitespace-nowrap">Telah Diambil</span>
                                            @endif
                                        @else
                                            @if(($order->order_status?->value ?? $order->order_status) === 'dikemas' && !$order->shipment)
                                                <div class="flex items-center gap-1.5 whitespace-nowrap">
                                                    {{-- Buat Pengiriman --}}
                                                    <form action="{{ route('admin.shipping.shipments.create', $order->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-lg px-2.5 py-1 cursor-pointer transition-colors shadow-2xs text-[10px] uppercase tracking-wider whitespace-nowrap inline-flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                            <span>Buat Pengiriman</span>
                                                        </button>
                                                    </form>
                                                    {{-- Cetak Label --}}
                                                    <form action="{{ route('admin.shipping.shipments.create', $order->id) }}?action=print" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold rounded-lg px-2.5 py-1 cursor-pointer transition-colors border border-slate-200 text-[10px] uppercase tracking-wider whitespace-nowrap inline-flex items-center gap-1">
                                                            <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                            <span>Cetak Label</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif(($order->order_status?->value ?? $order->order_status) === 'dikirim' && $order->shipment)
                                                <div class="flex items-center justify-center gap-1.5 whitespace-nowrap">
                                                    {{-- Detail --}}
                                                    <button @click="openShipmentDetailModal({{ $order->shipment->toJson() }}, {{ $order->toJson() }})"
                                                            class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 font-extrabold rounded-lg px-2.5 py-1 transition-colors text-[10px] uppercase tracking-wider whitespace-nowrap inline-flex items-center gap-1"
                                                            title="Lihat Detail Shipment">
                                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                        <span>Detail</span>
                                                    </button>
                                                    {{-- Update Tracking --}}
                                                    <button @click="openUpdateTrackingModal({{ $order->shipment->toJson() }})"
                                                            class="bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/80 font-extrabold rounded-lg px-2.5 py-1 transition-colors text-[10px] uppercase tracking-wider whitespace-nowrap inline-flex items-center gap-1"
                                                            title="Update Tracking">
                                                        <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5"/></svg>
                                                        <span>Update Status</span>
                                                    </button>
                                                </div>
                                                {{-- Input Resi --}}
                                                <form action="{{ route('admin.shipping.update_resi', $order->id) }}" method="POST" class="flex gap-1 items-center mt-1.5 font-bold whitespace-nowrap justify-center">
                                                    @csrf
                                                    <input type="text" name="tracking_number" value="{{ $order->shipment->tracking_number }}" required placeholder="No. Resi" class="text-[9px] border border-slate-200 rounded-md py-0.5 px-1.5 focus:ring-0 focus:border-orange-500 w-24 font-mono">
                                                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-extrabold rounded-md px-2 py-0.5 cursor-pointer transition-colors text-[9px] uppercase whitespace-nowrap">
                                                        Simpan
                                                    </button>
                                                </form>
                                            @elseif(($order->order_status?->value ?? $order->order_status) === 'selesai' && $order->shipment)
                                                <div class="flex items-center justify-center gap-1.5 whitespace-nowrap">
                                                    {{-- Detail --}}
                                                    <button @click="openShipmentDetailModal({{ $order->shipment->toJson() }}, {{ $order->toJson() }})"
                                                            class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 font-extrabold rounded-lg px-2.5 py-1 transition-colors text-[10px] uppercase tracking-wider whitespace-nowrap inline-flex items-center gap-1"
                                                            title="Lihat Detail Shipment">
                                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                        <span>Detail</span>
                                                    </button>
                                                    {{-- Download Label --}}
                                                    <a href="{{ route('admin.shipping.shipments.label', $order->shipment->id) }}" target="_blank"
                                                       class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 font-extrabold rounded-lg px-2.5 py-1 transition-colors text-[10px] uppercase tracking-wider whitespace-nowrap inline-flex items-center gap-1"
                                                       title="Download Label">
                                                        <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                        <span>Download Label</span>
                                                    </a>
                                                    {{-- Lihat Tracking --}}
                                                    <button @click="openLocalTrackModal({{ $order->shipment->toJson() }}, {{ $order->shipment->trackings->toJson() }})"
                                                            class="bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200/80 font-extrabold rounded-lg px-2.5 py-1 transition-colors text-[10px] uppercase tracking-wider whitespace-nowrap inline-flex items-center gap-1"
                                                            title="Lihat Riwayat Tracking">
                                                        <svg class="w-3 h-3 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                        <span>Lihat Tracking</span>
                                                    </button>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Empty States --}}
                        <tr x-show="activeTab === 'siap_kirim' && {{ $siapKirimCount }} === 0">
                            <td colspan="7" class="p-12 text-center text-slate-400 italic font-medium">
                                Tidak ada data transaksi yang siap dikirim.
                            </td>
                        </tr>
                        <tr x-show="activeTab === 'sedang_dikirim' && {{ $sedangDikirimCount }} === 0">
                            <td colspan="7" class="p-12 text-center text-slate-400 italic font-medium">
                                Tidak ada data transaksi yang sedang dikirim.
                            </td>
                        </tr>
                        <tr x-show="activeTab === 'selesai' && {{ $selesaiCount }} === 0">
                            <td colspan="7" class="p-12 text-center text-slate-400 italic font-medium">
                                Tidak ada data transaksi yang selesai.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Detail Shipment --}}
        <div x-show="shipmentDetailModalOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition
             x-cloak>
            <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-slate-150 overflow-hidden flex flex-col max-h-[85vh]"
                 @click.away="shipmentDetailModalOpen = false">
                <div class="bg-slate-900 text-white p-5 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="font-bold text-[10px] uppercase tracking-wider text-orange-400">Detail Pengiriman</h3>
                        <h2 class="text-sm font-bold mt-0.5" x-text="'Invoice ' + activeInvoice"></h2>
                    </div>
                    <button @click="shipmentDetailModalOpen = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>
                
                <div class="p-6 overflow-y-auto space-y-4 flex-1 text-xs no-scrollbar">
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 border border-slate-200/60 rounded-2xl">
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Nomor Resi</span>
                            <span class="font-mono font-bold text-slate-800 text-xs" x-text="activeShipment.tracking_number"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">ID Biteship</span>
                            <span class="font-mono font-semibold text-slate-500" x-text="activeShipment.shipment_id || '-'"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Kurir / Layanan</span>
                            <span class="font-bold text-slate-800 uppercase" x-text="activeShipment.courier + ' (' + activeShipment.service + ')'"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Status Pengiriman</span>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold border uppercase tracking-wider bg-orange-100 text-orange-600 border-orange-200" x-text="activeShipment.status"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Pickup Status</span>
                            <span class="font-semibold text-slate-700 uppercase" x-text="activeShipment.pickup_status"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Estimasi Waktu</span>
                            <span class="font-semibold text-slate-700" x-text="activeShipment.estimated_days || '-'"></span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-[9px] font-bold text-slate-450 uppercase">Tujuan Penerima</span>
                        <div class="bg-white border border-slate-150 p-3.5 rounded-2xl space-y-1 font-medium">
                            <div class="font-bold text-slate-800" x-text="activeOrder.receiver_name"></div>
                            <div x-text="'Telp: ' + activeOrder.phone"></div>
                            <div class="text-slate-500 leading-relaxed" x-text="activeOrder.full_address"></div>
                            <div class="font-bold text-slate-600" x-text="'Kodepos: ' + activeOrder.postal_code"></div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-[9px] font-bold text-slate-450 uppercase">Response JSON dari Biteship API</span>
                        <div class="bg-slate-900 text-emerald-400 font-mono text-[9px] p-4 rounded-2xl overflow-x-auto max-h-40 overflow-y-auto">
                            <pre x-text="JSON.stringify(activeShipment.raw_response, null, 2)"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Riwayat Tracking (Local) --}}
        <div x-show="localTrackModalOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition
             x-cloak>
            <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-150 overflow-hidden flex flex-col max-h-[80vh]"
                 @click.away="localTrackModalOpen = false">
                <div class="bg-slate-900 text-white p-5 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="font-bold text-[10px] uppercase tracking-wider text-orange-400">Riwayat Pengiriman</h3>
                        <h2 class="text-sm font-bold mt-0.5" x-text="'Resi ' + activeResi"></h2>
                    </div>
                    <button @click="localTrackModalOpen = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>
                
                <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs no-scrollbar">
                    <div x-show="localTrackData.length === 0" class="text-center py-8 text-slate-400 italic">
                        Belum ada histori pelacakan untuk pengiriman ini.
                    </div>
                    <div x-show="localTrackData.length > 0" class="relative border-l-2 border-slate-200 pl-6 space-y-6 ml-2.5">
                        <template x-for="(track, index) in localTrackData" :key="'local_' + track.id">
                            <div class="relative">
                                {{-- Dot marker --}}
                                <div :class="index === 0 ? 'bg-orange-500 ring-4 ring-orange-100' : 'bg-slate-300'"
                                     class="absolute -left-[31px] top-0.5 h-3 w-3 rounded-full"></div>
                                
                                <div class="space-y-1">
                                    <div class="flex justify-between items-start">
                                        <span :class="index === 0 ? 'text-orange-600 font-extrabold' : 'text-slate-800 font-bold'"
                                              class="uppercase tracking-tight text-[10px]" x-text="track.status"></span>
                                        <span class="text-[9px] text-slate-400 font-mono font-semibold" x-text="formatDate(track.created_at)"></span>
                                    </div>
                                    <p class="text-slate-500 font-medium leading-relaxed" x-text="track.description"></p>
                                    <p x-show="track.location" class="text-[10px] text-slate-400 font-semibold" x-text="'Lokasi: ' + track.location"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Simulasi Pickup --}}
        <div x-show="pickupModalOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition
             x-cloak>
            <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-150 overflow-hidden"
                 @click.away="pickupModalOpen = false">
                <div class="bg-slate-900 text-white p-5 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-[10px] uppercase tracking-wider text-orange-400">Simulasi Pickup</h3>
                        <h2 class="text-sm font-bold mt-0.5" x-text="'Resi ' + activeResi"></h2>
                    </div>
                    <button @click="pickupModalOpen = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>
                
                <form :action="'/admin/shipping/shipments/' + activeShipmentId + '/pickup'" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="pickup_status" class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Status Pickup</label>
                        <select id="pickup_status" name="pickup_status" x-model="activePickupStatus"
                                class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                            <option value="waiting_pickup">Waiting Pickup</option>
                            <option value="pickup_requested">Pickup Requested</option>
                            <option value="picked_up">Picked Up</option>
                        </select>
                    </div>
                    <div class="flex gap-2 justify-end pt-2">
                        <button type="button" @click="pickupModalOpen = false" class="btn-sm bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold px-4 py-2 cursor-pointer">Batal</button>
                        <button type="submit" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold px-6 py-2 shadow-md uppercase tracking-wider text-[10px] cursor-pointer">Simpan Status</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Update Tracking --}}
        <div x-show="updateTrackingModalOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition
             x-cloak>
            <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-150 overflow-hidden"
                 @click.away="updateTrackingModalOpen = false">
                <div class="bg-slate-900 text-white p-5 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-[10px] uppercase tracking-wider text-orange-400">Update Tracking</h3>
                        <h2 class="text-sm font-bold mt-0.5" x-text="'Resi ' + activeResi"></h2>
                    </div>
                    <button @click="updateTrackingModalOpen = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>
                
                <form :action="'/admin/shipping/shipments/' + activeShipmentId + '/tracking'" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="status" class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Status Pengiriman</label>
                        <select id="status" name="status" class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                            <option value="Picked Up">Picked Up</option>
                            <option value="Sorting Facility">Sorting Facility</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Out For Delivery">Out For Delivery</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </div>
                    <div>
                        <label for="description" class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Keterangan / Catatan</label>
                        <textarea id="description" name="description" required placeholder="Contoh: Paket sedang dikirim ke alamat tujuan..."
                                  class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500 h-20"></textarea>
                    </div>
                    <div>
                        <label for="location" class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Lokasi (Opsional)</label>
                        <input type="text" id="location" name="location" placeholder="Contoh: Jakarta Selatan"
                               class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:border-orange-500 focus:ring-orange-500">
                    </div>
                    <div class="flex gap-2 justify-end pt-2">
                        <button type="button" @click="updateTrackingModalOpen = false" class="btn-sm bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold px-4 py-2 cursor-pointer">Batal</button>
                        <button type="submit" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold px-6 py-2 shadow-md uppercase tracking-wider text-[10px] cursor-pointer">Update Status</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

@push('scripts')
<script>
    function shippingAdmin() {
        return {
            trackModalOpen: false,
            localTrackModalOpen: false,
            pickupModalOpen: false,
            updateTrackingModalOpen: false,
            shipmentDetailModalOpen: false,
            activeShipmentId: null,
            activeInvoice: '',
            activeResi: '',
            activePickupStatus: '',
            localTrackData: [],
            activeShipment: {},
            activeOrder: {},

            openShipmentDetailModal(shipment, order) {
                this.activeShipment = shipment;
                this.activeOrder = order;
                this.activeInvoice = order.invoice_number;
                this.activeResi = shipment.tracking_number;
                this.shipmentDetailModalOpen = true;
            },

            openLocalTrackModal(shipment, trackings) {
                this.activeShipmentId = shipment.id;
                this.activeResi = shipment.tracking_number;
                // Sort trackings desc by created_at or id
                this.localTrackData = trackings.sort((a, b) => b.id - a.id);
                this.localTrackModalOpen = true;
            },

            openPickupModal(shipment) {
                this.activeShipmentId = shipment.id;
                this.activeResi = shipment.tracking_number;
                this.activePickupStatus = shipment.pickup_status;
                this.pickupModalOpen = true;
            },

            openUpdateTrackingModal(shipment) {
                this.activeShipmentId = shipment.id;
                this.activeResi = shipment.tracking_number;
                this.updateTrackingModalOpen = true;
            },

            formatDate(datetime) {
                if (!datetime) return '-';
                const d = new Date(datetime);
                return d.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        };
    }
</script>
@endpush
