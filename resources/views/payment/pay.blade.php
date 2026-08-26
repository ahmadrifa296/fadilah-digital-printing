<x-app-layout>
   <x-slot name="header">
       <div class="page-header mb-0">
           <div>
               <h1 class="page-title">Pembayaran Transaksi</h1>
               <p class="page-subtitle">Selesaikan pembayaran untuk Invoice <span class="font-mono font-semibold text-primary-600">{{ $order->invoice_number }}</span></p>
           </div>
           <a href="{{ route('dashboard') }}" class="btn-sm btn-secondary">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
               </svg>
               Kembali ke Dashboard
           </a>
       </div>
   </x-slot>

   @push('scripts')
       <script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
   @endpush

   {{-- Flash messages --}}
   @if(session('error'))
       <div class="alert-danger mb-5 animate-fade-in">
           <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
           <span>{{ session('error') }}</span>
       </div>
   @endif

   <div class="max-w-2xl mx-auto space-y-6">

       {{-- Invoice Detail Card --}}
       <div class="card overflow-hidden">
           <div class="gradient-brand p-6 text-white">
               <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                   <div>
                       <p class="text-primary-100 text-2xs uppercase tracking-wider font-semibold">Nomor Invoice</p>
                       <h2 class="text-xl font-bold font-mono tracking-wide mt-1">{{ $order->invoice_number }}</h2>
                   </div>
                   <div class="sm:text-right">
                       <p class="text-primary-100 text-2xs uppercase tracking-wider font-semibold">Total Tagihan</p>
                       <p class="text-2xl font-extrabold mt-1">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                   </div>
               </div>
           </div>

           <div class="card-body space-y-5">
               <div class="flex flex-wrap items-center gap-3">
                   @php
                        $osVal = $order->order_status instanceof \App\Enums\OrderStatus ? $order->order_status->value : $order->order_status;
                        $osClass = match($osVal) {
                            'pending'    => 'status-pending',
                            'diproses'   => 'status-process',
                            'selesai'    => 'status-done',
                            'dibatalkan' => 'status-canceled',
                            default      => 'badge-neutral',
                        };
                        $osLabel = match($osVal) {
                            'pending'    => 'Menunggu Konfirmasi',
                            'diproses'   => 'Diproses',
                            'selesai'    => 'Selesai',
                            'dibatalkan' => 'Dibatalkan',
                            default      => $order->order_status instanceof \App\Enums\OrderStatus ? $order->order_status->label() : ucfirst((string)$osVal),
                        };

                       $psClass = match($order->payment_status) {
                           'settlement','capture' => 'status-paid',
                           'pending'              => 'status-unpaid',
                           'deny','cancel'        => 'status-failed',
                           'expire'               => 'status-expired',
                           default                => 'badge-neutral',
                       };
                       $psLabel = match($order->payment_status) {
                           'settlement','capture' => 'Lunas',
                           'pending'              => 'Belum Dibayar',
                           'deny','cancel'        => 'Gagal',
                           'expire'               => 'Kedaluwarsa',
                           default                => ucfirst($order->payment_status ?? '-'),
                       };
                   @endphp
                   <span class="inline-flex items-center gap-1.5 text-xs font-semibold">
                       Status Pesanan: <span class="{{ $osClass }}">{{ $osLabel }}</span>
                   </span>
                   <span class="inline-flex items-center gap-1.5 text-xs font-semibold">
                       Status Bayar: <span class="{{ $psClass }}">{{ $psLabel }}</span>
                   </span>
               </div>

               <div class="divider"></div>

               {{-- Rincian Produk --}}
               <div>
                   <h3 class="text-xs font-bold text-surface-500 uppercase tracking-wider mb-3">Produk yang Dipesan</h3>
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

               @if($order->notes)
                   <div class="divider"></div>
                   <div class="p-3 bg-surface-50 rounded-xl border border-surface-200 text-xs text-surface-600 italic">
                       <span class="font-semibold not-italic text-surface-700 block mb-1">Catatan Pesanan:</span>
                       {{ $order->notes }}
                   </div>
               @endif

               <div class="divider"></div>

               <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs text-surface-400 gap-2">
                   <span>Dipesan pada: {{ $order->created_at->format('d M Y, H:i') }}</span>
                   <span>Pelanggan: <strong class="text-surface-700">{{ $order->user->name }}</strong></span>
               </div>
           </div>
       </div>

       {{-- Payment Action Card --}}
        @if(($order->order_status?->value ?? $order->order_status) !== 'dibatalkan')
            <div class="card p-6 text-center">
               <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-primary-50 flex items-center justify-center text-primary-600 shadow-glow-sm">
                   <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                   </svg>
               </div>
               <h3 class="text-base font-bold text-surface-900 mb-1">Pilih Metode Pembayaran</h3>
               <p class="text-xs text-surface-400 max-w-sm mx-auto mb-6">
                   Selesaikan pembayaran menggunakan Transfer Bank, e-Wallet (QRIS, GoPay, OVO, ShopeePay), dll.
               </p>

               <button id="btn-bayar"
                       onclick="bayarSekarang()"
                       class="w-full btn-lg btn-primary justify-center shadow-md py-4 active:scale-[0.98]">
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                   Bayar Sekarang &mdash; Rp {{ number_format($order->total_price, 0, ',', '.') }}
               </button>

               <div class="mt-4 flex items-center justify-center gap-1 text-2xs text-surface-400">
                   <svg class="w-3.5 h-3.5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                   <span>Terenskripsi & aman via Midtrans Payment Gateway</span>
               </div>
           </div>
       @else
           <div class="alert-danger p-6 text-center flex-col justify-center items-center gap-3">
               <svg class="w-10 h-10 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
               <p class="font-bold text-sm text-danger-700">Pesanan Telah Dibatalkan</p>
               <p class="text-xs text-danger-600">Pesanan ini telah dibatalkan oleh sistem atau admin dan tidak dapat diproses lagi.</p>
               <a href="{{ route('dashboard') }}" class="btn-sm btn-secondary mt-2">Kembali ke Dashboard</a>
           </div>
       @endif

   </div>

   <script>
       function bayarSekarang() {
           const btn = document.getElementById('btn-bayar');
           btn.disabled = true;
           btn.innerHTML = `<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Membuka Gateway...`;

           window.snap.pay('{{ $snapToken }}', {
               onSuccess: function(result) {
                   const orderId = result.order_id || '';
                   window.location.href = '{{ route('payment.success', $order) }}?midtrans_order_id=' + encodeURIComponent(orderId);
               },
               onPending: function(result) {
                   const orderId = result.order_id || '';
                   window.location.href = '{{ route('payment.success', $order) }}?midtrans_order_id=' + encodeURIComponent(orderId);
               },
               onError: function(result) {
                   btn.disabled = false;
                   btn.innerHTML = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>Bayar Sekarang &mdash; Rp {{ number_format($order->total_price, 0, ',', '.') }}`;
                   alert('Pembayaran gagal dilakukan. Silakan coba kembali.');
               },
               onClose: function() {
                   btn.disabled = false;
                   btn.innerHTML = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>Bayar Sekarang &mdash; Rp {{ number_format($order->total_price, 0, ',', '.') }}`;
               }
           });
       }
   </script>
</x-app-layout>
