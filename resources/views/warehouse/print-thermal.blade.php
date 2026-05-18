<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan Thermal — {{ $booking->booking_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #0f172a;
            color: #1e293b;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .toolbar {
            width: 100%;
            max-width: 80mm;
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        
        .btn {
            flex: 1;
            padding: 12px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-weight: 700;
            font-size: 14px;
            text-align: center;
            border-radius: 12px;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .btn-print {
            background-color: #2563eb;
            color: white;
        }

        .btn-print:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-close {
            background-color: #334155;
            color: #f1f5f9;
        }

        .btn-close:hover {
            background-color: #475569;
            transform: translateY(-1px);
        }

        .receipt-container {
            width: 100%;
            max-width: 80mm;
            background: #ffffff;
            padding: 20px 15px;
            border-radius: 4px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5), 0 8px 10px -6px rgba(0,0,0,0.5);
            box-sizing: border-box;
            position: relative;
        }

        .receipt-container::before {
            content: "";
            position: absolute;
            top: -8px;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(-45deg, transparent 4px, #ffffff 0), linear-gradient(45deg, transparent 4px, #ffffff 0);
            background-size: 8px 8px;
            background-repeat: repeat-x;
        }

        .receipt-container::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(-45deg, #ffffff 4px, transparent 0), linear-gradient(45deg, #ffffff 4px, transparent 0);
            background-size: 8px 8px;
            background-repeat: repeat-x;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 2px;
            margin: 0 0 5px 0;
            color: #000000;
        }

        .subtitle {
            font-size: 11px;
            margin: 0;
            color: #475569;
            text-transform: uppercase;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin: 15px 0 10px 0;
            letter-spacing: 1px;
        }

        .divider {
            border-top: 1px dashed #475569;
            margin: 10px 0;
            height: 0;
        }

        .double-divider {
            border-top: 1px dashed #475569;
            border-bottom: 1px dashed #475569;
            margin: 10px 0;
            height: 3px;
        }

        .info-table {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .info-table td.label {
            width: 35%;
            color: #475569;
        }

        .info-table td.value {
            font-weight: bold;
            color: #000000;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 12px 0;
        }

        .items-table th {
            text-align: left;
            font-weight: bold;
            padding: 5px 0;
            border-bottom: 1px dashed #475569;
        }

        .items-table td {
            padding: 6px 0;
            vertical-align: top;
            border-bottom: 1px dotted #e2e8f0;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .items-table th.qty-col, .items-table td.qty-col {
            text-align: right;
            width: 25%;
        }

        .item-sku {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        .qr-section {
            text-align: center;
            margin: 20px 0;
        }

        .qr-code {
            width: 120px;
            height: 120px;
            object-fit: contain;
            margin: 0 auto 5px auto;
            display: block;
        }

        .qr-desc {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            text-align: center;
        }

        .signature-box {
            width: 45%;
        }

        .signature-space {
            height: 50px;
        }

        .signature-line {
            border-bottom: 1px dashed #000000;
            width: 100%;
            margin: 0 auto 4px auto;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #64748b;
            margin-top: 30px;
            text-transform: uppercase;
        }

        @media print {
            body {
                background-color: transparent;
                padding: 0;
                margin: 0;
                display: block;
                color: #000000;
            }

            .toolbar {
                display: none !important;
            }

            .receipt-container {
                box-shadow: none;
                max-width: 100%;
                width: 100%;
                padding: 0;
                border-radius: 0;
            }

            .receipt-container::before, .receipt-container::after {
                display: none !important;
            }

            .divider, .double-divider {
                border-color: #000000;
            }

            .items-table th {
                border-bottom-color: #000000;
            }

            .items-table td {
                border-bottom-color: #cccccc;
            }

            @page {
                size: auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button class="btn btn-print" onclick="window.print()">🖨️ Print Surat Jalan</button>
        <button class="btn btn-close" onclick="window.close()">Tutup Halaman</button>
    </div>

    <div class="receipt-container">
        
        <div class="header">
            <h1 class="logo">DOCKFLOW</h1>
            <p class="subtitle">Logistics & Warehouse System</p>
            <p style="font-size: 9px; margin: 2px 0 0 0; color: #64748b;">Telp: +62 812-3456-7890</p>
        </div>

        <div class="divider"></div>

        <div class="title">SURAT JALAN / DELIVERY NOTE</div>

        <div class="double-divider"></div>

        <table class="info-table">
            <tr>
                <td class="label">No. Booking</td>
                <td class="value">: {{ $booking->booking_number }}</td>
            </tr>
            <tr>
                <td class="label">Tgl. Selesai</td>
                <td class="value">: {{ \Carbon\Carbon::parse($booking->updated_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="value">: SIAP DIKIRIM (PROCESSED)</td>
            </tr>
        </table>

        <div class="divider"></div>

        <table class="info-table">
            <tr>
                <td class="label">Pemesan</td>
                <td class="value">: {{ $booking->user?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Kapal</td>
                <td class="value">: {{ $booking->vessel?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Lokasi Dermaga</td>
                <td class="value">: {{ $booking->dock_location ?? '—' }}</td>
            </tr>
        </table>

        <div class="double-divider"></div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>NAMA BARANG</th>
                    <th class="qty-col">QTY</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->bookingDetails as $detail)
                <tr>
                    <td>
                        <div>{{ $detail->product?->name ?? 'Produk tidak ditemukan' }}</div>
                        <div class="item-sku">SKU: {{ $detail->product?->sku_code ?? '—' }}</div>
                    </td>
                    <td class="qty-col" style="font-weight: bold;">
                        {{ $detail->qty }} {{ $detail->product?->unit ?? 'unit' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="double-divider"></div>

        @if($booking->barcode)
        <div class="qr-section">
            <img class="qr-code" src="{{ asset('storage/' . $booking->barcode) }}" alt="QR Code">
            <span class="qr-desc">Scan untuk serah terima lapangan</span>
        </div>
        <div class="divider"></div>
        @endif

        <div class="signature-section">
            <div class="signature-box">
                <p style="margin: 0 0 5px 0; font-weight: bold;">Pengirim (Crew)</p>
                <div class="signature-space"></div>
                <div class="signature-line"></div>
                <p style="margin: 0; font-size: 9px; color: #64748b;">Nama Jelas</p>
            </div>
            
            <div class="signature-box">
                <p style="margin: 0 0 5px 0; font-weight: bold;">Penerima (Kapten)</p>
                <div class="signature-space"></div>
                <div class="signature-line"></div>
                <p style="margin: 0; font-size: 9px; color: #64748b;">Ttd & Cap Kapal</p>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0 0 5px 0; font-weight: bold;">*** DOCKFLOW LOGISTICS ***</p>
            <p style="margin: 0;">Terima kasih atas kerja samanya</p>
        </div>

    </div>

    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 600);
        }
    </script>
</body>
</html>
