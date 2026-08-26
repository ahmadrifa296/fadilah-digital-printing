@php
    $periodLabel = match($period) {
        '1_month'  => '1 Bulan Terakhir',
        '3_months' => '3 Bulan Terakhir',
        '6_months' => '6 Bulan Terakhir',
        '1_year'   => '1 Tahun Terakhir',
        default    => 'Keseluruhan (Semua Periode)',
    };
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }

        /* Kop */
        .kop-title  { font-size: 16px; font-weight: bold; text-align: center; }
        .kop-sub    { font-size: 12px; text-align: center; color: #334155; }
        .kop-meta   { font-size: 10px; text-align: center; color: #64748b; }
        .kop-line   { border-bottom: 2px solid #1e293b; }

        /* Summary */
        .sum-kotor  { background:#334155; color:#fff; font-weight:bold; text-align:center; padding:8px; border:1px solid #94a3b8; }
        .sum-refund { background:#b91c1c; color:#fff; font-weight:bold; text-align:center; padding:8px; border:1px solid #fca5a5; }
        .sum-bersih { background:#15803d; color:#fff; font-weight:bold; text-align:center; padding:8px; border:1px solid #86efac; }
        .sum-val-kotor  { background:#f8fafc; font-weight:bold; font-size:13px; text-align:center; padding:8px; border:1px solid #94a3b8; }
        .sum-val-refund { background:#fef2f2; font-weight:bold; font-size:13px; color:#b91c1c; text-align:center; padding:8px; border:1px solid #fca5a5; }
        .sum-val-bersih { background:#f0fdf4; font-weight:bold; font-size:13px; color:#15803d; text-align:center; padding:8px; border:1px solid #86efac; }

        /* Section title */
        .sec-title { font-size: 12px; font-weight: bold; padding: 6px 0; border-bottom: 2px solid #1e293b; color: #1e293b; }
        .sec-title-red { font-size: 12px; font-weight: bold; padding: 6px 0; border-bottom: 2px solid #b91c1c; color: #b91c1c; }

        /* Table headers */
        .th-dark { background:#1e293b; color:#fff; font-weight:bold; text-align:center; padding:7px 6px; border:1px solid #475569; }
        .th-red  { background:#b91c1c; color:#fff; font-weight:bold; text-align:center; padding:7px 6px; border:1px solid #991b1b; }

        /* Table cells */
        .td { border:1px solid #cbd5e1; padding:6px 7px; vertical-align:top; }
        .td-center { border:1px solid #cbd5e1; padding:6px 7px; text-align:center; vertical-align:top; }
        .td-right  { border:1px solid #cbd5e1; padding:6px 7px; text-align:right;  vertical-align:top; }
        .td-mono   { border:1px solid #cbd5e1; padding:6px 7px; font-family: Courier New, monospace; vertical-align:top; }

        /* Row shading */
        .row-even  { background:#f8fafc; }
        .row-total-green { background:#f0fdf4; font-weight:bold; }
        .row-total-red   { background:#fef2f2; font-weight:bold; }
        .row-empty  { color:#94a3b8; font-style:italic; text-align:center; }

        /* Values */
        .val-green { color:#15803d; font-weight:bold; }
        .val-red   { color:#b91c1c; font-weight:bold; }
        .val-strike { text-decoration:line-through; color:#94a3b8; }
    </style>
</head>
<body>

{{-- =========================================================
     KOP SURAT
     ========================================================= --}}
<table>
    <tr><td colspan="10" class="kop-title">FADILAH DIGITAL PRINTING</td></tr>
    <tr><td colspan="10" class="kop-sub">LAPORAN REKAPITULASI PENJUALAN &amp; REFUND GARANSI</td></tr>
    <tr><td colspan="10" class="kop-meta kop-line">
        Periode: <strong>{{ $periodLabel }}</strong> &nbsp;|&nbsp; Tanggal Ekspor: <strong>{{ date('d-m-Y H:i') }}</strong>
    </td></tr>
    <tr><td colspan="10" style="height:12px;"></td></tr>
</table>

{{-- =========================================================
     RINGKASAN KEUANGAN
     ========================================================= --}}
<table>
    <tr>
        <th colspan="3" class="sum-kotor">TOTAL PENDAPATAN KOTOR</th>
        <th colspan="3" class="sum-refund">TOTAL POTONGAN REFUND</th>
        <th colspan="4" class="sum-bersih">TOTAL PENDAPATAN BERSIH</th>
    </tr>
    <tr>
        <td colspan="3" class="sum-val-kotor">Rp {{ number_format($totalPendapatanKotor, 0, ',', '.') }}</td>
        <td colspan="3" class="sum-val-refund">- Rp {{ number_format($totalRefund, 0, ',', '.') }}</td>
        <td colspan="4" class="sum-val-bersih">Rp {{ number_format($totalPendapatanBersih, 0, ',', '.') }}</td>
    </tr>
    <tr><td colspan="10" style="height:18px;"></td></tr>
</table>

{{-- =========================================================
     TABEL 1: DAFTAR PENJUALAN
     ========================================================= --}}
<table>
    <tr><td colspan="10" class="sec-title">&#9312; DAFTAR PENJUALAN SELESAI</td></tr>
    <tr>
        <th class="th-dark" style="width:4%;">No</th>
        <th class="th-dark" style="width:14%;">Tanggal Selesai</th>
        <th class="th-dark" style="width:14%;">No Invoice</th>
        <th class="th-dark" style="width:16%;">Nama Pelanggan</th>
        <th class="th-dark" style="width:16%;">Email</th>
        <th class="th-dark" style="width:10%;">Metode Bayar</th>
        <th class="th-dark" style="width:9%;">Status Bayar</th>
        <th class="th-dark" style="width:6%;">Garansi</th>
        <th class="th-dark" style="width:9%;">Status Klaim</th>
        <th class="th-dark" style="width:12%; text-align:right;">Pendapatan Bersih</th>
    </tr>
    @forelse($orders as $i => $order)
        @php
            $isEven = $i % 2 === 0;
            $hasClaim   = $order->claim ? 'Ya'  : 'Tidak';
            $claimStatus = $order->claim ? strtoupper($order->claim->status) : '-';
            $isRefunded  = $order->claim && $order->claim->status === 'approved';
            $netValue    = $isRefunded ? 0 : $order->total_price;
        @endphp
        <tr class="{{ $isEven ? '' : 'row-even' }}">
            <td class="td-center">{{ $i + 1 }}</td>
            <td class="td-center">{{ $order->updated_at->format('d-m-Y H:i') }}</td>
            <td class="td-mono">{{ $order->invoice_number }}</td>
            <td class="td">{{ $order->user->name ?? 'Guest' }}</td>
            <td class="td" style="font-size:10px;">{{ $order->user->email ?? '-' }}</td>
            <td class="td-center">{{ strtoupper($order->payment_type ?: '-') }}</td>
            <td class="td-center">{{ strtoupper($order->payment_status) }}</td>
            <td class="td-center">{{ $hasClaim }}</td>
            <td class="td-center">{{ $claimStatus }}</td>
            <td class="td-right {{ $isRefunded ? 'val-red' : 'val-green' }}">
                @if($isRefunded)
                    <span class="val-strike">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span> | Rp 0
                @else
                    Rp {{ number_format($netValue, 0, ',', '.') }}
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="10" class="td row-empty">Tidak ada transaksi selesai pada periode ini.</td></tr>
    @endforelse
    <tr class="row-total-green">
        <td colspan="9" class="td-right" style="font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">
            Total Pendapatan Bersih
        </td>
        <td class="td-right val-green" style="font-size:12px;">
            Rp {{ number_format($totalPendapatanBersih, 0, ',', '.') }}
        </td>
    </tr>
    <tr><td colspan="10" style="height:18px;"></td></tr>
</table>

{{-- =========================================================
     TABEL 2: KLAIM GARANSI
     ========================================================= --}}
<table>
    <tr><td colspan="10" class="sec-title-red">&#9313; DAFTAR KLAIM GARANSI &amp; KOMPLAIN PELANGGAN</td></tr>
    <tr>
        <th class="th-red" style="width:4%;">No</th>
        <th class="th-red" style="width:13%;">No Invoice</th>
        <th class="th-red" style="width:14%;">Nama Pelanggan</th>
        <th class="th-red" style="width:12%;">Alasan Klaim</th>
        <th class="th-red" style="width:18%;">Deskripsi Komplain</th>
        <th class="th-red" style="width:10%;">Bank / E-Wallet</th>
        <th class="th-red" style="width:13%;">No Rekening / Wallet</th>
        <th class="th-red" style="width:12%;">Atas Nama</th>
        <th class="th-red" style="width:8%;">Status</th>
        <th class="th-red" style="width:10%; text-align:right;">Nominal Refund</th>
    </tr>
    @forelse($claims as $i => $claim)
        @php
            $isEven = $i % 2 === 0;
            $refundAmount = ($claim->status === 'approved') ? $claim->order->total_price : 0;
        @endphp
        <tr class="{{ $isEven ? '' : 'row-even' }}">
            <td class="td-center">{{ $i + 1 }}</td>
            <td class="td-mono">{{ $claim->order->invoice_number }}</td>
            <td class="td">{{ $claim->order->user->name ?? 'Guest' }}</td>
            <td class="td val-red">{{ $claim->reason }}</td>
            <td class="td" style="font-size:10px; color:#475569;">{{ $claim->description }}</td>
            <td class="td-center" style="font-weight:bold; text-transform:uppercase;">{{ $claim->bank_name ?? '-' }}</td>
            <td class="td-mono">{{ $claim->bank_account_number ?? '-' }}</td>
            <td class="td">{{ $claim->bank_account_name ?? '-' }}</td>
            <td class="td-center" style="font-weight:bold; color:{{ $claim->status === 'approved' ? '#15803d' : ($claim->status === 'rejected' ? '#b91c1c' : '#d97706') }};">
                {{ strtoupper($claim->status) }}
            </td>
            <td class="td-right val-red">
                Rp {{ number_format($refundAmount, 0, ',', '.') }}
            </td>
        </tr>
    @empty
        <tr><td colspan="10" class="td row-empty">Tidak ada pengajuan klaim garansi pada periode ini.</td></tr>
    @endforelse
    <tr class="row-total-red">
        <td colspan="9" class="td-right" style="font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#b91c1c;">
            Total Dana Di-Refund (Claims Approved)
        </td>
        <td class="td-right val-red" style="font-size:12px;">
            Rp {{ number_format($totalRefund, 0, ',', '.') }}
        </td>
    </tr>
</table>

</body>
</html>
