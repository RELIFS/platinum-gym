@php
    $invoice = $document['invoice'];
    $payment = $document['payment'];
    $business = $document['business'];
    $member = $document['member'];
    $service = $document['service'];
    $labels = $document['labels'];
    $amounts = $document['amounts'];
@endphp

<article class="receipt-paper mx-auto w-full max-w-[320px] bg-white p-5 font-mono text-[12px] leading-5 text-zinc-950 shadow-xl">
    <header class="text-center">
        <h1 class="text-base type-title uppercase tracking-wide">{{ $business['site_name'] }}</h1>
        <p class="mt-1 text-[11px] leading-4">{{ $business['address'] }}</p>
        <p class="text-[11px]">{{ $business['phone_display'] }}</p>
    </header>

    <div class="my-4 border-t border-dashed border-zinc-500"></div>

    <dl class="space-y-1">
        <div class="flex justify-between gap-3">
            <dt>No. Invoice</dt>
            <dd class="text-right type-control">{{ $invoice->invoice_number }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt>Kode Bayar</dt>
            <dd class="text-right type-control">{{ $payment?->payment_code ?? '-' }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt>Tanggal</dt>
            <dd class="text-right">{{ $payment?->paid_at?->translatedFormat('d/m/Y H:i') ?? $invoice->issued_at?->translatedFormat('d/m/Y') ?? '-' }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt>Status</dt>
            <dd class="text-right type-control uppercase">{{ $labels['paymentStatus'] }}</dd>
        </div>
    </dl>

    <div class="my-4 border-t border-dashed border-zinc-500"></div>

    <dl class="space-y-1">
        <div class="flex justify-between gap-3">
            <dt>Member</dt>
            <dd class="text-right type-control">{{ $member['name'] }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt>Kode</dt>
            <dd class="text-right">{{ $member['code'] }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt>Metode</dt>
            <dd class="text-right">{{ $labels['method'] }}</dd>
        </div>
    </dl>

    <div class="my-4 border-t border-dashed border-zinc-500"></div>

    <div>
        <div class="flex justify-between gap-3">
            <div>
                <p class="type-control">{{ $service['name'] }}</p>
                <p class="text-[11px]">{{ $service['kind'] }}</p>
            </div>
            <p class="shrink-0 type-control">{{ $amounts['subtotal'] }}</p>
        </div>
    </div>

    <div class="my-4 border-t border-dashed border-zinc-500"></div>

    <dl class="space-y-1">
        <div class="flex justify-between gap-3">
            <dt>Subtotal</dt>
            <dd>{{ $amounts['subtotal'] }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt>Diskon</dt>
            <dd>{{ $amounts['discount'] }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt>Pajak</dt>
            <dd>{{ $amounts['tax'] }}</dd>
        </div>
        <div class="mt-2 flex justify-between gap-3 border-t border-dashed border-zinc-500 pt-2 text-sm type-control">
            <dt>Total</dt>
            <dd>{{ $amounts['total'] }}</dd>
        </div>
    </dl>

    <div class="my-4 border-t border-dashed border-zinc-500"></div>

    <footer class="text-center text-[11px] leading-4">
        <p>{{ $business['invoice_footer'] }}</p>
        <p class="mt-2">Simpan struk ini sebagai bukti transaksi.</p>
    </footer>
</article>
