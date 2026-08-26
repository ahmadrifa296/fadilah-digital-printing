<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Kelola Pesanan & Production Workflow</h1>
    </x-slot>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs animate-fade-in mb-5">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                <p class="text-xs font-bold">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div x-data="{ 
        activeTab: (new URLSearchParams(window.location.search).get('tab')) || 'diproses', 
        previewOpen: false,
        activeFileUrl: '',
        activeInvoice: '',
        activeType: '',
        activeOrderId: null,
        zoomLevel: 100,

        openPreview(url, invoice, type, orderId) {
            this.activeFileUrl = url;
            this.activeInvoice = invoice;
            this.activeType = type;
            this.activeOrderId = orderId;
            this.zoomLevel = 100;
            this.previewOpen = true;
        },

        triggerPrint(orderId, url, mode, ext, invoice) {
            fetch(`/pesanan/${orderId}/trigger-print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.updated) {
                    setTimeout(() => { window.location.href = '/pesanan?tab=sedang_dicetak'; }, 600);
                }
            })
            .catch(err => console.error('Error triggering print status:', err));

            if (mode === 'preview') {
                this.openPreview(url, invoice, ext, orderId);
            } else if (mode === 'print') {
                const win = window.open(url, '_blank');
                win.focus();
                win.print();
            } else {
                // Download File
                const link = document.createElement('a');
                link.href = url;
                link.download = '';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    }" class="space-y-6">

        <!-- Workflow Tabs Control -->
        <div class="bg-white p-2 rounded-2xl border border-slate-200/80 flex flex-wrap items-center gap-1 shadow-2xs">
            <button @click="activeTab = 'diproses'" 
                    :class="activeTab === 'diproses' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                    class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Diproses</span>
                <span class="bg-slate-900/10 text-[10px] px-1.5 py-0.5 rounded-md font-mono">{{ $orders->filter(fn($o) => in_array($o->payment_status, ['paid', 'settlement']) && ($o->order_status?->value ?? $o->order_status) === 'diproses')->count() }}</span>
            </button>

            <button @click="activeTab = 'sedang_dicetak'" 
                    :class="activeTab === 'sedang_dicetak' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                    class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Sedang Dicetak</span>
                <span class="bg-slate-900/10 text-[10px] px-1.5 py-0.5 rounded-md font-mono">{{ $orders->where('order_status', \App\Enums\OrderStatus::SEDANG_DICETAK)->count() }}</span>
            </button>

            <button @click="activeTab = 'siap_dikemas'" 
                    :class="activeTab === 'siap_dikemas' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                    class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Siap Dikemas / Diambil</span>
                <span class="bg-slate-900/10 text-[10px] px-1.5 py-0.5 rounded-md font-mono">{{ $orders->filter(fn($o) => in_array($o->order_status?->value ?? $o->order_status, ['siap_dikemas', 'siap_diambil']))->count() }}</span>
            </button>

            <button @click="activeTab = 'dikemas'" 
                    :class="activeTab === 'dikemas' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                    class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                <span>Dikemas</span>
                <span class="bg-slate-900/10 text-[10px] px-1.5 py-0.5 rounded-md font-mono">{{ $orders->where('order_status', \App\Enums\OrderStatus::DIKEMAS)->count() }}</span>
            </button>

            <button @click="activeTab = 'dikirim'" 
                    :class="activeTab === 'dikirim' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                    class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4z"/></svg>
                <span>Dikirim</span>
                <span class="bg-slate-900/10 text-[10px] px-1.5 py-0.5 rounded-md font-mono">{{ $orders->where('order_status', \App\Enums\OrderStatus::DIKIRIM)->count() }}</span>
            </button>

            <button @click="activeTab = 'selesai'" 
                    :class="activeTab === 'selesai' ? 'bg-orange-500 text-white font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-100 font-bold'" 
                    class="px-3.5 py-1.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Selesai</span>
                <span class="bg-slate-900/10 text-[10px] px-1.5 py-0.5 rounded-md font-mono">{{ $orders->where('order_status', \App\Enums\OrderStatus::SELESAI)->count() }}</span>
            </button>
        </div>

        <!-- Orders Table Container (Fit to Laptop Screen) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
               <table class="w-full text-left border-collapse text-[11px]">
                   <thead>
                       <tr class="bg-slate-100/70 text-slate-600 font-extrabold text-[10px] uppercase tracking-wider border-b border-slate-200/80 whitespace-nowrap">
                           <th class="px-3.5 py-2.5 whitespace-nowrap">Invoice / Pelanggan</th>
                           <th class="px-3.5 py-2.5 whitespace-nowrap">Detail Rincian Produk</th>
                           <th class="px-3.5 py-2.5 text-center whitespace-nowrap">File Desain</th>
                           <th class="px-3.5 py-2.5 text-right whitespace-nowrap">Total</th>
                           <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Pembayaran</th>
                           <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Status Produksi</th>
                           <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Aksi Alur</th>
                       </tr>
                   </thead>
                   <tbody class="divide-y divide-slate-100 text-slate-700">
                       @forelse($orders as $item)
                           @php
                               $designExt = $item->design_file ? strtolower(pathinfo($item->design_file, PATHINFO_EXTENSION)) : '';
                               $hasCetak = $item->orderDetails()->whereHas('product', function($q) {
                                   $q->where('requires_design_file', true);
                               })->exists();
                           @endphp
                           <!-- Render rows with Alpine x-show filter -->
                           <tr class="hover:bg-orange-50/20 transition-colors"
                               x-show="(activeTab === 'diproses' && ['paid', 'settlement'].includes('{{ $item->payment_status }}') && '{{ $item->order_status?->value ?? $item->order_status }}' === 'diproses') ||
                                      (activeTab === 'sedang_dicetak' && '{{ $item->order_status?->value ?? $item->order_status }}' === 'sedang_dicetak') ||
                                      (activeTab === 'siap_dikemas' && ['siap_dikemas', 'siap_diambil'].includes('{{ $item->order_status?->value ?? $item->order_status }}')) ||
                                      (activeTab === 'dikemas' && '{{ $item->order_status?->value ?? $item->order_status }}' === 'dikemas') ||
                                      (activeTab === 'dikirim' && '{{ $item->order_status?->value ?? $item->order_status }}' === 'dikirim') ||
                                      (activeTab === 'selesai' && '{{ $item->order_status?->value ?? $item->order_status }}' === 'selesai')">
                               <td class="px-3.5 py-2.5 space-y-0.5 whitespace-nowrap">
                                   <div class="font-mono text-xs font-black text-slate-900 whitespace-nowrap">#{{ $item->invoice_number }}</div>
                                   <div class="text-[11px] font-bold text-slate-700 whitespace-nowrap">{{ $item->user->name ?? 'Guest' }}</div>
                                   <div class="text-[9px] font-mono text-slate-400 whitespace-nowrap">{{ $item->user->phone ?? '-' }}</div>
                               </td>
                               <td class="px-3.5 py-2.5 text-[11px] text-slate-700 max-w-xs">
                                   @if($item->notes)
                                       <div class="mb-1 italic text-slate-400 font-medium text-[10px] truncate max-w-[220px]" title="{{ $item->notes }}">Catatan: "{{ $item->notes }}"</div>
                                   @endif
                                   <div class="space-y-1">
                                       @foreach($item->orderDetails as $det)
                                           <div class="bg-slate-50 border border-slate-200/60 p-1.5 rounded-lg">
                                               <div class="font-bold text-slate-800 text-[11px]">{{ $det->qty }}x {{ $det->product?->product_name ?? 'Produk' }}</div>
                                               @if($det->ukuran || $det->bahan || $det->finishing || $det->custom_text)
                                                   <div class="text-[9px] text-orange-600 font-semibold mt-0.5 space-y-0.2">
                                                       @if($det->ukuran) <span>Size: {{ $det->ukuran }}</span> @endif
                                                       @if($det->bahan) <span>&bull; Bahan: {{ $det->bahan }}</span> @endif
                                                       @if($det->finishing) <span>&bull; {{ $det->finishing }}</span> @endif
                                                       @if($det->custom_text) <div class="truncate max-w-[200px]">Text: "{{ $det->custom_text }}"</div> @endif
                                                   </div>
                                               @endif
                                           </div>
                                       @endforeach
                                   </div>
                               </td>
                               <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                   @if($item->design_file)
                                       <div class="flex flex-col gap-1 items-center justify-center whitespace-nowrap">
                                           <button @click="triggerPrint({{ $item->id }}, '{{ $item->design_file_url }}', 'preview', '{{ $designExt }}', '{{ $item->invoice_number }}')"
                                                   class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white font-extrabold rounded-md transition-colors text-[9px] uppercase tracking-wide cursor-pointer shadow-2xs whitespace-nowrap inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Lihat Desain</span>
                                           </button>
                                           <button @click="triggerPrint({{ $item->id }}, '{{ $item->design_file_url }}', 'download', '{{ $designExt }}', '{{ $item->invoice_number }}')"
                                              class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold rounded-md transition-colors text-[9px] uppercase tracking-wide cursor-pointer border border-slate-200 whitespace-nowrap inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>Unduh File</span>
                                           </button>
                                       </div>
                                   @else
                                       <span class="text-[10px] text-slate-400 italic whitespace-nowrap">Tanpa File</span>
                                   @endif
                               </td>
                               <td class="px-3.5 py-2.5 text-right font-black text-slate-900 text-xs whitespace-nowrap">
                                   Rp {{ number_format($item->total_price, 0, ',', '.') }}
                               </td>
                               <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                   @php
                                       $psMap = [
                                           'pending'    => ['label' => 'Belum Bayar', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                           'settlement' => ['label' => 'Lunas',       'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                           'capture'    => ['label' => 'Lunas',       'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                           'deny'       => ['label' => 'Ditolak',     'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                           'cancel'     => ['label' => 'Batal',       'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                           'expire'     => ['label' => 'Kedaluwarsa', 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
                                       ];
                                       $ps = $psMap[$item->payment_status] ?? ['label' => ucfirst($item->payment_status ?? '-'), 'class' => 'bg-slate-100 text-slate-650 border-slate-200'];
                                   @endphp
                                   <span class="{{ $ps['class'] }} text-[9px] font-extrabold px-2 py-0.5 rounded-md border uppercase tracking-wider inline-block whitespace-nowrap">
                                       {{ $ps['label'] }}
                                   </span>
                                   @if($item->payment_type)
                                       <div class="text-[8px] text-slate-400 mt-0.5 capitalize font-semibold whitespace-nowrap">{{ str_replace('_', ' ', $item->payment_type) }}</div>
                                   @endif
                               </td>
                               <td class="px-3.5 py-2.5 text-center space-y-1 whitespace-nowrap">
                                   <span class="{{ $item->order_status_badge_class }} text-[9px] font-extrabold uppercase px-2.5 py-0.5 rounded-full border border-current inline-block text-center whitespace-nowrap">
                                       {{ $item->order_status_label }}
                                   </span>
                                   @if($item->shipment)
                                       <div class="text-[8px] text-slate-400 font-mono whitespace-nowrap">Resi: {{ $item->shipment->tracking_number }}</div>
                                       <div class="mt-0.5 whitespace-nowrap">
                                           <a href="{{ route('admin.shipping.shipments.label', $item->shipment->id) }}" target="_blank"
                                              class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[8px] font-extrabold px-2 py-0.5 rounded transition-all border border-slate-200 uppercase tracking-wide cursor-pointer whitespace-nowrap">
                                               <span>Cetak Label</span>
                                           </a>
                                       </div>
                                   @endif
                               </td>
                               <td class="p-4 text-center whitespace-nowrap">
                                    <div class="flex flex-col gap-1.5 items-center justify-center whitespace-nowrap">
                                        <!-- Contextual Automation Buttons -->
                                        @if(($item->order_status?->value ?? $item->order_status) === 'pending')
                                            <form action="{{ route('pesanan.update', [$item->id, 'tab' => 'diproses']) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="order_status" value="diproses">
                                                <button type="submit" class="btn-xs bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-lg px-3 py-1.5 transition-colors uppercase tracking-wider text-[9px] cursor-pointer shadow-sm">
                                                    Proses Pesanan
                                                </button>
                                            </form>
                                        @elseif(in_array($item->order_status?->value ?? $item->order_status, ['diproses', 'paid']))
                                            <form action="{{ route('pesanan.update', [$item->id, 'tab' => 'sedang_dicetak']) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="order_status" value="sedang_dicetak">
                                                <button type="submit" class="btn-xs bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-lg px-3 py-1.5 transition-colors uppercase tracking-wider text-[9px] cursor-pointer shadow-sm">
                                                    Siap Dicetak
                                                </button>
                                            </form>
                                        @elseif(($item->order_status?->value ?? $item->order_status) === 'sedang_dicetak')
                                            @if($item->shipping_courier === 'pickup')
                                                <form action="{{ route('pesanan.update', [$item->id, 'tab' => 'siap_dikemas']) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="order_status" value="siap_diambil">
                                                    <button type="submit" class="btn-xs bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold rounded-lg px-3 py-1.5 transition-colors uppercase tracking-wider text-[9px] cursor-pointer shadow-sm">
                                                        Siap Diambil
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('pesanan.update', [$item->id, 'tab' => 'siap_dikemas']) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="order_status" value="siap_dikemas">
                                                    <button type="submit" class="btn-xs bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold rounded-lg px-3 py-1.5 transition-colors uppercase tracking-wider text-[9px] cursor-pointer shadow-sm">
                                                        Produksi Selesai
                                                    </button>
                                                </form>
                                            @endif
                                        @elseif(($item->order_status?->value ?? $item->order_status) === 'siap_dikemas')
                                            @if($item->shipping_courier === 'pickup')
                                                <form action="{{ route('pesanan.update', [$item->id, 'tab' => 'siap_dikemas']) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="order_status" value="siap_diambil">
                                                    <button type="submit" class="btn-xs bg-purple-500 hover:bg-purple-600 text-white font-extrabold rounded-lg px-3 py-1.5 transition-colors uppercase tracking-wider text-[9px] cursor-pointer shadow-sm">
                                                        Siap Diambil
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('pesanan.update', [$item->id, 'tab' => 'dikemas']) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="order_status" value="dikemas">
                                                    <button type="submit" class="btn-xs bg-purple-500 hover:bg-purple-600 text-white font-extrabold rounded-lg px-3 py-1.5 transition-colors uppercase tracking-wider text-[9px] cursor-pointer shadow-sm">
                                                        Kemas Pesanan
                                                    </button>
                                                </form>
                                            @endif
                                        @elseif(($item->order_status?->value ?? $item->order_status) === 'dikemas')
                                            @if($item->shipping_courier === 'pickup')
                                                <form action="{{ route('pesanan.update', [$item->id, 'tab' => 'siap_dikemas']) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="order_status" value="siap_diambil">
                                                    <button type="submit" class="btn-xs bg-blue-500 hover:bg-blue-600 text-white font-extrabold rounded-lg px-3 py-1.5 transition-colors uppercase tracking-wider text-[9px] cursor-pointer shadow-sm">
                                                        Siap Diambil
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('pesanan.update', [$item->id, 'tab' => 'dikirim']) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="order_status" value="dikirim">
                                                    <button type="submit" class="btn-xs bg-blue-500 hover:bg-blue-600 text-white font-extrabold rounded-lg px-3 py-1.5 transition-colors uppercase tracking-wider text-[9px] cursor-pointer shadow-sm">
                                                        Kirim Pesanan
                                                    </button>
                                                </form>
                                            @endif
                                        @elseif(($item->order_status?->value ?? $item->order_status) === 'siap_diambil')
                                            <form action="{{ route('pesanan.update', [$item->id, 'tab' => 'selesai']) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="order_status" value="selesai">
                                                <button type="submit" class="btn-xs bg-green-500 hover:bg-green-600 text-white font-extrabold rounded-lg px-3 py-1.5 transition-colors uppercase tracking-wider text-[9px] cursor-pointer shadow-sm">
                                                    Sudah Diambil
                                                </button>
                                            </form>
                                        @elseif(($item->order_status?->value ?? $item->order_status) === 'dikirim')
                                            <form action="{{ route('pesanan.update', [$item->id, 'tab' => 'selesai']) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="order_status" value="selesai">
                                                <button type="submit" class="btn-xs bg-green-500 hover:bg-green-600 text-white font-extrabold rounded-lg px-3 py-1.5 transition-colors uppercase tracking-wider text-[9px] cursor-pointer shadow-sm">
                                                    Selesaikan
                                                </button>
                                            </form>
                                        @endif
 
                                        <!-- Legacy select selector for backup purposes/owner manual updates -->
                                        <form action="{{ route('pesanan.update', $item->id) }}" method="POST" class="inline-block mt-1">
                                            @csrf
                                            @method('PUT')
                                            <select name="order_status" onchange="this.form.action += '?tab=' + (this.value === 'paid' ? 'diproses' : this.value); this.form.submit()" class="text-[8px] border-slate-200 rounded-md py-0.5 px-1 font-bold text-slate-500 focus:ring-0 cursor-pointer">
                                                <option value="" disabled selected>Manual Ubah</option>
                                                <option value="pending">Menunggu</option>
                                                <option value="diproses">Diproses</option>
                                                <option value="sedang_dicetak">Dicetak</option>
                                                <option value="siap_dikemas">Siap Kemas</option>
                                                <option value="dikemas">Dikemas</option>
                                                <option value="dikirim">Dikirim</option>
                                                <option value="selesai">Selesai</option>
                                                <option value="dibatalkan">Batal</option>
                                            </select>
                                        </form>
                                    </div>
                               </td>
                           </tr>
                       @empty
                           <tr>
                               <td colspan="7">
                                   <div class="empty-state py-12">
                                       <div class="empty-state-icon">
                                           <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                           </svg>
                                       </div>
                                       <p class="text-sm font-semibold text-slate-700">Belum ada pesanan pada tahap ini</p>
                                       <p class="text-xs text-slate-400 mt-1">Seluruh pesanan akan diproses otomatis mengikuti alur kerja percetakan.</p>
                                   </div>
                               </td>
                           </tr>
                       @endforelse
                   </tbody>
               </table>
           </div>
       </div>

       <!-- Inline Preview & Printing Modal -->
       <div x-show="previewOpen" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            x-transition
            x-cloak>
           <div class="bg-white rounded-3xl w-full max-w-4xl shadow-2xl border border-slate-150 overflow-hidden flex flex-col h-[85vh]"
                @click.away="previewOpen = false">
               
               <!-- Modal Header -->
               <div class="bg-slate-900 text-white p-5 flex justify-between items-center shrink-0">
                   <div>
                       <h3 class="font-bold text-[10px] uppercase tracking-wider text-orange-400">Preview File Desain</h3>
                       <h2 class="text-sm font-bold mt-0.5" x-text="activeInvoice"></h2>
                   </div>
                   <div class="flex items-center gap-3">
                       <!-- Zoom Controls -->
                       <div class="flex items-center bg-slate-800 rounded-xl px-2.5 py-1 gap-1">
                           <button @click="zoomLevel = Math.max(25, zoomLevel - 15)" class="text-slate-350 hover:text-white px-2 py-0.5 font-bold text-sm cursor-pointer">-</button>
                           <span class="text-[10px] font-mono font-bold w-12 text-center text-orange-400" x-text="zoomLevel + '%'"></span>
                           <button @click="zoomLevel = Math.min(300, zoomLevel + 15)" class="text-slate-350 hover:text-white px-2 py-0.5 font-bold text-sm cursor-pointer">+</button>
                           <button @click="zoomLevel = 100" class="text-slate-400 hover:text-white text-[9px] uppercase px-1 font-bold ml-1 cursor-pointer">Reset</button>
                       </div>

                       <!-- Print Button -->
                       <button @click="triggerPrint(activeOrderId, activeFileUrl, 'print', activeType, activeInvoice)" class="bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-xl px-4 py-1.5 text-[10px] uppercase tracking-wider transition-all shadow-md cursor-pointer">
                           🖨️ Cetak Desain
                       </button>
                       <button @click="previewOpen = false" class="text-slate-400 hover:text-white text-xl font-bold ml-2 cursor-pointer">&times;</button>
                   </div>
               </div>
               
               <!-- Preview Area -->
               <div class="flex-1 overflow-auto bg-slate-150 flex items-center justify-center p-6 relative">
                   <!-- If PDF -->
                   <template x-if="activeType === 'pdf'">
                       <iframe :src="activeFileUrl" class="w-full h-full border-0 rounded-2xl bg-white transition-all duration-300 origin-center"
                               :style="'transform: scale(' + zoomLevel/100 + ');'"></iframe>
                   </template>

                   <!-- If Image -->
                   <template x-if="activeType !== 'pdf'">
                       <img :src="activeFileUrl" class="max-w-full max-h-full object-contain rounded-2xl shadow-lg transition-all duration-300 origin-center"
                            :style="'transform: scale(' + zoomLevel/100 + ');'">
                   </template>
               </div>
           </div>
       </div>

   </div>
</x-app-layout>