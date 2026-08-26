<x-app-layout>
   <x-slot name="header">
       <div class="page-header mb-0">
           <div class="flex items-center gap-3">
               <a href="{{ route('pesanan.index') }}" class="btn-icon btn-ghost">
                   <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                   </svg>
               </a>
               <div>
                   <h1 class="page-title">Detail Pesanan</h1>
                   <p class="page-subtitle">Invoice: {{ $pesanan->invoice_number }}</p>
               </div>
           </div>
       </div>
   </x-slot>

   @if(session('success'))
       <div class="alert-success mb-5 animate-fade-in">
           <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
           <span>{{ session('success') }}</span>
       </div>
   @endif

   <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
       
       {{-- Kiri: Rincian Pesanan & Status (Col 2) --}}
       <div class="lg:col-span-2 space-y-6">
           
           {{-- List Produk Dipesan --}}
           <div class="card">
               <div class="card-header bg-slate-900 text-white rounded-t-2xl">
                   <h3 class="text-sm font-bold uppercase tracking-wider text-orange-400">Rincian Produk Dipesan</h3>
               </div>
               <div class="card-body p-0 divide-y divide-slate-100">
                   @foreach($pesanan->orderDetails as $detail)
                       <div class="p-5 flex items-start gap-4">
                           <div class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                               @if($detail->product?->image)
                                   <img src="{{ asset('storage/' . $detail->product->image) }}" class="w-full h-full object-cover">
                               @else
                                   <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                                   </svg>
                               @endif
                           </div>
                           <div class="flex-1 min-w-0">
                               <h4 class="font-bold text-slate-800 text-sm truncate">{{ $detail->product?->product_name ?? 'Produk Percetakan' }}</h4>
                               <p class="text-xs text-slate-400 mt-1">
                                   {{ $detail->qty }} &times; Rp {{ number_format($detail->subtotal / max($detail->qty, 1), 0, ',', '.') }}
                               </p>

                               {{-- Pilihan Variasi --}}
                               @if($detail->custom_length || $detail->custom_width || $detail->ukuran || $detail->bahan || $detail->finishing || $detail->custom_text)
                                   <div class="mt-2.5 flex flex-wrap gap-1.5 text-[10px] text-slate-500 font-semibold">
                                       @if($detail->custom_length && $detail->custom_width)
                                           <span class="bg-orange-50 text-orange-700 border border-orange-200 px-2 py-0.5 rounded-lg">
                                               Ukuran Kustom: {{ number_format($detail->custom_length, 2) }}m &times; {{ number_format($detail->custom_width, 2) }}m ({{ number_format($detail->custom_area ?: ($detail->custom_length * $detail->custom_width), 2) }} m²)
                                               @if($detail->price_per_m2) @ Rp{{ number_format($detail->price_per_m2, 0, ',', '.') }}/m² @endif
                                           </span>
                                       @endif
                                       @if($detail->ukuran) <span class="bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">Ukuran: {{ $detail->ukuran }}</span> @endif
                                       @if($detail->bahan) <span class="bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">Bahan: {{ $detail->bahan }}</span> @endif
                                       @if($detail->finishing) <span class="bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">Finishing: {{ $detail->finishing }}</span> @endif
                                       @if($detail->custom_text) <span class="bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">Teks Custom: "{{ $detail->custom_text }}"</span> @endif
                                   </div>
                               @endif

                               {{-- File Desain --}}
                               @if($detail->design_file)
                                   <div class="mt-3">
                                       <a href="{{ str_starts_with($detail->design_file, '/storage/') ? asset($detail->design_file) : asset('storage/' . $detail->design_file) }}" 
                                          target="_blank" 
                                          class="btn-xs btn-primary inline-flex text-3xs font-bold uppercase tracking-wider py-1 px-2.5">
                                           <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                           Unduh File Desain
                                       </a>
                                   </div>
                               @endif
                           </div>
                           <div class="text-right font-black text-slate-800 text-sm flex-shrink-0">
                               Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                           </div>
                       </div>
                   @endforeach
               </div>
           </div>

           {{-- Informasi Pemesan --}}
           <div class="card p-5 space-y-4">
               <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider border-b border-slate-100 pb-2">Informasi Pelanggan</h3>
               <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                   <div class="bg-slate-50/50 p-3.5 rounded-xl border border-slate-200/50">
                       <span class="block text-[9px] font-bold text-slate-400 uppercase">Nama</span>
                       <span class="font-semibold text-slate-800 block mt-1">{{ $pesanan->user->name ?? 'Guest' }}</span>
                   </div>
                   <div class="bg-slate-50/50 p-3.5 rounded-xl border border-slate-200/50">
                       <span class="block text-[9px] font-bold text-slate-400 uppercase">Email</span>
                       <span class="font-semibold text-slate-800 block mt-1">{{ $pesanan->user->email ?? '-' }}</span>
                   </div>
                   <div class="bg-slate-50/50 p-3.5 rounded-xl border border-slate-200/50">
                       <span class="block text-[9px] font-bold text-slate-400 uppercase">No WhatsApp</span>
                       <span class="font-semibold text-slate-800 block mt-1">{{ $pesanan->user->phone_number ?? '-' }}</span>
                   </div>
               </div>

               @if($pesanan->notes)
                   <div class="bg-yellow-50/50 border border-yellow-200/60 p-4 rounded-xl text-xs text-yellow-800">
                       <span class="block text-[9px] font-bold text-yellow-600 uppercase mb-1">Catatan Tambahan Pelanggan:</span>
                       "{{ $pesanan->notes }}"
                   </div>
               @endif
           </div>

           {{-- File Desain Order Level --}}
           <div class="card p-5 space-y-4">
               <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-1.5">
                   <span>🎨</span> File Desain Utama
               </h3>
               @if($pesanan->design_file)
                   <div class="flex items-center gap-2">
                       <a href="{{ str_starts_with($pesanan->design_file, '/storage/') ? asset($pesanan->design_file) : asset('storage/' . $pesanan->design_file) }}" 
                          target="_blank" 
                          class="btn-xs btn-primary inline-flex text-3xs font-bold uppercase tracking-wider py-1.5 px-3">
                           Lihat File
                       </a>
                       <a href="{{ str_starts_with($pesanan->design_file, '/storage/') ? asset($pesanan->design_file) : asset('storage/' . $pesanan->design_file) }}" 
                          download
                          class="btn-xs btn-secondary inline-flex text-3xs font-bold uppercase tracking-wider py-1.5 px-3 border border-slate-200 hover:bg-slate-50">
                           Download File
                       </a>
                   </div>
               @else
                   <p class="text-xs text-slate-500 italic">Tidak ada file desain.</p>
               @endif
           </div>

           {{-- Alamat & Detail Pengiriman --}}
           @if($pesanan->receiver_name)
               <div class="card p-5 space-y-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
                   <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-1.5">
                       <span>📍</span> Alamat & Detail Pengiriman
                   </h3>
                   <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-200/50 text-xs space-y-3">
                       {{-- Identitas Penerima --}}
                       <div class="flex items-center flex-wrap gap-2">
                           <span class="font-bold text-slate-800">{{ $pesanan->receiver_name }}</span>
                           <span class="text-slate-500 font-semibold">({{ $pesanan->phone }})</span>
                           @if($pesanan->address_label)
                               <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">{{ $pesanan->address_label }}</span>
                           @endif
                       </div>
                       
                       {{-- Alamat Fisik --}}
                       <p class="text-slate-600 leading-relaxed font-semibold">
                           {{ $pesanan->full_address }} (No. {{ $pesanan->no_rumah }}, RT {{ $pesanan->rt }}/RW {{ $pesanan->rw }}), {{ $pesanan->subdistrict }}, {{ $pesanan->district }}, {{ $pesanan->city }}, {{ $pesanan->province }} - {{ $pesanan->postal_code }}
                       </p>
                       @if($pesanan->patokan)
                           <p class="text-[10px] text-slate-400 font-semibold italic">📍 Patokan: {{ $pesanan->patokan }}</p>
                       @endif

                       {{-- Info Kurir & Ongkir --}}
                       <div class="grid grid-cols-2 gap-4 border-t border-slate-200/60 pt-3 mt-1 text-[11px]">
                           <div>
                               <span class="block text-[9px] font-bold text-slate-450 uppercase">Kurir / Layanan</span>
                               <span class="font-bold text-slate-800 uppercase">{{ $pesanan->shipping_courier ?? '-' }}</span>
                               <span class="text-slate-500 font-bold">({{ $pesanan->shipping_service ?? '-' }})</span>
                           </div>
                           <div>
                               <span class="block text-[9px] font-bold text-slate-450 uppercase">Ongkos Kirim</span>
                               <span class="font-bold text-slate-800">Rp {{ number_format($pesanan->shipping_cost, 0, ',', '.') }}</span>
                           </div>
                           <div>
                               <span class="block text-[9px] font-bold text-slate-450 uppercase">Estimasi Waktu</span>
                               <span class="font-semibold text-slate-600">{{ $pesanan->shipping_estimation ?? '-' }}</span>
                           </div>
                           <div>
                               <span class="block text-[9px] font-bold text-slate-450 uppercase">Nomor Resi / Status</span>
                               @if($pesanan->shipment)
                                   <span class="font-mono font-bold text-slate-800">{{ $pesanan->shipment->tracking_number }}</span>
                                   <div class="mt-0.5">
                                       <span class="px-1.5 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-wider bg-orange-100 text-orange-600 border border-orange-200">
                                           {{ $pesanan->shipment->status }}
                                       </span>
                                   </div>
                               @else
                                   <span class="font-mono font-bold text-slate-800">{{ $pesanan->tracking_number ?? 'Belum Tersedia' }}</span>
                                   <div class="mt-0.5">
                                       <span class="px-1.5 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-wider bg-orange-100 text-orange-600 border border-orange-200">
                                           {{ $pesanan->shipping_status ?? 'pending' }}
                                       </span>
                                   </div>
                               @endif
                           </div>
                       </div>

                       @if($pesanan->shipment)
                           <div class="pt-3">
                               <a href="{{ route('pesanan.shipments.label', $pesanan->shipment->id) }}" target="_blank"
                                  class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold text-[9px] uppercase tracking-wider transition-all shadow-sm cursor-pointer">
                                   <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                   </svg>
                                   Download Label Pengiriman
                               </a>
                           </div>
                       @endif
                   </div>

                   {{-- Timeline Tracking Pengiriman --}}
                   @if($pesanan->shipment && $pesanan->shipment->trackings->count() > 0)
                       <div class="border-t border-slate-100 pt-4 space-y-3">
                           <h4 class="font-bold text-slate-800 text-[10px] uppercase tracking-wider flex items-center gap-1.5">
                               <span>📦</span> Status Pelacakan Pengiriman
                           </h4>
                           <div class="relative border-l-2 border-slate-200 pl-4 space-y-4 ml-1.5 text-[10px]">
                               @foreach($pesanan->shipment->trackings->sortByDesc('id') as $index => $track)
                                   <div class="relative">
                                       <div class="absolute -left-[23px] top-0.5 h-2.5 w-2.5 rounded-full {{ $loop->first ? 'bg-orange-500 ring-4 ring-orange-100' : 'bg-slate-300' }}"></div>
                                       <div class="space-y-0.5">
                                           <div class="flex justify-between items-center font-bold text-slate-700">
                                               <span class="uppercase text-[9px] {{ $loop->first ? 'text-orange-600' : 'text-slate-800' }}">{{ $track->status }}</span>
                                               <span class="text-[8px] text-slate-400 font-mono">{{ $track->created_at->format('d M Y, H:i') }}</span>
                                           </div>
                                           <p class="text-slate-500 font-medium leading-relaxed">{{ $track->description }}</p>
                                           @if($track->location)
                                               <p class="text-[9px] text-slate-400 font-semibold">Lokasi: {{ $track->location }}</p>
                                           @endif
                                       </div>
                                   </div>
                               @endforeach
                           </div>
                       </div>
                   @else
                       @php
                           $tracking = null;
                           if ($pesanan->shipping_snapshot) {
                               $tracking = json_decode($pesanan->shipping_snapshot, true);
                           }
                       @endphp

                       @if($tracking && isset($tracking['history']) && count($tracking['history']) > 0)
                           <div class="border-t border-slate-100 pt-4 space-y-3">
                               <h4 class="font-bold text-slate-800 text-[10px] uppercase tracking-wider flex items-center gap-1.5">
                                   <span>📦</span> Histori Status Pelacakan
                               </h4>
                               <div class="relative border-l-2 border-slate-200 pl-4 space-y-4 ml-1.5 text-[10px]">
                                   @foreach($tracking['history'] as $index => $hist)
                                       <div class="relative">
                                           <div class="absolute -left-[23px] top-0.5 h-2.5 w-2.5 rounded-full {{ $index === count($tracking['history']) - 1 ? 'bg-orange-500 ring-4 ring-orange-100' : 'bg-slate-300' }}"></div>
                                           <div class="space-y-0.5">
                                               <div class="flex justify-between items-center font-bold text-slate-700">
                                                   <span class="uppercase text-[9px] {{ $index === count($tracking['history']) - 1 ? 'text-orange-600' : 'text-slate-800' }}">{{ $hist['status'] ?? 'dikirim' }}</span>
                                                   <span class="text-[8px] text-slate-400 font-mono">{{ !empty($hist['time']) ? date('d M Y, H:i', strtotime($hist['time'])) : '' }}</span>
                                               </div>
                                               <p class="text-slate-500 font-medium leading-relaxed">{{ $hist['note'] ?? '' }}</p>
                                           </div>
                                       </div>
                                   @endforeach
                               </div>
                           </div>
                       @endif
                   @endif
               </div>
           @endif

       </div>

       {{-- Kanan: Aksi & Update Status (Col 1) --}}
       <div class="lg:col-span-1 space-y-6">
           
           {{-- Update Status Panel --}}
           <div class="card p-5 space-y-4">
               <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider border-b border-slate-100 pb-2">Manajemen Status</h3>
               
               <form action="{{ route('pesanan.update', $pesanan->id) }}" method="POST" class="space-y-4 text-xs">
                   @csrf
                   @method('PUT')
                   
                   {{-- Status Pembayaran (Read-only) --}}
                   <div>
                       <span class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Status Pembayaran</span>
                       @php
                           $psMap = [
                               'pending'    => ['label' => 'Belum Bayar', 'class' => 'status-unpaid'],
                               'settlement' => ['label' => 'Lunas',       'class' => 'status-paid'],
                               'capture'    => ['label' => 'Lunas',       'class' => 'status-paid'],
                               'deny'       => ['label' => 'Ditolak',     'class' => 'status-failed'],
                               'cancel'     => ['label' => 'Batal',       'class' => 'status-failed'],
                               'expire'     => ['label' => 'Kedaluwarsa', 'class' => 'status-expired'],
                           ];
                           $ps = $psMap[$pesanan->payment_status] ?? ['label' => ucfirst($pesanan->payment_status ?? '-'), 'class' => 'badge-neutral'];
                       @endphp
                       <span class="{{ $ps['class'] }} inline-block mt-0.5">
                           {{ $ps['label'] }}
                       </span>
                       @if($pesanan->payment_type)
                           <div class="text-[10px] text-slate-400 mt-1 capitalize">Metode: {{ str_replace('_', ' ', $pesanan->payment_type) }}</div>
                       @endif
                       @if($pesanan->paid_at)
                           <div class="text-[10px] text-slate-400 mt-0.5">Waktu: {{ $pesanan->paid_at->format('d M Y, H:i') }}</div>
                       @endif
                   </div>

                   {{-- Dropdown Status Pesanan --}}
                   <div class="form-group">
                       <label class="form-label text-[9px] font-bold text-slate-400 uppercase">Status Pesanan</label>
                       <select name="order_status" onchange="this.form.submit()" class="form-select text-xs w-full mt-1.5 rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500">
                           <option value="pending"    {{ ($pesanan->order_status instanceof \App\Enums\OrderStatus ? $pesanan->order_status->value : $pesanan->order_status) == 'pending'    ? 'selected' : '' }}>Menunggu</option>
                           <option value="diproses"   {{ ($pesanan->order_status instanceof \App\Enums\OrderStatus ? $pesanan->order_status->value : $pesanan->order_status) == 'diproses'   ? 'selected' : '' }}>Diproses</option>
                           <option value="selesai"    {{ ($pesanan->order_status instanceof \App\Enums\OrderStatus ? $pesanan->order_status->value : $pesanan->order_status) == 'selesai'    ? 'selected' : '' }}>Selesai</option>
                           <option value="dibatalkan" {{ ($pesanan->order_status instanceof \App\Enums\OrderStatus ? $pesanan->order_status->value : $pesanan->order_status) == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                       </select>
                   </div>

                   {{-- Dropdown Status Produksi --}}
                   <div class="form-group">
                       <label class="form-label text-[9px] font-bold text-slate-400 uppercase">Status Produksi / Tracking</label>
                       <select name="tracking_status" onchange="this.form.submit()" class="form-select text-xs w-full mt-1.5 rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500">
                           <option value="pending"      {{ $pesanan->tracking_status == 'pending'      ? 'selected' : '' }}>Menunggu</option>
                           <option value="antrian"      {{ $pesanan->tracking_status == 'antrian'      ? 'selected' : '' }}>Antrian Cetak</option>
                           <option value="diproduksi"   {{ $pesanan->tracking_status == 'diproduksi'   ? 'selected' : '' }}>Dicetak</option>
                           <option value="siap_diambil" {{ $pesanan->tracking_status == 'siap_diambil' ? 'selected' : '' }}>Siap Diambil/Kirim</option>
                           <option value="dikirim"      {{ $pesanan->tracking_status == 'dikirim'      ? 'selected' : '' }}>Dikirim</option>
                           <option value="selesai"      {{ $pesanan->tracking_status == 'selesai'      ? 'selected' : '' }}>Selesai</option>
                           <option value="dibatalkan"   {{ $pesanan->tracking_status == 'dibatalkan'   ? 'selected' : '' }}>Batal</option>
                       </select>
                   </div>

                   {{-- Input Resi Pengiriman --}}
                   @if($pesanan->tracking_status == 'dikirim')
                       <div class="form-group">
                           <label class="form-label text-[9px] font-bold text-slate-400 uppercase">Nomor Resi</label>
                           <input type="text" name="tracking_resi" value="{{ $pesanan->tracking_resi }}" 
                                  placeholder="Input Resi..." 
                                  onchange="this.form.submit()"
                                  class="form-input text-xs w-full mt-1.5 rounded-xl border-slate-200 focus:border-orange-500 focus:ring-orange-500">
                       </div>
                   @endif
               </form>
           </div>

           {{-- Ringkasan Tagihan & Print Action --}}
           <div class="card p-5 space-y-4">
               <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider border-b border-slate-100 pb-2">Total Tagihan</h3>
               <div class="flex justify-between items-center text-sm">
                   <span class="font-semibold text-slate-500">Grand Total:</span>
                   <span class="font-black text-base text-slate-900">
                       Rp {{ number_format($pesanan->total_price, 0, ',', '.') }}
                   </span>
               </div>

               <div class="divider"></div>

               <div class="space-y-2">
                   <a href="{{ route('pesanan.cetak', $pesanan->id) }}" 
                      target="_blank" 
                      class="w-full btn-md btn-secondary justify-center text-xs font-bold uppercase tracking-wider">
                       <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                       Cetak Nota Transaksi
                   </a>
               </div>
           </div>

       </div>

   </div>
</x-app-layout>
