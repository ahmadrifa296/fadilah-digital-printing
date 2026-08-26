<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi Akun Anda</title>
    <style>
        /* Reset & Base Styles */
        body, html {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
            background-color: #f8fafc;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            display: block;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding: 40px 0;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
        }
        
        /* Header section with brand color accent */
        .accent-bar {
            height: 6px;
            background: linear-gradient(90deg, #f97316 0%, #ea580c 100%);
        }
        
        .header {
            padding: 32px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }
        .logo-img {
            max-height: 48px;
            margin: 0 auto 12px auto;
            display: inline-block;
        }
        .brand-name {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.025em;
        }
        .brand-sub {
            font-size: 10px;
            font-weight: 700;
            color: #f97316;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 2px;
        }
        
        /* Content area */
        .content {
            padding: 40px 32px;
            color: #334155;
            line-height: 1.6;
        }
        h2 {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }
        p {
            font-size: 14px;
            margin-top: 0;
            margin-bottom: 24px;
        }
        
        /* Button style */
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.25);
            transition: all 0.2s ease;
        }
        
        /* Warning box */
        .info-box {
            background-color: #f8fafc;
            border-left: 4px solid #cbd5e1;
            padding: 16px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 24px;
        }
        .info-text {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }
        
        /* Footer area */
        .footer {
            padding: 32px;
            background-color: #f8fafc;
            border-top: 1px solid #f1f5f9;
            text-align: center;
        }
        .footer-logo {
            max-height: 28px;
            opacity: 0.8;
            margin: 0 auto 12px auto;
            display: inline-block;
        }
        .footer-text {
            font-size: 11px;
            color: #94a3b8;
            margin: 0 0 6px 0;
            font-weight: 500;
        }
        .footer-link {
            color: #f97316;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="container" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <!-- Top Brand Strip -->
                <tr>
                    <td class="accent-bar"></td>
                </tr>
                
                <!-- Email Header -->
                <tr>
                    <td class="header">
                        @if(setting('company_logo'))
                            <img src="{{ url(setting('company_logo')) }}" alt="Logo" class="logo-img">
                        @endif
                        <h1 class="brand-name">Fadilah <span style="color: #f97316; font-weight: 500;">Printing</span></h1>
                        <div class="brand-sub">Layanan Cetak Premium</div>
                    </td>
                </tr>
                
                <!-- Email Body -->
                <tr>
                    <td class="content">
                        <h2>Halo, {{ $user->name }}</h2>
                        <p>Kami menerima permintaan untuk mengatur ulang kata sandi akun Fadilah Digital Printing Anda. Silakan klik tombol di bawah ini untuk melanjutkan:</p>
                        
                        <div class="btn-container">
                            <a href="{{ $url }}" class="btn-primary" target="_blank">Atur Ulang Kata Sandi</a>
                        </div>
                        
                        <div class="info-box">
                            <p class="info-text">Tautan ini hanya berlaku selama <strong>{{ $expire }} menit</strong>. Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini dan akun Anda akan tetap aman.</p>
                        </div>
                        
                        <p style="font-size: 13px; color: #64748b; margin-bottom: 0;">
                            Jika Anda mengalami masalah saat mengeklik tombol "Atur Ulang Kata Sandi", salin dan tempel URL berikut ke peramban web Anda:
                            <br>
                            <a href="{{ $url }}" style="color: #f97316; word-break: break-all; font-size: 12px;">{{ $url }}</a>
                        </p>
                    </td>
                </tr>
                
                <!-- Email Footer -->
                <tr>
                    <td class="footer">
                        @if(setting('company_logo'))
                            <img src="{{ url(setting('company_logo')) }}" alt="Logo" class="footer-logo">
                        @endif
                        <p class="footer-text">&copy; {{ date('Y') }} Fadilah Digital Printing. Hak Cipta Dilindungi.</p>
                        <p class="footer-text">
                            <a href="{{ url('/') }}" class="footer-link">{{ url('/') }}</a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
