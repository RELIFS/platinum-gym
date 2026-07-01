@php
    /** @var array<string, mixed> $document */
    $invoice = $document['invoice'];
    $payment = $document['payment'];
    $business = $document['business'];
    $member = $document['member'];
    $service = $document['service'];
    $labels = $document['labels'];
    $amounts = $document['amounts'];
    $buttonClass = ($document['viewerRole'] ?? '') === 'Member' ? 'member-button-secondary' : 'owner-button-secondary';
    $cardClass = ($document['viewerRole'] ?? '') === 'Member' ? 'member-card' : 'owner-card';
    $panelClass = ($document['viewerRole'] ?? '') === 'Member' ? 'member-soft-panel' : 'owner-panel';
    $eyebrowClass = ($document['viewerRole'] ?? '') === 'Member' ? 'member-eyebrow' : 'owner-eyebrow';
    if (($document['viewerRole'] ?? '') === 'Admin') {
        $buttonClass = 'admin-button-secondary';
        $cardClass = 'admin-card';
        $panelClass = 'admin-panel';
        $eyebrowClass = 'admin-eyebrow';
    }
    $actions = $document['actions'] ?? [];
@endphp

<section class="{{ $cardClass }} relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/70 to-transparent" aria-hidden="true"></div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <p class="{{ $eyebrowClass }}">Invoice Transaksi</p>
            <h2 class="mt-2 break-words font-mono text-2xl font-black text-zinc-950 dark:text-white sm:text-3xl">{{ $invoice->invoice_number }}</h2>
            <p class="mt-2 text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $business['site_name'] }}</p>
        </div>
        <div class="flex flex-col gap-2 sm:items-end">
            <span class="admin-status-pill admin-status-success">{{ $labels['invoiceStatus'] }}</span>
            <div class="grid w-full gap-2 sm:w-auto sm:grid-cols-2">
                @if ($actions['receipt'] ?? null)
                    <a href="{{ $actions['receipt'] }}" class="{{ $buttonClass }} justify-center">Lihat Struk</a>
                @endif
                @if ($actions['download'] ?? null)
                    <a href="{{ $actions['download'] }}" class="{{ $buttonClass }} justify-center">Unduh PDF</a>
                @endif
                <button type="button" onclick="window.print()" class="{{ $buttonClass }} justify-center">Cetak</button>
                <a href="{{ $backUrl }}" class="{{ $buttonClass }} justify-center">Kembali</a>
            </div>
        </div>
    </div>
</section>

<section class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
    <div class="{{ $cardClass }}">
        <div class="grid min-w-0 gap-5 md:grid-cols-2">
            <div class="min-w-0">
                <p class="{{ $eyebrowClass }}">Ditagihkan kepada</p>
                <div class="{{ $panelClass }} mt-3 min-w-0">
                    <p class="font-black text-zinc-950 dark:text-white">{{ $member['name'] }}</p>
                    <p class="mt-1 font-mono text-xs font-bold text-zinc-500 dark:text-zinc-400">{{ $member['code'] }}</p>
                    @if ($member['email'])
                        <p class="mt-3 break-words text-sm font-semibold text-zinc-600 dark:text-zinc-300">{{ $member['email'] }}</p>
                    @endif
                </div>
            </div>

            <div class="min-w-0">
                <p class="{{ $eyebrowClass }}">Informasi gym</p>
                <div class="{{ $panelClass }} mt-3 min-w-0">
                    <p class="font-black text-zinc-950 dark:text-white">{{ $business['site_name'] }}</p>
                    <p class="mt-2 text-sm font-semibold leading-6 text-zinc-600 dark:text-zinc-300">{{ $business['address'] }}</p>
                    <p class="mt-2 text-sm font-semibold text-zinc-600 dark:text-zinc-300">{{ $business['phone_display'] }}</p>
                    <p class="mt-1 break-words text-sm font-semibold text-zinc-600 dark:text-zinc-300">{{ $business['public_email'] }}</p>
                </div>
            </div>
        </div>

        <div class="{{ $panelClass }} mt-6 xl:hidden">
            <p class="{{ $eyebrowClass }}">Detail layanan</p>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="font-semibold text-zinc-500 dark:text-zinc-400">Layanan</dt>
                    <dd class="mt-1 break-words font-black text-zinc-950 dark:text-white">{{ $service['name'] }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-zinc-500 dark:text-zinc-400">Jenis</dt>
                    <dd class="mt-1 font-black text-zinc-950 dark:text-white">{{ $service['kind'] }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-t border-zinc-200 pt-3 dark:border-white/10">
                    <dt class="font-semibold text-zinc-500 dark:text-zinc-400">Nominal</dt>
                    <dd class="text-right font-black text-zinc-950 dark:text-white">{{ $amounts['subtotal'] }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-6 hidden overflow-hidden rounded-lg border border-zinc-200 dark:border-white/10 xl:block">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">Detail layanan invoice</caption>
                <thead class="admin-table-head">
                    <tr>
                        <th scope="col" class="px-4 py-3">Layanan</th>
                        <th scope="col" class="px-4 py-3">Jenis</th>
                        <th scope="col" class="px-4 py-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    <tr class="admin-table-row">
                        <td class="admin-table-cell">{{ $service['name'] }}</td>
                        <td class="admin-table-cell">{{ $service['kind'] }}</td>
                        <td class="admin-table-cell text-right">{{ $amounts['subtotal'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="mt-5 text-sm font-semibold leading-7 text-zinc-500 dark:text-zinc-400">{{ $business['invoice_footer'] }}</p>
    </div>

    <aside class="{{ $cardClass }}">
        <p class="{{ $eyebrowClass }}">Ringkasan</p>
        <dl class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="font-semibold text-zinc-500 dark:text-zinc-400">Kode pembayaran</dt>
                <dd class="break-all text-right font-mono font-black text-zinc-950 dark:text-white">{{ $payment?->payment_code ?? '-' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="font-semibold text-zinc-500 dark:text-zinc-400">Tanggal invoice</dt>
                <dd class="text-right font-black text-zinc-950 dark:text-white">{{ $invoice->issued_at?->translatedFormat('d M Y') ?? '-' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="font-semibold text-zinc-500 dark:text-zinc-400">Tanggal bayar</dt>
                <dd class="text-right font-black text-zinc-950 dark:text-white">{{ $payment?->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="font-semibold text-zinc-500 dark:text-zinc-400">Metode</dt>
                <dd class="text-right font-black text-zinc-950 dark:text-white">{{ $labels['method'] }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="font-semibold text-zinc-500 dark:text-zinc-400">Status pembayaran</dt>
                <dd class="text-right font-black text-zinc-950 dark:text-white">{{ $labels['paymentStatus'] }}</dd>
            </div>
        </dl>

        <div class="{{ $panelClass }} mt-5">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="font-semibold text-zinc-500 dark:text-zinc-400">Subtotal</dt>
                    <dd class="font-black text-zinc-950 dark:text-white">{{ $amounts['subtotal'] }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="font-semibold text-zinc-500 dark:text-zinc-400">Diskon</dt>
                    <dd class="font-black text-zinc-950 dark:text-white">{{ $amounts['discount'] }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="font-semibold text-zinc-500 dark:text-zinc-400">Pajak</dt>
                    <dd class="font-black text-zinc-950 dark:text-white">{{ $amounts['tax'] }}</dd>
                </div>
                <div class="border-t border-zinc-200 pt-3 dark:border-white/10">
                    <div class="flex justify-between gap-4">
                        <dt class="font-black text-zinc-950 dark:text-white">Total</dt>
                        <dd class="font-black text-gold-600 dark:text-gold-400">{{ $amounts['total'] }}</dd>
                    </div>
                </div>
            </dl>
        </div>
    </aside>
</section>
