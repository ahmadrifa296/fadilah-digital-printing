@extends('layouts.app')

@section('content')
<div class="py-12 bg-slate-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        
        <!-- Back button and Title -->
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('dashboard') }}?tab=pesanan" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-black text-slate-800">Detail Pelacakan Paket</h1>
                <p class="text-xs text-slate-400 font-semibold mt-0.5">Invoice #{{ $order->invoice_number }}</p>
            </div>
        </div>

        <!-- Info Card -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden p-6 mb-6 space-y-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs font-semibold">
                <div>
                    <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Nomor Resi</span>
                    <span class="font-mono text-slate-800 font-bold text-sm">{{ $order->tracking_number ?? 'Belum Tersedia' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Kurir / Layanan</span>
                    <span class="text-slate-800 font-bold uppercase">{{ $order->shipping_courier ?? 'Biteship' }} ({{ $order->shipping_service ?? 'REG' }})</span>
                </div>
                <div>
                    <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Status Pengiriman</span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase {{ $order->order_status_badge_class }} border border-current">
                        {{ $order->order_status_label }}
                    </span>
                </div>
                <div>
                    <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Penerima</span>
                    <span class="text-slate-800 font-bold">{{ $order->receiver_name }}</span>
                </div>
            </div>
            <div class="border-t border-slate-100 pt-4 text-xs font-medium text-slate-500">
                <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Alamat Tujuan</span>
                {{ $order->full_address }}, {{ $order->district }}, {{ $order->city }}, {{ $order->province }} - {{ $order->postal_code }}
            </div>
        </div>

        <!-- Timeline Progress -->
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden p-6 mb-6">
            <h3 class="text-sm font-black text-slate-850 uppercase tracking-wide mb-6">Timeline Progress</h3>
            
            <div class="relative border-l-2 border-slate-200 pl-6 space-y-6 ml-3">
                @foreach($timeline as $title => $data)
                    <div class="relative">
                        <!-- Dot marker -->
                        <div class="absolute -left-[31px] top-1.5 h-4.5 w-4.5 rounded-full flex items-center justify-center {{ $data['active'] ? 'bg-orange-500 text-white ring-4 ring-orange-100' : 'bg-slate-200 text-slate-450' }}">
                            @if($data['active'])
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <div class="w-1.5 h-1.5 rounded-full bg-slate-400"></div>
                            @endif
                        </div>
                        
                        <div class="space-y-1">
                            <div class="flex justify-between items-start">
                                <span class="text-xs font-black uppercase tracking-tight {{ $data['active'] ? 'text-orange-600' : 'text-slate-400' }}">
                                    {{ $title }}
                                </span>
                                @if($data['time'] && $data['active'])
                                    <span class="text-[9px] text-slate-400 font-mono font-bold">{{ $data['time']->format('d M Y, H:i') }}</span>
                                @endif
                            </div>
                            <p class="text-xs {{ $data['active'] ? 'text-slate-600' : 'text-slate-350' }} font-medium">
                                {{ $data['desc'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Detailed Shipping Log -->
        @if($shipment && $shipment->trackings->count() > 0)
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden p-6">
                <h3 class="text-sm font-black text-slate-850 uppercase tracking-wide mb-6">Riwayat Logistik Real-Time</h3>
                
                <div class="relative border-l-2 border-slate-100 pl-6 space-y-6 ml-3">
                    @foreach($shipment->trackings->sortByDesc('id') as $index => $log)
                        <div class="relative">
                            <div class="absolute -left-[29px] top-1 h-3.5 w-3.5 rounded-full {{ $index === 0 ? 'bg-orange-500 ring-2 ring-orange-100' : 'bg-slate-300' }}"></div>
                            <div class="space-y-0.5">
                                <div class="flex justify-between items-start font-bold">
                                    <span class="text-xs uppercase tracking-tight {{ $index === 0 ? 'text-orange-600 font-black' : 'text-slate-800' }}">{{ $log->status }}</span>
                                    <span class="text-[9px] text-slate-400 font-mono">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                <p class="text-xs text-slate-500 font-semibold">{{ $log->description }}</p>
                                @if($log->location)
                                    <span class="inline-block text-[9px] bg-slate-50 text-slate-450 border border-slate-150 px-2 py-0.5 rounded-md font-bold mt-1">📍 {{ $log->location }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
