<!DOCTYPE html>
<html lang="id" class="h-full bg-white">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Invoice {{ $pesanan->invoice_number }} — Fadilah Printing</title>
   
   {{-- Google Fonts - Inter --}}
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
   
   {{-- Tailwind CSS CDN --}}
   <script src="https://cdn.tailwindcss.com"></script>
   <script>
       tailwind.config = {
           theme: {
               extend: {
                   fontFamily: {
                       sans: ['Inter', 'sans-serif'],
                   }
               }
           }
       }
   </script>
   <style>
       body {
           font-family: 'Inter', sans-serif;
       }
       @media print {
           body { font-size: 10pt; color: #000; background: #fff; }
           .no-print { display: none !important; }
           @page { margin: 1cm; }
       }
   </style>
</head>
<body class="bg-slate-50 p-6 sm:p-12 text-slate-800 h-full">

   <div class="max-w-3xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-slate-200 print:border-0 print:shadow-none print:p-0 print:rounded-none">

       {{-- Header --}}
       <div class="flex flex-col sm:flex-row justify-between items-start border-b border-slate-200 pb-8 gap-4">
           <div>
               <div class="flex items-center gap-2">
                   <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-sm">F</div>
                   <span class="text-xl font-bold text-slate-900 tracking-tight">Fadilah Digital Printing</span>
               </div>
               <p class="text-xs text-slate-400 mt-2">Solusi Cetak Cepat, Murah & Berkualitas</p>
               <p class="text-xs text-slate-500 mt-1">Jl. Contoh Percetakan No. 123, Kota</p>
               <p class="text-xs text-slate-500">Telp: 0812-3456-7890 | email: admin@fadilahprinting.com</p>
           </div>
           <div class="sm:text-right">
               <span class="text-xs font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">Invoice</span>
               <p class="text-sm font-mono font-bold text-slate-900 mt-3">{{ $pesanan->invoice_number }}</p>
               <p class="text-xs text-slate-400 mt-1">Tanggal: {{ $pesanan->created_at->format('d M Y') }}</p>
           </div>
       </div>

       {{-- Detail Pemesan & Transaksi --}}
       <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 my-8 text-sm">
           <div>
               <p class="text-2xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ditagihkan Kepada</p>
               <p class="font-bold text-slate-900">{{ $pesanan->user->name ?? 'Pelanggan' }}</p>
               <p class="text-xs text-slate-500 mt-0.5">{{ $pesanan->user->email ?? '-' }}</p>
               <p class="text-xs text-slate-500">{{ $pesanan->user->phone_number ?? '-' }}</p>
           </div>
           <div>
               <p class="text-2xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Informasi Transaksi</p>
               <div class="space-y-1 text-xs">
                   <div class="flex justify-between">
                        <span class="text-slate-500">Status Pesanan:</span>
                        <span class="font-semibold uppercase @php
                            $osVal = $pesanan->order_status instanceof \App\Enums\OrderStatus ? $pesanan->order_status->value : $pesanan->order_status;
                            echo match($osVal) {
                                'pending'    => 'text-amber-600',
                                'diproses'   => 'text-blue-600',
                                'selesai'    => 'text-green-600',
                                'dibatalkan' => 'text-red-600',
                                default      => 'text-slate-600',
                            };
                        @endphp">
                            {{ match($osVal) {
                                'pending'    => 'Menunggu',
                                'diproses'   => 'Diproses',
                                'selesai'    => 'Selesai',
                                'dibatalkan' => 'Batal',
                                default      => $pesanan->order_status instanceof \App\Enums\OrderStatus ? $pesanan->order_status->label() : ucfirst((string)$osVal),
                            } }}
                        </span>
                   </div>
                   <div class="flex justify-between">
                       <span class="text-slate-500">Pembayaran:</span>
                       <span class="font-semibold uppercase @php
                           echo match($pesanan->payment_status) {
                               'settlement', 'capture' => 'text-green-600',
                               'pending'               => 'text-amber-600',
                               'deny', 'cancel'        => 'text-red-600',
                               default                 => 'text-slate-600',
                           };
                       @endphp">
                           {{ match($pesanan->payment_status) {
                               'settlement', 'capture' => 'Lunas',
                               'pending'               => 'Belum Bayar',
                               'deny'                  => 'Ditolak',
                               'cancel'                => 'Batal',
                               'expire'                => 'Kedaluwarsa',
                               default                 => ucfirst($pesanan->payment_status ?? '-'),
                           } }}
                       </span>
                   </div>
                   @if($pesanan->paid_at)
                       <div class="flex justify-between">
                           <span class="text-slate-500">Waktu Bayar:</span>
                           <span class="font-medium text-slate-800">{{ $pesanan->paid_at->format('d M Y, H:i') }}</span>
                       </div>
                   @endif
                   @if($pesanan->payment_type)
                       <div class="flex justify-between">
                           <span class="text-slate-500">Metode Bayar:</span>
                           <span class="font-medium text-slate-800 capitalize">{{ str_replace('_', ' ', $pesanan->payment_type) }}</span>
                       </div>
                   @endif
               </div>
           </div>
       </div>

       {{-- Tabel Rincian --}}
       <div class="border border-slate-200 rounded-xl overflow-hidden my-8">
           <table class="w-full text-left text-sm">
               <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                   <tr>
                       <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider w-12 text-center">No</th>
                       <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider">Produk / Layanan</th>
                       <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-center w-20">Qty</th>
                       <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-right w-36">Harga Satuan</th>
                       <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-right w-36">Subtotal</th>
                   </tr>
               </thead>
               <tbody class="divide-y divide-slate-100 text-slate-700">
                   @forelse($pesanan->orderDetails as $i => $detail)
                       <tr>
                           <td class="py-3.5 px-4 text-center text-slate-400 text-xs">{{ $i + 1 }}</td>
                           <td class="py-3.5 px-4">
                               <span class="font-medium text-slate-900 block">{{ $detail->product?->product_name ?? 'Produk Jasa' }}</span>
                               <span class="text-2xs text-slate-400 block mt-0.5">{{ $detail->product?->category?->category_name ?? '-' }}</span>
                               @if($detail->custom_length && $detail->custom_width)
                                   <span class="text-[9px] text-slate-500 block mt-1 bg-slate-50 border border-slate-200/60 rounded px-1.5 py-0.5 w-max">
                                       Ukuran: {{ number_format($detail->custom_length, 2) }}m &times; {{ number_format($detail->custom_width, 2) }}m ({{ number_format($detail->custom_area ?: ($detail->custom_length * $detail->custom_width), 2) }} m²)
                                       @if($detail->price_per_m2) &middot; Rp{{ number_format($detail->price_per_m2, 0, ',', '.') }}/m² @endif
                                   </span>
                               @endif
                               @if($detail->ukuran || $detail->bahan || $detail->finishing || $detail->custom_text)
                                   <span class="text-[9px] text-slate-500 block mt-1 bg-slate-50 border border-slate-200/60 rounded px-1.5 py-0.5 w-max">
                                       @if($detail->ukuran) Ukuran: {{ $detail->ukuran }} @endif
                                       @if($detail->bahan) &middot; Bahan: {{ $detail->bahan }} @endif
                                       @if($detail->finishing) &middot; Finishing: {{ $detail->finishing }} @endif
                                       @if($detail->custom_text) &middot; Teks: "{{ $detail->custom_text }}" @endif
                                   </span>
                               @endif
                           </td>
                           <td class="py-3.5 px-4 text-center">{{ $detail->qty }}</td>
                           <td class="py-3.5 px-4 text-right">Rp {{ number_format($detail->subtotal / max($detail->qty, 1), 0, ',', '.') }}</td>
                           <td class="py-3.5 px-4 text-right font-semibold text-slate-950">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                       </tr>
                   @empty
                       <tr>
                           <td colspan="5" class="py-6 text-center text-slate-400 italic text-xs">Rincian produk tidak tersedia.</td>
                       </tr>
                   @endforelse
               </tbody>
               <tfoot class="bg-slate-50 border-t border-slate-200 font-semibold text-slate-800">
                   <tr>
                       <td colspan="4" class="py-4 px-4 text-right text-xs uppercase tracking-wider text-slate-500 font-bold">Total Pembayaran</td>
                       <td class="py-4 px-4 text-right text-base text-slate-950 font-extrabold">Rp {{ number_format($pesanan->total_price, 0, ',', '.') }}</td>
                   </tr>
               </tfoot>
           </table>
       </div>

       @if($pesanan->notes)
           <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs text-slate-600 mb-8 italic">
               <span class="font-semibold not-italic text-slate-700 block mb-1">Catatan Pesanan:</span>
               {{ $pesanan->notes }}
           </div>
       @endif

       {{-- Signatures --}}
       <div class="grid grid-cols-2 gap-12 mt-12 text-center text-xs">
           <div class="space-y-16">
               <p class="text-slate-400">Penerima / Customer</p>
               <div class="border-t border-slate-200 pt-2 font-semibold text-slate-800">
                   {{ $pesanan->user->name ?? 'Pelanggan' }}
               </div>
           </div>
           <div class="space-y-16">
               <p class="text-slate-400">Hormat Kami, Admin</p>
               <div class="border-t border-slate-200 pt-2 font-semibold text-slate-800">
                   Fadilah Printing
               </div>
           </div>
       </div>

       {{-- Print Action Buttons --}}
       <div class="mt-12 text-center no-print flex justify-center gap-3">
           <button onclick="window.print()"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl shadow-sm flex items-center gap-2 text-sm transition active:scale-[0.98]">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
               Cetak / Simpan PDF
           </button>
           <button onclick="window.close();"
                   class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-semibold py-2.5 px-6 rounded-xl shadow-sm flex items-center text-sm transition active:scale-[0.98]">
               Tutup Halaman
           </button>
       </div>

   </div>
</body>
</html>