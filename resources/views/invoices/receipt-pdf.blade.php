<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $document['invoice']->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; color: #111; font-family: DejaVu Sans Mono, monospace; font-size: 10px; line-height: 1.45; }
        .receipt-paper { width: 100%; padding: 10px; }
        .text-center { text-align: center; }
        .brand { font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .line { border-top: 1px dashed #444; margin: 9px 0; }
        .row { display: table; width: 100%; }
        .left { display: table-cell; width: 46%; vertical-align: top; }
        .right { display: table-cell; width: 54%; text-align: right; vertical-align: top; font-weight: 700; }
        .small { font-size: 9px; }
        .total { font-size: 12px; font-weight: 700; }
    </style>
</head>
<body>
    @php
        $invoice = $document['invoice'];
        $payment = $document['payment'];
        $business = $document['business'];
        $member = $document['member'];
        $service = $document['service'];
        $labels = $document['labels'];
        $amounts = $document['amounts'];
    @endphp
    <main class="receipt-paper">
        <header class="text-center">
            <div class="brand">{{ $business['site_name'] }}</div>
            <div class="small">{{ $business['address'] }}</div>
            <div class="small">{{ $business['phone_display'] }}</div>
        </header>
        <div class="line"></div>
        <div class="row"><div class="left">No. Invoice</div><div class="right">{{ $invoice->invoice_number }}</div></div>
        <div class="row"><div class="left">Kode Bayar</div><div class="right">{{ $payment?->payment_code ?? '-' }}</div></div>
        <div class="row"><div class="left">Tanggal</div><div class="right">{{ $payment?->paid_at?->translatedFormat('d/m/Y H:i') ?? $invoice->issued_at?->translatedFormat('d/m/Y') ?? '-' }}</div></div>
        <div class="row"><div class="left">Status</div><div class="right">{{ $labels['paymentStatus'] }}</div></div>
        <div class="line"></div>
        <div class="row"><div class="left">Member</div><div class="right">{{ $member['name'] }}</div></div>
        <div class="row"><div class="left">Kode</div><div class="right">{{ $member['code'] }}</div></div>
        <div class="row"><div class="left">Metode</div><div class="right">{{ $labels['method'] }}</div></div>
        <div class="line"></div>
        <div><strong>{{ $service['name'] }}</strong></div>
        <div class="row"><div class="left">{{ $service['kind'] }}</div><div class="right">{{ $amounts['subtotal'] }}</div></div>
        <div class="line"></div>
        <div class="row"><div class="left">Subtotal</div><div class="right">{{ $amounts['subtotal'] }}</div></div>
        <div class="row"><div class="left">Diskon</div><div class="right">{{ $amounts['discount'] }}</div></div>
        <div class="row"><div class="left">Pajak</div><div class="right">{{ $amounts['tax'] }}</div></div>
        <div class="line"></div>
        <div class="row total"><div class="left">Total</div><div class="right">{{ $amounts['total'] }}</div></div>
        <div class="line"></div>
        <footer class="text-center small">
            <div>{{ $business['invoice_footer'] }}</div>
            <div>Simpan struk ini sebagai bukti transaksi.</div>
        </footer>
    </main>
</body>
</html>
