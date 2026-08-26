<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Laporan Penjualan & Analitik</h1>
    </x-slot>

    <div class="space-y-6">

        {{-- Print Header (Only visible when printing) --}}
        <div class="hidden print:block text-center border-b border-slate-300 pb-5 mb-8">
            <h1 class="text-2xl font-bold text-slate-900 uppercase tracking-wide">Fadilah Digital Printing</h1>
            <p class="text-xs text-slate-500 mt-1">Laporan Rekapitulasi Pendapatan Penjualan</p>
            <p class="text-xs text-slate-600 font-semibold mt-1">
                Periode: 
                @if($period === '1_month') 1 Bulan Terakhir
                @elseif($period === '3_months') 3 Bulan Terakhir
                @elseif($period === '6_months') 6 Bulan Terakhir
                @elseif($period === '1_year') 1 Tahun Terakhir
                @else Keseluruhan (Semua Periode)
                @endif
            </p>
            <p class="text-xs text-slate-400 mt-0.5">Dicetak pada: {{ date('d F Y, H:i') }}</p>
        </div>

        {{-- Filters Section (No-Print) --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
            <form method="GET" action="{{ route('laporan.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <label for="period" class="text-xs font-extrabold text-slate-600 uppercase tracking-wider">Pilih Periode:</label>
                <select name="period" id="period" onchange="this.form.submit()" 
                        class="rounded-xl border-slate-200 text-xs py-2 pl-3 pr-8 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 shadow-2xs cursor-pointer font-bold text-slate-700 bg-slate-50">
                    <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Semua / Keseluruhan</option>
                    <option value="1_month" {{ $period === '1_month' ? 'selected' : '' }}>1 Bulan Terakhir</option>
                    <option value="3_months" {{ $period === '3_months' ? 'selected' : '' }}>3 Bulan Terakhir</option>
                    <option value="6_months" {{ $period === '6_months' ? 'selected' : '' }}>6 Bulan Terakhir</option>
                    <option value="1_year" {{ $period === '1_year' ? 'selected' : '' }}>1 Tahun Terakhir</option>
                </select>
            </form>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('laporan.export', ['period' => $period]) }}" 
                   class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl transition-all shadow-2xs inline-flex items-center gap-1.5 cursor-pointer active:scale-[0.98] whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Ekspor Excel</span>
                </a>
                <a href="{{ route('laporan.pdf', ['period' => $period]) }}" target="_blank" 
                   class="px-3.5 py-2 bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-xs rounded-xl transition-all shadow-2xs inline-flex items-center gap-1.5 cursor-pointer active:scale-[0.98] whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Cetak PDF</span>
                </a>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Pendapatan Kotor -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center justify-between print:border-slate-300 print:bg-transparent">
                <div>
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pendapatan Kotor</h3>
                    <p class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">Rp {{ number_format($totalPendapatanKotor, 0, ',', '.') }}</p>
                    <span class="text-[10px] text-slate-400 font-semibold">Total omset pesanan selesai</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Potongan Refund Garansi -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center justify-between print:border-slate-300 print:bg-transparent">
                <div>
                    <h3 class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Potongan Refund Garansi</h3>
                    <p class="text-2xl font-black text-rose-600 tracking-tight mt-0.5">- Rp {{ number_format($totalRefund, 0, ',', '.') }}</p>
                    <span class="text-[10px] text-rose-500 font-semibold">Klaim disetujui (Approved)</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>

            <!-- Pendapatan Bersih -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center justify-between print:border-slate-300 print:bg-transparent">
                <div>
                    <h3 class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Total Pendapatan Bersih</h3>
                    <p class="text-2xl font-black text-emerald-600 tracking-tight mt-0.5">Rp {{ number_format($totalPendapatanBersih, 0, ',', '.') }}</p>
                    <span class="text-[10px] text-emerald-500 font-semibold">Pendapatan bersih realisasi</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
        </div>

        {{-- Tabel Laporan Penjualan Utama --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-extrabold text-[11px] text-slate-800 uppercase tracking-wider">Rekapitulasi Penjualan</h3>
                <p class="text-slate-400 text-[10px] mt-0.5 font-medium">Daftar semua transaksi yang berstatus sukses (Selesai).</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[11px] print:border print:border-slate-300">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-600 font-extrabold text-[10px] uppercase tracking-wider border-b border-slate-200/80 whitespace-nowrap print:bg-slate-50">
                            <th class="w-12 text-center px-3.5 py-2.5 whitespace-nowrap">No</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Invoice</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Tanggal Selesai</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Pelanggan</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Status Klaim</th>
                            <th class="px-3.5 py-2.5 text-right whitespace-nowrap">Nominal Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @forelse($orders as $index => $item)
                            <tr class="hover:bg-orange-50/20 transition-colors print:hover:bg-transparent">
                                <td class="text-center text-slate-400 text-xs py-2.5 px-3.5 font-mono whitespace-nowrap">{{ $index + 1 }}</td>
                                <td class="px-3.5 py-2.5 font-mono text-xs font-black text-slate-900 whitespace-nowrap">#{{ $item->invoice_number }}</td>
                                <td class="px-3.5 py-2.5 text-[10px] text-slate-600 font-mono font-bold whitespace-nowrap">{{ $item->updated_at->format('d M Y') }}</td>
                                <td class="px-3.5 py-2.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-800 text-xs whitespace-nowrap">{{ $item->user->name ?? 'Guest' }}</div>
                                    <div class="text-[10px] text-slate-400 font-semibold whitespace-nowrap">{{ $item->user->email ?? '-' }}</div>
                                </td>
                                <td class="px-3.5 py-2.5 whitespace-nowrap">
                                    @if($item->claim)
                                        @if($item->claim->status === 'approved')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wider whitespace-nowrap">Refunded</span>
                                        @elseif($item->claim->status === 'rejected')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-slate-100 text-slate-600 border border-slate-200 uppercase tracking-wider whitespace-nowrap">Claim Rejected</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider whitespace-nowrap">Claim Pending</span>
                                        @endif
                                    @else
                                        <span class="text-slate-300 text-xs whitespace-nowrap">-</span>
                                    @endif
                                </td>
                                <td class="px-3.5 py-2.5 text-right font-black text-slate-900 text-xs whitespace-nowrap">
                                    @if($item->claim && $item->claim->status === 'approved')
                                        <span class="line-through text-slate-400 font-semibold mr-1.5 whitespace-nowrap">Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                                        <span class="text-rose-600 whitespace-nowrap">Rp 0</span>
                                    @else
                                        <span class="whitespace-nowrap">Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="py-12 text-center text-slate-400 font-medium">
                                        <p class="text-xs font-bold text-slate-600">Belum ada rekapitulasi</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Belum ada transaksi dengan status pesanan selesai.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100/70 print:bg-transparent font-bold text-slate-800 border-t border-slate-200">
                            <td colspan="5" class="py-3 px-4 text-right text-xs uppercase tracking-wider text-slate-600 font-extrabold whitespace-nowrap">Grand Total (Pendapatan Bersih)</td>
                            <td class="py-3 px-4 text-right text-sm text-slate-950 font-black whitespace-nowrap">
                                Rp {{ number_format($totalPendapatanBersih, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Tabel Khusus Pesanan Claim Garansi --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-extrabold text-[11px] text-rose-700 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Rekapitulasi Klaim Garansi & Komplain
                </h3>
                <p class="text-slate-400 text-[10px] mt-0.5 font-medium">Daftar semua pengajuan garansi uang kembali pada periode terpilih.</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[11px] print:border print:border-slate-300">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-600 font-extrabold text-[10px] uppercase tracking-wider border-b border-slate-200/80 whitespace-nowrap print:bg-slate-50">
                            <th class="w-12 text-center px-3.5 py-2.5 whitespace-nowrap">No</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Invoice</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Pelanggan</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Alasan Komplain</th>
                            <th class="px-3.5 py-2.5 whitespace-nowrap">Informasi Rekening Refund</th>
                            <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Status</th>
                            <th class="px-3.5 py-2.5 text-right whitespace-nowrap">Nominal Refund</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @forelse($claims as $index => $c)
                            <tr class="hover:bg-rose-50/20 transition-colors print:hover:bg-transparent">
                                <td class="text-center text-slate-400 text-xs py-2.5 px-3.5 font-mono whitespace-nowrap">{{ $index + 1 }}</td>
                                <td class="px-3.5 py-2.5 font-mono text-xs font-black text-slate-900 whitespace-nowrap">
                                    <a href="{{ route('admin.claims.show', $c->id) }}" class="text-orange-500 hover:underline no-print whitespace-nowrap">
                                        #{{ $c->order->invoice_number }}
                                    </a>
                                    <span class="hidden print:inline whitespace-nowrap">#{{ $c->order->invoice_number }}</span>
                                </td>
                                <td class="px-3.5 py-2.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-800 text-xs whitespace-nowrap">{{ $c->order->user->name ?? 'Guest' }}</div>
                                    <div class="text-[10px] text-slate-400 font-semibold whitespace-nowrap">{{ $c->order->user->email ?? '-' }}</div>
                                </td>
                                <td class="px-3.5 py-2.5 text-xs font-medium text-slate-700">
                                    <div class="font-bold text-rose-600 text-[11px] whitespace-nowrap">{{ $c->reason }}</div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-[200px]" title="{{ $c->description }}">{{ $c->description }}</div>
                                </td>
                                <td class="px-3.5 py-2.5 text-[10px] font-semibold text-slate-600 leading-normal whitespace-nowrap">
                                    <div>Bank: <span class="font-bold uppercase text-slate-800 whitespace-nowrap">{{ $c->bank_name ?? '-' }}</span></div>
                                    <div>No: <span class="font-bold font-mono text-slate-800 whitespace-nowrap">{{ $c->bank_account_number ?? '-' }}</span></div>
                                    <div>Nama: <span class="font-bold text-slate-800 whitespace-nowrap">{{ $c->bank_account_name ?? '-' }}</span></div>
                                </td>
                                <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                    @if($c->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider whitespace-nowrap">Disetujui (Refunded)</span>
                                    @elseif($c->status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wider whitespace-nowrap">Ditolak</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider whitespace-nowrap">Pending</span>
                                    @endif
                                </td>
                                <td class="px-3.5 py-2.5 text-right font-black text-slate-900 text-xs whitespace-nowrap">
                                    @if($c->status === 'approved')
                                        <span class="text-rose-600 whitespace-nowrap">Rp {{ number_format($c->order->total_price, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-slate-400 font-medium whitespace-nowrap">Rp {{ number_format($c->order->total_price, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="py-12 text-center text-slate-400 font-medium">
                                        <p class="text-xs font-bold text-slate-600">Tidak ada klaim garansi</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Tidak ada pengajuan klaim garansi/komplain pada periode terpilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-rose-50/40 print:bg-transparent font-bold text-slate-800 border-t border-slate-200">
                            <td colspan="6" class="py-3 px-4 text-right text-xs uppercase tracking-wider text-rose-800 font-extrabold whitespace-nowrap">Total Nilai Refunded (Klaim Disetujui)</td>
                            <td class="py-3 px-4 text-right text-sm text-rose-600 font-black whitespace-nowrap">
                                Rp {{ number_format($totalRefund, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Signature Print --}}
        <div class="hidden print:flex justify-end mt-16">
            <div class="text-center">
                <p class="text-slate-500 text-xs mb-20">Mengetahui,</p>
                <p class="font-bold text-slate-800 border-t border-slate-400 pt-2 inline-block px-8">Pemilik Fadilah Printing</p>
            </div>
        </div>

    </div>
</x-app-layout>