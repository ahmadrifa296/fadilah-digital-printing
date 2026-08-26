<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333333;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
            color: #1a1a1a;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 11px;
            color: #666666;
        }
        .period-info {
            font-weight: bold;
            color: #d97706;
            margin-top: 5px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .summary-grid {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .summary-card {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            text-align: left;
        }
        .summary-card.kotor {
            background-color: #f1f5f9;
        }
        .summary-card.refund {
            background-color: #fff1f2;
            border-color: #ffe4e6;
        }
        .summary-card.bersih {
            background-color: #ecfdf5;
            border-color: #d1fae5;
        }
        .summary-title {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
        }
        .summary-card.refund .summary-value {
            color: #e11d48;
        }
        .summary-card.bersih .summary-value {
            color: #059669;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e293b;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            letter-spacing: 0.5px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-data th {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            font-size: 9px;
            color: #475569;
        }
        .table-data td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            vertical-align: top;
            font-size: 10px;
        }
        .table-data tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: Courier, monospace;
        }
        .font-bold {
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #d97706;
        }
        .badge-approved {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .badge-rejected {
            background-color: #f1f5f9;
            color: #475569;
        }
        .footer-sig {
            margin-top: 50px;
            width: 100%;
        }
        .footer-sig td {
            border: none;
            width: 50%;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Fadilah Digital Printing</h1>
        <p>Laporan Rekapitulasi Pendapatan Penjualan</p>
        <div class="period-info">
            Periode: 
            @if($period === '1_month') 1 Bulan Terakhir
            @elseif($period === '3_months') 3 Bulan Terakhir
            @elseif($period === '6_months') 6 Bulan Terakhir
            @elseif($period === '1_year') 1 Tahun Terakhir
            @else Keseluruhan (Semua Periode)
            @endif
        </div>
        <p style="margin-top: 6px; font-size: 9px; color: #999999;">Dicetak pada: {{ date('d F Y, H:i') }}</p>
    </div>

    <!-- Summary Cards Table (Grid simulation for DomPDF) -->
    <table class="summary-grid">
        <tr>
            <td style="width: 32%; padding-right: 10px;">
                <div class="summary-card kotor">
                    <div class="summary-title">Total Pendapatan Kotor</div>
                    <div class="summary-value">Rp {{ number_format($totalPendapatanKotor, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 32%; padding-right: 10px; padding-left: 10px;">
                <div class="summary-card refund">
                    <div class="summary-title">Potongan Refund Garansi</div>
                    <div class="summary-value">- Rp {{ number_format($totalRefund, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 32%; padding-left: 10px;">
                <div class="summary-card bersih">
                    <div class="summary-title">Total Pendapatan Bersih</div>
                    <div class="summary-value">Rp {{ number_format($totalPendapatanBersih, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabel 1: Rekapitulasi Penjualan Utama -->
    <div class="section-title">1. Daftar Penjualan Selesai</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 20%;">Invoice</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 25%;">Pelanggan</th>
                <th style="width: 15%;">Klaim Garansi</th>
                <th style="width: 20%; text-align: right;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-mono font-bold">{{ $item->invoice_number }}</td>
                    <td>{{ $item->updated_at->format('d M Y') }}</td>
                    <td>
                        <div>{{ $item->user->name ?? 'Guest' }}</div>
                        <div style="font-size: 8px; color: #666666;">{{ $item->user->email ?? '-' }}</div>
                    </td>
                    <td>
                        @if($item->claim)
                            @if($item->claim->status === 'approved')
                                <span class="badge badge-approved">Refunded</span>
                            @elseif($item->claim->status === 'rejected')
                                <span class="badge badge-rejected">Ditolak</span>
                            @else
                                <span class="badge badge-pending">Pending</span>
                            @endif
                        @else
                            <span style="color: #999999;">-</span>
                        @endif
                    </td>
                    <td class="text-right font-bold">
                        @if($item->claim && $item->claim->status === 'approved')
                            <span style="text-decoration: line-through; color: #999999; font-weight: normal; font-size: 9px;">Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                            <br><span style="color: #b91c1c;">Rp 0</span>
                        @else
                            <span>Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: #999999; font-style: italic; padding: 20px;">
                        Tidak ada transaksi dengan status pesanan selesai pada periode ini.
                    </td>
                </tr>
            @endforelse
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="5" class="text-right" style="text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px; padding: 10px;">Total Pendapatan Bersih</td>
                <td class="text-right" style="font-size: 11px; padding: 10px; color: #059669;">
                    Rp {{ number_format($totalPendapatanBersih, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Tabel 2: Rekapitulasi Klaim Garansi & Komplain -->
    <div class="section-title" style="color: #b91c1c;">2. Rekapitulasi Klaim Garansi & Komplain</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 15%;">Invoice</th>
                <th style="width: 20%;">Pelanggan</th>
                <th style="width: 20%;">Alasan Komplain</th>
                <th style="width: 25%;">Informasi Rekening Refund</th>
                <th style="width: 15%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($claims as $index => $c)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-mono font-bold">{{ $c->order->invoice_number }}</td>
                    <td>
                        <div>{{ $c->order->user->name ?? 'Guest' }}</div>
                        <div style="font-size: 8px; color: #666666;">{{ $c->order->user->email ?? '-' }}</div>
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #b91c1c;">{{ $c->reason }}</div>
                        <div style="font-size: 8px; color: #666666; line-height: 1.2;">{{ $c->description }}</div>
                    </td>
                    <td style="font-size: 9px; line-height: 1.3;">
                        <div>Bank: <span style="font-weight: bold; text-transform: uppercase;">{{ $c->bank_name ?? '-' }}</span></div>
                        <div>No: <span style="font-weight: bold; font-family: Courier, monospace;">{{ $c->bank_account_number ?? '-' }}</span></div>
                        <div>Nama: <span style="font-weight: bold;">{{ $c->bank_account_name ?? '-' }}</span></div>
                    </td>
                    <td class="text-center">
                        @if($c->status === 'approved')
                            <span class="badge badge-approved" style="background-color: #d1fae5; color: #065f46;">Disetujui</span>
                        @elseif($c->status === 'rejected')
                            <span class="badge badge-rejected" style="background-color: #fee2e2; color: #991b1b;">Ditolak</span>
                        @else
                            <span class="badge badge-pending">Pending</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: #999999; font-style: italic; padding: 20px;">
                        Tidak ada pengajuan klaim garansi/komplain pada periode ini.
                    </td>
                </tr>
            @endforelse
            <tr style="background-color: #fef2f2; font-weight: bold;">
                <td colspan="5" class="text-right" style="text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px; padding: 10px; color: #991b1b;">Total Uang Di-Refund (Klaim Disetujui)</td>
                <td class="text-right" style="font-size: 11px; padding: 10px; color: #b91c1c;">
                    Rp {{ number_format($totalRefund, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="footer-sig">
        <tr>
            <td></td>
            <td>
                <p style="margin-bottom: 60px;">Mengetahui,</p>
                <p style="font-weight: bold; text-decoration: underline; margin-bottom: 2px;">Pemilik Fadilah Printing</p>
                <p style="font-size: 9px; color: #666666; margin: 0;">Fadilah Digital Printing</p>
            </td>
        </tr>
    </table>

</body>
</html>
