<div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden flex flex-col divide-y divide-slate-100 transition-all duration-300 hover:shadow-md">
   
   <!-- Card Header (Toko, Invoice & Status) -->
   <div class="p-4 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
       <div class="flex items-center gap-2">
           <div class="w-7 h-7 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
           </div>
           <h4 class="text-xs font-bold text-slate-800">Fadilah Digital Printing</h4>
           <span class="text-[10px] text-slate-400 font-mono">#{{ $order->invoice_number }}</span>
       </div>
       <div class="flex items-center gap-2">
           <span class="text-[10px] text-slate-400 font-semibold">{{ $order->created_at->format('d M Y H:i') }}</span>
           <span class="{{ $order->order_status_badge_class }} text-[9px] font-black uppercase px-2.5 py-0.5 rounded-full border border-current">
               {{ $order->order_status_label }}
           </span>
       </div>
   </div>

   <!-- Card Body (Order Items Loop) -->
   <div class="p-4 space-y-3.5">
       @foreach($order->orderDetails as $det)
           @php $isCustom = $det->product?->isCustom() ?? true; @endphp
           <div class="flex gap-4">
               <!-- Product Thumbnail -->
               <div class="w-16 h-16 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center relative shadow-inner">
                   @if($det->product && $det->product->image)
                       <img src="{{ Storage::url($det->product->image) }}" class="w-full h-full object-cover">
                   @else
                       <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                   @endif
               </div>

               <!-- Product Info & Specifications -->
               <div class="min-w-0 flex-1 space-y-1">
                   <div class="flex items-start justify-between gap-4">
                       <h4 class="text-xs font-bold text-slate-800 truncate">{{ $det->product?->product_name ?? 'Produk Percetakan' }}</h4>
                       <span class="text-xs font-bold text-slate-800 flex-shrink-0">Rp{{ number_format($det->subtotal / max(1, $det->qty), 0, ',', '.') }}</span>
                   </div>
                   
                   <div class="flex flex-wrap items-center gap-1.5 text-[9px] text-slate-400 font-bold uppercase">
                       <span>{{ $det->product?->category->category_name ?? 'Kategori Umum' }}</span>
                       <span>&middot;</span>
                       <span>Qty: {{ $det->qty }}</span>
                       <span>&middot;</span>
                       @if($isCustom)
                           <span class="text-orange-500 font-extrabold bg-orange-50 border border-orange-200/50 px-2 py-0.5 rounded-full text-[8px]">Custom Print</span>
                       @else
                           <span class="text-emerald-500 font-extrabold bg-emerald-50 border border-emerald-200/50 px-2 py-0.5 rounded-full text-[8px]">Ready Stock</span>
                       @endif
                   </div>

                   <!-- Custom Printing Form Details (Custom only) -->
                   @if($isCustom)
                       <div class="bg-slate-50/70 p-3 rounded-2xl border border-slate-200/50 text-[10px] text-slate-500 grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-6 mt-3">
                           <div><span class="font-bold text-slate-400 uppercase text-[8px] block">Ukuran</span> {{ $det->ukuran ?: '-' }}</div>
                           <div><span class="font-bold text-slate-400 uppercase text-[8px] block">Bahan</span> {{ $det->bahan ?: '-' }}</div>
                           <div><span class="font-bold text-slate-400 uppercase text-[8px] block">Finishing</span> {{ $det->finishing ?: '-' }}</div>
                           <div><span class="font-bold text-slate-400 uppercase text-[8px] block">Catatan Cetak</span> <span class="italic text-slate-500">{{ $det->notes ?: 'Tidak ada catatan khusus.' }}</span></div>
                           
                           @php
                               $designVerification = 'Menunggu Pembayaran';
                               $verifColor = 'text-amber-500 bg-amber-50 border-amber-200';
                               if ($order->isPaid()) {
                                   $designVerification = 'Disetujui / Siap Cetak';
                                   $verifColor = 'text-green-600 bg-green-50 border-green-200';
                               } elseif (($order->order_status?->value ?? $order->order_status) === 'dibatalkan') {
                                   $designVerification = 'Dibatalkan';
                                   $verifColor = 'text-rose-600 bg-rose-50 border-rose-200';
                               }
                           @endphp
                           <div class="md:col-span-2 pt-2 border-t border-slate-200/60 grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                               <div>
                                   <span class="font-bold text-slate-400 uppercase text-[8px] block">Status Desain</span>
                                   <span class="inline-flex items-center gap-1 mt-0.5 text-[8px] font-black uppercase px-2 py-0.5 rounded-full border {{ $verifColor }}">
                                       {{ $designVerification }}
                                   </span>
                               </div>
                               
                               @if($det->design_file)
                                   <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                       <!-- Preview desain kecil jika bertipe image -->
                                       @if(preg_match('/\.(jpg|jpeg|png|webp)$/i', $det->design_file))
                                           <a href="{{ Storage::url($det->design_file) }}" target="_blank" class="w-8 h-8 rounded-lg border border-slate-200 overflow-hidden flex-shrink-0 block shadow-2xs hover:scale-105 transition-transform">
                                               <img src="{{ Storage::url($det->design_file) }}" class="w-full h-full object-cover">
                                           </a>
                                       @endif
                                       <a href="{{ Storage::url($det->design_file) }}" target="_blank" class="text-orange-500 hover:text-orange-600 font-bold hover:underline flex items-center gap-1 text-[9px] bg-white border border-slate-200 px-2.5 py-1 rounded-xl shadow-2xs">
                                           <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                           Unduh Desain
                                       </a>
                                   </div>
                               @else
                                   <div class="text-[9px] text-slate-400 italic sm:text-right">File desain belum diunggah</div>
                               @endif
                           </div>
                       </div>
                   @endif
               </div>
           </div>
       @endforeach

       {{-- Shipping Address Snapshot (Customer View) --}}
       @if($order->receiver_name)
           <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-200/50 text-[10px] text-slate-555 mt-2 space-y-3">
               <div class="flex items-center gap-1.5 font-bold text-slate-700">
                   <span>📍 Alamat Pengiriman:</span>
                   <span class="text-slate-800 font-black">{{ $order->receiver_name }}</span>
                   <span>({{ $order->phone }})</span>
               </div>
               <p class="leading-relaxed font-semibold">{{ $order->full_address }} (No. {{ $order->no_rumah }}, RT {{ $order->rt }}/RW {{ $order->rw }}), {{ $order->subdistrict }}, {{ $order->district }}, {{ $order->city }}, {{ $order->province }} - {{ $order->postal_code }}</p>
               @if($order->patokan)
                   <p class="text-[9px] text-slate-400 font-semibold italic">📍 Patokan: {{ $order->patokan }}</p>
               @endif

               {{-- Shipping info --}}
                <div class="grid grid-cols-2 gap-4 border-t border-slate-200/60 pt-3 mt-1 font-semibold text-slate-650">
                    @if($order->shipping_courier === 'pickup')
                        <div>
                            <span class="block text-[8px] font-bold text-slate-400 uppercase">Metode Pengiriman</span>
                            <span class="font-extrabold text-slate-800 uppercase">Ambil di Toko</span>
                        </div>
                        <div>
                            <span class="block text-[8px] font-bold text-slate-400 uppercase">Ongkos Kirim</span>
                            <span class="font-extrabold text-slate-855">Gratis (Rp 0)</span>
                        </div>
                    @else
                        <div>
                            <span class="block text-[8px] font-bold text-slate-400 uppercase">Kurir & Layanan</span>
                            <span class="font-extrabold text-slate-800 uppercase">{{ $order->shipping_courier ?? '-' }}</span>
                            <span>({{ $order->shipping_service ?? '-' }})</span>
                        </div>
                        <div>
                            <span class="block text-[8px] font-bold text-slate-400 uppercase">Ongkos Kirim</span>
                            <span class="font-extrabold text-slate-850">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="block text-[8px] font-bold text-slate-400 uppercase">Estimasi</span>
                            <span class="text-slate-700">{{ $order->shipping_estimation ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-[8px] font-bold text-slate-400 uppercase">Nomor Resi / Status</span>
                            <span class="font-mono font-bold text-slate-800">{{ $order->tracking_number ?? 'Belum Tersedia' }}</span>
                            <div class="mt-0.5">
                                <span class="px-1.5 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-wider bg-orange-100 text-orange-600 border border-orange-200">
                                    {{ $order->shipping_status ?? 'pending' }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

               {{-- Timeline Tracking --}}
               @php
                   $tracking = null;
                   if ($order->shipping_snapshot) {
                       $tracking = json_decode($order->shipping_snapshot, true);
                   }
               @endphp

                @if($order->shipping_courier !== 'pickup' && $tracking && isset($tracking['history']) && count($tracking['history']) > 0)
                   <div class="border-t border-slate-200/60 pt-3 space-y-2">
                       <span class="block text-[8px] font-bold text-slate-400 uppercase">Pelacakan Paket</span>
                       <div class="relative border-l-2 border-slate-200 pl-3 space-y-3 ml-1 text-[9px]">
                           @foreach($tracking['history'] as $index => $hist)
                               <div class="relative">
                                   <div class="absolute -left-[18px] top-0.5 h-2 w-2 rounded-full {{ $index === count($tracking['history']) - 1 ? 'bg-orange-500 ring-2 ring-orange-100' : 'bg-slate-300' }}"></div>
                                   <div class="space-y-0.5">
                                       <div class="flex justify-between items-center font-bold text-slate-700">
                                           <span class="uppercase text-[8px] {{ $index === count($tracking['history']) - 1 ? 'text-orange-600 font-extrabold' : 'text-slate-800' }}">{{ $hist['status'] ?? 'dikirim' }}</span>
                                           <span class="text-[8px] text-slate-400 font-mono">{{ !empty($hist['time']) ? date('d M Y, H:i', strtotime($hist['time'])) : '' }}</span>
                                       </div>
                                       <p class="text-slate-500 font-semibold leading-relaxed">{{ $hist['note'] ?? '' }}</p>
                                   </div>
                               </div>
                           @endforeach
                       </div>
                   </div>
               @endif
           </div>
       @endif
   </div>

    <!-- Card Footer 1 (Progress Alur Produksi Horizontal) -->
    @if($order->shipping_courier === 'pickup')
        <div class="p-4 space-y-4 bg-slate-50/20">
            <div class="space-y-2">
                <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">Progress Pengambilan Pesanan</span>
                <div class="relative flex items-center justify-between text-[8px] font-black text-slate-400 uppercase tracking-wide">
                    
                    <!-- Progress Line Background -->
                    <div class="absolute left-0 right-0 top-1.5 -translate-y-1/2 h-1 bg-slate-200 -z-10 rounded"></div>
                    
                    @php
                         $progVal = 0;
                         $statusStr = $order->order_status?->value ?? $order->order_status;
                         if (in_array($statusStr, ['diproses', 'paid', 'sedang_dicetak', 'siap_dikemas', 'dikemas'])) {
                             $progVal = 33;
                         } elseif ($statusStr === 'siap_diambil') {
                             $progVal = 66;
                         } elseif ($statusStr === 'selesai') {
                             $progVal = 100;
                         }
                     @endphp
                    <div class="absolute left-0 top-1.5 -translate-y-1/2 h-1 bg-orange-500 -z-10 rounded transition-all duration-500" style="width: {{ $progVal }}%"></div>

                    <!-- Step 1: Menunggu Diambil -->
                    <div class="flex flex-col items-center gap-1">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[9px] {{ $progVal >= 33 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-slate-200 text-slate-400' }}">⏳</span>
                        <span class="text-[7.5px] scale-90 font-bold">Menunggu Diambil</span>
                    </div>
                    <!-- Step 2: Siap Diambil -->
                    <div class="flex flex-col items-center gap-1">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[9px] {{ $progVal >= 66 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-slate-200 text-slate-400' }}">🏪</span>
                        <span class="text-[7.5px] scale-90 font-bold">Siap Diambil</span>
                    </div>
                    <!-- Step 3: Sudah Diambil -->
                    <div class="flex flex-col items-center gap-1">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[9px] {{ $progVal >= 100 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-slate-200 text-slate-400' }}">🏁</span>
                        <span class="text-[7.5px] scale-90 font-bold">Sudah Diambil</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="p-4 space-y-4 bg-slate-50/20">
            <div class="space-y-2">
                <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">Progress Alur Produksi</span>
                <div class="relative flex items-center justify-between text-[8px] font-black text-slate-400 uppercase tracking-wide">
                    
                    <!-- Progress Line Background -->
                    <div class="absolute left-0 right-0 top-1.5 -translate-y-1/2 h-1 bg-slate-200 -z-10 rounded"></div>
                    
                    @php
                         $progVal = 0;
                         $statusStr = $order->order_status?->value ?? $order->order_status;
                         if ($statusStr === 'diproses' || $statusStr === 'paid') {
                             $progVal = 20;
                         } elseif ($statusStr === 'sedang_dicetak') {
                             $progVal = 40;
                         } elseif (in_array($statusStr, ['siap_dikemas', 'dikemas'])) {
                             $progVal = 60;
                         } elseif ($statusStr === 'dikirim') {
                             $progVal = 80;
                         } elseif ($statusStr === 'selesai') {
                             $progVal = 100;
                         }
                     @endphp
                    <div class="absolute left-0 top-1.5 -translate-y-1/2 h-1 bg-orange-500 -z-10 rounded transition-all duration-500" style="width: {{ $progVal }}%"></div>

                    <!-- Step 1: Diproses -->
                    <div class="flex flex-col items-center gap-1">
                        <span class="text-[7.5px] scale-90 font-bold">Diproses</span>
                    </div>
                    <!-- Step 2: Sedang Dicetak -->
                    <div class="flex flex-col items-center gap-1">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[9px] {{ $progVal >= 40 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-slate-200 text-slate-400' }}">🖨️</span>
                        <span class="text-[7.5px] scale-90 font-bold">Dicetak</span>
                    </div>
                    <!-- Step 3: Dikemas -->
                    <div class="flex flex-col items-center gap-1">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[9px] {{ $progVal >= 60 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-slate-200 text-slate-400' }}">📦</span>
                        <span class="text-[7.5px] scale-90 font-bold">Dikemas</span>
                    </div>
                    <!-- Step 4: Dikirim -->
                    <div class="flex flex-col items-center gap-1">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[9px] {{ $progVal >= 80 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-slate-200 text-slate-400' }}">🚚</span>
                        <span class="text-[7.5px] scale-90 font-bold">Dikirim</span>
                    </div>
                    <!-- Step 5: Selesai -->
                    <div class="flex flex-col items-center gap-1">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[9px] {{ $progVal >= 100 ? 'bg-orange-500 text-white shadow-3xs' : 'bg-slate-200 text-slate-400' }}">🏁</span>
                        <span class="text-[7.5px] scale-90 font-bold">Selesai</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

   <!-- Card Footer 2 (Total, Ringkasan Pembayaran & Tombol Aksi) -->
   <div class="p-4 flex flex-wrap items-center justify-between gap-4 bg-slate-50/10">
       <!-- Ringkasan Bayar -->
       <div class="text-[10px] text-slate-400 font-semibold">
           <span>Total Pembayaran:</span>
           <span class="text-sm font-black text-orange-500 ml-1">Rp{{ number_format($order->total_price, 0, ',', '.') }}</span>
       </div>

       <!-- Tombol Aksi Kontekstual -->
        <div class="flex items-center gap-2">
            <!-- 1. Belum Bayar -->
            @if($order->payment_status === 'pending' && ($order->order_status?->value ?? $order->order_status) !== 'dibatalkan')
                <a href="{{ route('payment.pay', $order->id) }}" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-xl shadow-md transition-all active:scale-[0.98]">
                    Bayar Sekarang
                </a>
                <form action="{{ route('pesanan.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-sm bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl border border-slate-200/60">
                        Batalkan
                    </button>
                </form>
            @endif

            <!-- 2. Diproses / Dicetak / Packing / Dikemas -->
            @if(in_array($order->order_status?->value ?? $order->order_status, ['paid', 'diproses', 'sedang_dicetak', 'siap_dikemas', 'dikemas']))
                <a href="{{ route('chat.index') }}?order_id={{ $order->id }}" class="btn-sm bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl border border-slate-200/60 flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Hubungi Admin
                </a>
            @endif

            <!-- 3. Dikirim / Transit / Delivered -->
            @if(($order->order_status?->value ?? $order->order_status) === 'dikirim')
                <a href="{{ route('pesanan.track', $order->id) }}" class="btn-sm bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl border border-slate-200/60">
                    Lacak Pengiriman
                </a>
                
                <form action="{{ route('pesanan.terima', $order->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin pesanan sudah sampai dan diterima dengan baik?')">
                    @csrf
                    <button type="submit" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-xl shadow-md transition-all active:scale-[0.98] cursor-pointer">
                        Pesanan Diterima
                    </button>
                </form>
            @endif

             <!-- 3.b Pickup Order is Ready to Collect -->
             @if($order->shipping_courier === 'pickup' && ($order->order_status?->value ?? $order->order_status) === 'siap_diambil')
                 <form action="{{ route('pesanan.terima', $order->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin sudah mengambil pesanan Anda di toko?')">
                     @csrf
                     <button type="submit" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-xl shadow-md transition-all active:scale-[0.98] cursor-pointer">
                         Saya Sudah Mengambil Pesanan
                     </button>
                 </form>
             @endif

            <!-- 4. Selesai (Invoice & Review) -->
            @if(($order->order_status?->value ?? $order->order_status) === 'selesai')
                <a href="{{ route('pesanan.cetak', $order->id) }}" target="_blank" class="btn-sm bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl border border-slate-200/60">
                    Invoice
                </a>

                @if($order->claim)
                    <span class="px-2.5 py-1.5 rounded-xl text-3xs font-extrabold border uppercase tracking-wider inline-flex items-center gap-1
                        {{ $order->claim->status === 'pending' ? 'bg-amber-50 text-amber-600 border-amber-200' : '' }}
                        {{ $order->claim->status === 'approved' ? 'bg-green-50 text-green-600 border-green-200' : '' }}
                        {{ $order->claim->status === 'rejected' ? 'bg-rose-50 text-rose-600 border-rose-200' : '' }}
                    ">
                        @if($order->claim->status === 'pending')
                            ⏳ Garansi: Pending
                        @elseif($order->claim->status === 'approved')
                            ✅ Garansi: Disetujui (Refund)
                        @else
                            ❌ Garansi: Ditolak
                        @endif
                    </span>
                @else
                    <a href="{{ route('pesanan.claim.create', $order->id) }}" class="btn-sm bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold rounded-xl border border-rose-200 text-3xs flex items-center gap-1 cursor-pointer">
                        ⚠️ Ajukan Garansi / Komplain
                    </a>
                @endif
                
                <!-- Review logic per order item -->
                @foreach($order->orderDetails as $det)
                    @php
                        $hasReview = $order->reviews->where('product_id', $det->product_id)->first();
                    @endphp
                    @if(!$hasReview)
                        <button @click="activeOrder = {{ $order->id }}; activeProduct = {{ $det->product_id }}; reviewModalOpen = true" 
                                class="btn-sm bg-orange-50 hover:bg-orange-100 text-orange-600 font-bold rounded-xl border border-orange-200 text-3xs cursor-pointer">
                            Ulas {{ $det->product?->product_name }}
                        </button>
                    @else
                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-emerald-600 bg-emerald-50 px-2 py-1 rounded-xl border border-emerald-200/50 cursor-default">
                            ✓ Ulasan Terkirim
                        </span>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div>
