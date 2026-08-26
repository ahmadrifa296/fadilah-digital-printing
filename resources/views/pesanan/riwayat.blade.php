<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs text-slate-500 mt-0.5">Arsip riwayat transaksi pesanan, status pembayaran Midtrans, dan rincian transaksi</p>
        </div>
    </x-slot>

    <div class="space-y-5">

        {{-- Filter Panel --}}
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <form action="{{ route('transaksi.riwayat') }}" method="GET" id="filter-form"
                  class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 items-end">
                <div class="space-y-1">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Status Pembayaran</label>
                    <select name="payment_status" class="w-full text-[11px] font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl py-1.5 px-2.5 focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all">
                        <option value="">Semua Status Pembayaran</option>
                        <option value="pending"    {{ request('payment_status') == 'pending'    ? 'selected' : '' }}>Belum Bayar (Pending)</option>
                        <option value="settlement" {{ request('payment_status') == 'settlement' ? 'selected' : '' }}>Lunas (Settlement)</option>
                        <option value="capture"    {{ request('payment_status') == 'capture'    ? 'selected' : '' }}>Lunas (Capture)</option>
                        <option value="deny"       {{ request('payment_status') == 'deny'       ? 'selected' : '' }}>Ditolak (Deny)</option>
                        <option value="cancel"     {{ request('payment_status') == 'cancel'     ? 'selected' : '' }}>Batal (Cancel)</option>
                        <option value="expire"     {{ request('payment_status') == 'expire'     ? 'selected' : '' }}>Kedaluwarsa (Expire)</option>
                        <option value="refund"     {{ request('payment_status') == 'refund'     ? 'selected' : '' }}>Refund</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Status Pesanan</label>
                    <select name="order_status" class="w-full text-[11px] font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl py-1.5 px-2.5 focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all">
                        <option value="">Semua Status Pesanan</option>
                        <option value="pending"    {{ request('order_status') == 'pending'    ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                        <option value="diproses"   {{ request('order_status') == 'diproses'   ? 'selected' : '' }}>Sedang Diproses</option>
                        <option value="selesai"    {{ request('order_status') == 'selesai'    ? 'selected' : '' }}>Selesai Cetak</option>
                        <option value="dibatalkan" {{ request('order_status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full text-[11px] font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl py-1.5 px-2.5 focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full text-[11px] font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl py-1.5 px-2.5 focus:bg-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all">
                </div>
                
                <div class="flex flex-wrap gap-2 col-span-full justify-between items-center mt-1 pt-2.5 border-t border-slate-100">
                    <div class="flex gap-2">
                        <button type="submit" class="px-3.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-[11px] rounded-xl transition-all shadow-2xs flex items-center gap-1.5 cursor-pointer active:scale-[0.98] whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            <span>Terapkan Filter</span>
                        </button>
                        <a href="{{ route('transaksi.riwayat') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-[11px] rounded-xl transition-all border border-slate-200 flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5"/></svg>
                            <span>Reset</span>
                        </a>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <a href="{{ route('pesanan.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-[11px] rounded-xl transition-all shadow-2xs cursor-pointer active:scale-[0.98] whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>Kelola Pesanan</span>
                        </a>
                        <a href="{{ route('laporan.export', request()->all()) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 border border-emerald-200 text-[11px] font-extrabold rounded-xl text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-colors shadow-2xs whitespace-nowrap">
                            <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Ekspor Excel/CSV</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabel Transaksi --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[10px]">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-600 font-extrabold text-[9px] uppercase tracking-wider border-b border-slate-200/80 whitespace-nowrap">
                            <th class="px-2.5 py-2 whitespace-nowrap">Invoice</th>
                            <th class="px-2.5 py-2 whitespace-nowrap">Tanggal Transaksi</th>
                            <th class="px-2.5 py-2 whitespace-nowrap">Pelanggan</th>
                            <th class="px-2.5 py-2 text-right whitespace-nowrap">Total</th>
                            <th class="px-2.5 py-2 text-center whitespace-nowrap">Metode</th>
                            <th class="px-2.5 py-2 text-center whitespace-nowrap">Pembayaran</th>
                            <th class="px-2.5 py-2 text-center whitespace-nowrap">Status Pesanan</th>
                            <th class="px-2.5 py-2 whitespace-nowrap">Dibayar Pada</th>
                            <th class="px-2.5 py-2 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @forelse($orders as $order)
                            <tr class="hover:bg-orange-50/20 transition-colors">
                                <td class="px-2.5 py-2 font-mono text-[10px] font-black text-slate-900 whitespace-nowrap">
                                    #{{ $order->invoice_number }}
                                </td>
                                <td class="px-2.5 py-2 text-[9px] text-slate-600 whitespace-nowrap">
                                    <span class="font-mono text-slate-800 font-bold whitespace-nowrap">{{ $order->created_at->format('d M Y') }}</span>
                                    <span class="text-slate-400 font-mono text-[9px] ml-0.5 whitespace-nowrap">{{ $order->created_at->format('H:i') }}</span>
                                </td>
                                <td class="px-2.5 py-2 max-w-[130px] whitespace-nowrap">
                                    <div class="font-bold text-slate-800 text-[10px] truncate whitespace-nowrap" title="{{ $order->user->name ?? 'Guest' }}">{{ $order->user->name ?? 'Guest' }}</div>
                                    <div class="text-[9px] text-slate-400 font-normal truncate whitespace-nowrap" title="{{ $order->user->email ?? '-' }}">{{ $order->user->email ?? '-' }}</div>
                                </td>
                                <td class="px-2.5 py-2 text-right font-black text-slate-900 text-[11px] whitespace-nowrap">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                </td>
                                <td class="px-2.5 py-2 text-center text-[9px] font-extrabold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    {{ $order->payment_type ? str_replace('_', ' ', $order->payment_type) : '—' }}
                                </td>
                                {{-- payment_status badge --}}
                                <td class="px-2.5 py-2 text-center whitespace-nowrap">
                                    @php
                                        $psMap = [
                                            'pending'    => ['label' => 'Belum Bayar', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                            'settlement' => ['label' => 'Lunas',       'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                            'capture'    => ['label' => 'Lunas',       'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                            'deny'       => ['label' => 'Ditolak',     'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                            'cancel'     => ['label' => 'Batal',       'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                            'expire'     => ['label' => 'Kedaluwarsa', 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
                                            'refund'     => ['label' => 'Refund',      'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
                                        ];
                                        $ps = $psMap[$order->payment_status] ?? ['label' => ucfirst($order->payment_status ?? '-'), 'class' => 'bg-slate-100 text-slate-600 border-slate-200'];
                                    @endphp
                                    <span class="{{ $ps['class'] }} text-[8px] font-extrabold px-1.5 py-0.5 rounded border uppercase tracking-wider whitespace-nowrap inline-block">
                                        {{ $ps['label'] }}
                                    </span>
                                </td>
                                {{-- order_status badge --}}
                                <td class="px-2.5 py-2 text-center whitespace-nowrap">
                                    @php
                                        $statusVal = $order->order_status instanceof \App\Enums\OrderStatus ? $order->order_status->value : $order->order_status;
                                        $osMap = [
                                            'pending'    => ['label' => 'Menunggu',   'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                            'diproses'   => ['label' => 'Diproses',   'class' => 'bg-sky-50 text-sky-700 border-sky-200'],
                                            'selesai'    => ['label' => 'Selesai',    'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                            'dibatalkan' => ['label' => 'Dibatalkan', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                        ];
                                        $os = $osMap[$statusVal] ?? ['label' => $order->order_status instanceof \App\Enums\OrderStatus ? $order->order_status->label() : ucfirst((string)$statusVal), 'class' => 'bg-slate-100 text-slate-600 border-slate-200'];
                                    @endphp
                                    <span class="{{ $os['class'] }} text-[8px] font-extrabold px-2 py-0.5 rounded-full border uppercase tracking-wider whitespace-nowrap inline-block">
                                        {{ $os['label'] }}
                                    </span>
                                </td>
                                <td class="px-2.5 py-2 text-[9px] text-slate-600 whitespace-nowrap">
                                    @if($order->paid_at)
                                        <span class="font-mono text-slate-800 font-bold whitespace-nowrap">{{ $order->paid_at->format('d M Y') }}</span>
                                        <span class="text-slate-400 font-mono text-[9px] ml-0.5 whitespace-nowrap">{{ $order->paid_at->format('H:i') }}</span>
                                    @else
                                        <span class="text-slate-300 font-mono">—</span>
                                    @endif
                                </td>
                                <td class="px-2.5 py-2 text-center whitespace-nowrap">
                                    <a href="{{ route('pesanan.cetak', $order->id) }}"
                                       target="_blank"
                                       class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold border border-slate-200 text-[9px] uppercase tracking-wider rounded px-2 py-0.5 transition-colors whitespace-nowrap inline-flex items-center gap-1">
                                        <svg class="w-3 h-3 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        <span>Invoice</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="py-12 text-center text-slate-400 font-medium">
                                        <p class="text-xs font-bold text-slate-600">Tidak ada transaksi ditemukan</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Coba sesuaikan filter pencarian atau reset filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($orders->hasPages())
                <div class="px-3.5 py-2.5 border-t border-slate-100 bg-slate-50/50">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        {{-- Info jumlah data --}}
        <p class="text-[10px] text-slate-400 text-center font-medium">
            Menampilkan {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }}
            dari {{ $orders->total() }} transaksi
        </p>

    </div>
</x-app-layout>
