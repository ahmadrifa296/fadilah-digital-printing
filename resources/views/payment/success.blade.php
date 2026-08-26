<x-app-layout>
   <x-slot name="header">
       <div class="page-header mb-0">
           <div>
               <h1 class="page-title">Status Transaksi</h1>
               <p class="page-subtitle">Informasi hasil transaksi pembayaran Anda</p>
           </div>
           <a href="{{ route('dashboard') }}" class="btn-sm btn-secondary">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
               </svg>
               Dashboard
           </a>
       </div>
   </x-slot>

   <div class="max-w-2xl mx-auto space-y-6">

       @php
           $isPaid = in_array($order->payment_status, ['settlement', 'capture']);
           $isPending = $order->payment_status === 'pending';
           $isFailed = in_array($order->payment_status, ['deny', 'cancel', 'expire']);
       @endphp

       {{-- Banner status --}}
       @if($isPaid)
           <div class="alert-success p-6 text-center flex-col justify-center items-center gap-3">
               <div class="w-14 h-14 bg-success-100 rounded-full flex items-center justify-center text-success-600 shadow-glow-sm">
                   <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                   </svg>
               </div>
               <h3 class="text-base font-bold text-success-800">Pembayaran Berhasil!</h3>
               <p class="text-xs text-success-600 max-w-sm">Terima kasih, pembayaran Anda sudah kami konfirmasi. Pesanan akan segera masuk ke antrean cetak.</p>
           </div>
       @elseif($isPending)
           <div class="alert-warning p-6 text-center flex-col justify-center items-center gap-3">
               <div class="w-14 h-14 bg-warning-100 rounded-full flex items-center justify-center text-warning-600 animate-pulse">
                   <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                   </svg>
               </div>
               <h3 class="text-base font-bold text-warning-800">Menunggu Pembayaran</h3>
               <p class="text-xs text-warning-600 max-w-sm">Selesaikan pembayaran sesuai instruksi dari Midtrans. Pesanan Anda akan diproses setelah pembayaran dikonfirmasi.</p>
           </div>
       @else
           <div class="alert-danger p-6 text-center flex-col justify-center items-center gap-3">
               <div class="w-14 h-14 bg-danger-100 rounded-full flex items-center justify-center text-danger-600">
                   <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                   </svg>
               </div>
               <h3 class="text-base font-bold text-danger-800">Transaksi Gagal / Dibatalkan</h3>
               <p class="text-xs text-danger-600 max-w-sm">Pembayaran Anda tidak berhasil diproses. Silakan hubungi admin atau buat pesanan baru.</p>
           </div>
       @endif

       {{-- Detail invoice --}}
       <div class="card overflow-hidden">
           <div class="card-header bg-surface-900 text-white">
               <div>
                   <p class="text-surface-400 text-2xs uppercase tracking-wider">Invoice</p>
                   <p class="font-bold font-mono tracking-wide mt-0.5">{{ $order->invoice_number }}</p>
               </div>
               <div class="text-right">
                   <p class="text-surface-400 text-2xs uppercase tracking-wider">Tanggal</p>
                   <p class="font-semibold text-sm mt-0.5">{{ $order->created_at->format('d M Y') }}</p>
               </div>
           </div>

           <div class="card-body space-y-5">
               {{-- Status badges --}}
               <div class="flex gap-2 flex-wrap">
                   @php
                       $osMap = [
                           'pending'    => ['label' => 'Menunggu', 'class' => 'status-pending'],
                           'diproses'   => ['label' => 'Diproses', 'class' => 'status-process'],
                           'selesai'    => ['label' => 'Selesai',  'class' => 'status-done'],
                           'dibatalkan' => ['label' => 'Dibatalkan','class' => 'status-canceled'],
                       ];
                       $psMap = [
                           'pending'    => ['label' => 'Belum Bayar',  'class' => 'status-unpaid'],
                           'settlement' => ['label' => 'Lunas',        'class' => 'status-paid'],
                           'capture'    => ['label' => 'Lunas',        'class' => 'status-paid'],
                           'deny'       => ['label' => 'Ditolak',      'class' => 'status-failed'],
                           'cancel'     => ['label' => 'Dibatalkan',   'class' => 'status-failed'],
                           'expire'     => ['label' => 'Kedaluwarsa',  'class' => 'status-expired'],
                       ];
                        $statusVal = $order->order_status instanceof \App\Enums\OrderStatus ? $order->order_status->value : $order->order_status;
                        $os = $osMap[$statusVal] ?? ['label' => $order->order_status instanceof \App\Enums\OrderStatus ? $order->order_status->label() : ucfirst((string)$statusVal), 'class' => 'badge-neutral'];
                        $ps = $psMap[$order->payment_status] ?? ['label' => ucfirst($order->payment_status), 'class' => 'badge-neutral'];
                   @endphp
                   <span class="inline-flex items-center gap-1 text-xs">Pesanan: <span class="{{ $os['class'] }} ml-1">{{ $os['label'] }}</span></span>
                   <span class="inline-flex items-center gap-1 text-xs">Pembayaran: <span class="{{ $ps['class'] }} ml-1">{{ $ps['label'] }}</span></span>
                   @if($order->payment_type)
                       <span class="inline-flex items-center gap-1 text-xs">Metode: <span class="badge-neutral ml-1">{{ str_replace('_', ' ', ucfirst($order->payment_type)) }}</span></span>
                   @endif
               </div>

               <div class="divider"></div>

               {{-- Data pemesan --}}
               <div class="p-3 bg-surface-50 rounded-xl border border-surface-200 text-sm">
                   <p class="text-2xs font-semibold text-surface-400 uppercase tracking-wider mb-1">Data Pemesan</p>
                   <p class="font-bold text-surface-800">{{ $order->user->name }}</p>
                   <p class="text-xs text-surface-500 mt-0.5">{{ $order->user->email }}</p>
               </div>

               {{-- Daftar produk --}}
               <div>
                   <p class="text-2xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Rincian Produk</p>
                   <div class="space-y-3">
                       @foreach($order->orderDetails as $detail)
                           <div class="flex items-center justify-between text-sm">
                               <div class="min-w-0">
                                   <p class="font-medium text-surface-800 truncate">{{ $detail->product?->product_name ?? 'Produk' }}</p>
                                   <p class="text-xs text-surface-400 mt-0.5">{{ $detail->qty }} &times; Rp {{ number_format($detail->subtotal / max($detail->qty, 1), 0, ',', '.') }}</p>
                               </div>
                               <span class="font-semibold text-surface-900 flex-shrink-0">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                           </div>
                       @endforeach
                   </div>
               </div>

               {{-- Total --}}
               <div class="border-t border-surface-100 pt-4">
                   <div class="flex justify-between items-center">
                       <span class="font-bold text-surface-900 text-sm">Total Pembayaran</span>
                       <span class="font-extrabold text-lg text-surface-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                   </div>
                   @if($order->paid_at)
                       <p class="text-2xs text-surface-400 mt-1 text-right">
                           Dibayar pada: {{ $order->paid_at->format('d M Y, H:i') }}
                       </p>
                   @endif
               </div>

               @if($order->notes)
                   <div class="divider"></div>
                   <div class="text-xs text-surface-500 italic bg-surface-50 p-3 rounded-lg border border-surface-200">
                       <span class="font-semibold not-italic text-surface-700 block mb-1">Catatan:</span>
                       {{ $order->notes }}
                   </div>
               @endif
           </div>
       </div>

       {{-- Tombol aksi --}}
       <div class="flex flex-col sm:flex-row gap-3">
           @if($isPaid || $isPending)
               <a href="{{ route('pesanan.cetak', $order->id) }}"
                  target="_blank"
                  class="flex-1 btn-md btn-primary justify-center shadow-md">
                   <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                   Cetak Invoice
               </a>
           @endif
           @if($isPending && ($order->order_status?->value ?? $order->order_status) !== 'dibatalkan')
                <a href="{{ route('payment.pay', $order->id) }}"
                  class="flex-1 btn-md btn-warning justify-center shadow-md">
                   <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                   Selesaikan Pembayaran
               </a>
           @endif
           <a href="{{ route('dashboard') }}"
              class="flex-1 btn-md btn-secondary justify-center">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
               Dashboard
           </a>
       </div>

   </div>
</x-app-layout>
