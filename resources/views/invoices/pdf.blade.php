@php
    $invoice = $document['invoice'];
    $payment = $document['payment'];
    $business = $document['business'];
    $member = $document['member'];
    $service = $document['service'];
    $labels = $document['labels'];
    $amounts = $document['amounts'];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #18181b; font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; line-height: 1.5; }
        .page { padding: 34px; }
        .header { border-bottom: 3px solid #18181b; padding-bottom: 18px; }
        .brand { font-size: 22px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .muted { color: #52525b; }
        .title { margin-top: 24px; font-size: 24px; font-weight: 700; }
        .grid { width: 100%; margin-top: 18px; }
        .grid td { width: 50%; vertical-align: top; padding-right: 16px; }
        .box { border: 1px solid #d4d4d8; padding: 12px; min-height: 108px; }
        .label { color: #71717a; font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        table.items { width: 100%; margin-top: 22px; border-collapse: collapse; }
        .items th { background: #f4f4f5; border: 1px solid #d4d4d8; padding: 9px; text-align: left; font-size: 10px; text-transform: uppercase; }
        .items td { border: 1px solid #e4e4e7; padding: 9px; vertical-align: top; }
        .right { text-align: right; }
        .totals { margin-left: auto; margin-top: 20px; width: 260px; border-collapse: collapse; }
        .totals td { border-bottom: 1px solid #e4e4e7; padding: 7px 0; }
        .total { font-size: 16px; font-weight: 700; }
        .footer { border-top: 1px solid #d4d4d8; color: #52525b; margin-top: 28px; padding-top: 12px; }
    </style>
</head>
<body>
    <main class="page">
        <header class="header">
            <div class="brand">{{ $business['site_name'] }}</div>
            <div class="muted">{{ $business['address'] }}</div>
            <div class="muted">{{ $business['phone_display'] }} | {{ $business['public_email'] }}</div>
        </header>

        <div class="title">Invoice Transaksi</div>
        <p><strong>{{ $invoice->invoice_number }}</strong> | Status: {{ $labels['invoiceStatus'] }}</p>

        <table class="grid">
            <tr>
                <td>
                    <div class="box">
                        <div class="label">Ditagihkan kepada</div>
                        <p><strong>{{ $member['name'] }}</strong><br>{{ $member['code'] }}</p>
                        @if ($member['email'])
                            <p class="muted">{{ $member['email'] }}</p>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="box">
                        <div class="label">Ringkasan pembayaran</div>
                        <p>Kode: <strong>{{ $payment?->payment_code ?? '-' }}</strong></p>
                        <p>Tanggal bayar: {{ $payment?->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}</p>
                        <p>Metode: {{ $labels['method'] }} | Status: {{ $labels['paymentStatus'] }}</p>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Jenis</th>
                    <th class="right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $service['name'] }}</td>
                    <td>{{ $service['kind'] }}</td>
                    <td class="right">{{ $amounts['subtotal'] }}</td>
                </tr>
            </tbody>
        </table>

        <table class="totals">
            <tr><td>Subtotal</td><td class="right">{{ $amounts['subtotal'] }}</td></tr>
            <tr><td>Diskon</td><td class="right">{{ $amounts['discount'] }}</td></tr>
            <tr><td>Pajak</td><td class="right">{{ $amounts['tax'] }}</td></tr>
            <tr class="total"><td>Total</td><td class="right">{{ $amounts['total'] }}</td></tr>
        </table>

        <footer class="footer">{{ $business['invoice_footer'] }}</footer>
    </main>
</body>
</html>

