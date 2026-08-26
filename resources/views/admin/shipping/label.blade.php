<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Label Pengiriman - {{ $order->invoice_number }}</title>
    <style>
        @page {
            size: 105mm 148mm; /* A6 Size */
            margin: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 8.5px;
            color: #000;
            line-height: 1.15;
            background-color: #fff;
        }
        .container {
            border: 2px solid #000;
            margin: 3mm;
            height: 140mm; /* strictly fit A6 (148mm) single page including margin */
            box-sizing: border-box;
            overflow: hidden;
        }
        .table-border {
            border-bottom: 2px solid #000;
        }
        .table-border-dashed {
            border-bottom: 2px dashed #000;
        }
        /* Grid Tables */
        .table-layout {
            border-collapse: collapse;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .header-cell {
            padding: 5px 6px;
            vertical-align: middle;
        }
        .logo-text {
            font-size: 10px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }
        .logo-img {
            max-height: 18px;
            display: block;
        }
        .header-center {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            border-left: 1px dashed #000;
            border-right: 1px dashed #000;
        }
        .header-right {
            font-size: 11px;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
        }
        /* Resi Box */
        .resi-box {
            border-bottom: 2px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
        }
        /* Barcode Area */
        .barcode-section {
            text-align: center;
            padding: 6px 0;
            border-bottom: 2px dashed #000;
        }
        .barcode-img {
            max-height: 36px;
            width: 90%;
            display: inline-block;
        }
        /* Address Area */
        .address-cell {
            width: 50%;
            vertical-align: top;
            padding: 5px;
            box-sizing: border-box;
        }
        .border-right {
            border-right: 2px dashed #000;
        }
        .address-label {
            font-weight: bold;
        }
        .address-text {
            margin-top: 2px;
            font-size: 7.5px;
            line-height: 1.2;
        }
        /* Region tags */
        .region-table {
            border-collapse: collapse;
            width: 100%;
            border-bottom: 2px solid #000;
        }
        .region-cell {
            width: 50%;
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }
        /* Cashless banner */
        .banner-table {
            border-collapse: collapse;
            width: 100%;
            border-bottom: 2px solid #000;
        }
        .banner-left {
            width: 25%;
            border-right: 1px solid #000;
            padding: 3px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
        }
        .banner-right {
            width: 75%;
            padding: 3px;
            font-size: 7.5px;
            font-weight: bold;
            color: #444;
        }
        /* Meta info section */
        .meta-table {
            border-collapse: collapse;
            width: 100%;
            border-bottom: 2px solid #000;
        }
        .meta-left {
            width: 55%;
            padding: 5px;
            vertical-align: top;
            line-height: 1.35;
        }
        .meta-right {
            width: 45%;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
            border-left: 2px dashed #000;
        }
        .meta-barcode {
            max-height: 22px;
            width: 80%;
            margin-bottom: 1px;
            display: inline-block;
        }
        /* Items section */
        .items-section {
            padding: 4px 6px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
        }
        .items-table th {
            border-bottom: 1px solid #000;
            text-align: left;
            padding: 1px;
            font-weight: bold;
        }
        .items-table td {
            padding: 1px;
            vertical-align: top;
        }
        .items-footer {
            margin-top: 3px;
            font-size: 7px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <table class="table-layout table-border">
            <tr>
                <td class="header-cell logo-cell">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" class="logo-img">
                    @else
                        <div class="logo-text">FADILAH PRINTING</div>
                    @endif
                </td>
                <td class="header-cell header-center">
                    {{ strtoupper($shipment->service) }}
                </td>
                <td class="header-cell header-right">
                    {{ strtoupper($shipment->courier) }}
                </td>
            </tr>
        </table>

        <!-- Resi Box -->
        <div class="resi-box">
            No. Resi: {{ $shipment->tracking_number }}
        </div>

        <!-- Giant Barcode (Local SVG) -->
        <div class="barcode-section">
            <img src="{{ $trackingBarcode }}" class="barcode-img">
        </div>

        <!-- Destination / Origin Addresses -->
        <table class="table-layout table-border-dashed">
            <tr>
                <td class="address-cell border-right">
                    <div><span class="address-label">Penerima:</span> {{ $order->receiver_name }}</div>
                    <div class="address-text">
                        {{ $order->phone }}<br>
                        {{ $order->full_address }}
                    </div>
                </td>
                <td class="address-cell">
                    <div><span class="address-label">Pengirim:</span> {{ $shipperName }}</div>
                    <div class="address-text">
                        {{ $shipperPhone }}<br>
                        {{ $shipperAddress }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Destination City / District Tags -->
        <table class="region-table">
            <tr>
                <td class="region-cell">{{ $order->city }}</td>
                <td class="region-cell">{{ $order->district }}</td>
            </tr>
        </table>

        <!-- Cashless / Payment Instruction Banner -->
        <table class="banner-table">
            <tr>
                <td class="banner-left">
                    @if($order->shipping_courier === 'pickup')
                        PICKUP
                    @else
                        CASHLESS
                    @endif
                </td>
                <td class="banner-right">
                    @if($order->shipping_courier === 'pickup')
                        Pelanggan mengambil sendiri barang di Toko Fadilah Digital Printing.
                    @else
                        Penjual tidak perlu bayar ongkir ke Kurir
                    @endif
                </td>
            </tr>
        </table>

        <!-- Metadata Section (Local Barcode) -->
        <table class="meta-table">
            <tr>
                <td class="meta-left">
                    <strong>Berat:</strong> {{ number_format($totalWeightKg * 1000, 0, ',', '.') }} gr<br>
                    <strong>COD:</strong> Rp0<br>
                    <strong>Batas Kirim:</strong> {{ $order->created_at->addDays(2)->format('d-m-Y') }}<br>
                    <strong>No. Pesanan:</strong> {{ $order->invoice_number }}
                </td>
                <td class="meta-right">
                    <img src="{{ $orderBarcode }}" class="meta-barcode"><br>
                    <span style="font-size: 7px; font-weight: bold; font-family: monospace;">{{ $order->invoice_number }}</span>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 55%">Nama Produk</th>
                        <th style="width: 30%">Variasi</th>
                        <th style="width: 10%">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->orderDetails as $index => $detail)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ $detail->product->name }}
                                @if($detail->custom_length && $detail->custom_width)
                                    ({{ $detail->custom_length }}x{{ $detail->custom_width }} m)
                                @endif
                            </td>
                            <td>
                                @if($detail->type_file)
                                    {{ $detail->type_file === 'ready' ? 'Ready Stock' : 'Custom Desain' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $detail->qty }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="items-footer">
                Pesan: ({{ $order->invoice_number }})
            </div>
        </div>
    </div>
</body>
</html>
