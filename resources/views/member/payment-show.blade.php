@php
    $payable = $payment->payable;
    $serviceName = $payable?->package?->name ?? $payable?->schedule?->gymClass?->name ?? 'Layanan Platinum Gym';
    $canPay = in_array($payment->status, ['waiting_payment', 'pending', 'unpaid'], true) && filled($payment->midtrans_redirect_url);
    $paymentStatusLabel = match ((string) $payment->status) {
        'waiting_payment', 'pending', 'unpaid' => 'Menunggu Bayar',
        'waiting_confirmation' => 'Menunggu Konfirmasi',
        'paid' => 'Lunas',
        'rejected' => 'Ditolak',
        'failed' => 'Gagal',
        'expired' => 'Kedaluwarsa',
        'cancelled', 'canceled' => 'Dibatalkan',
        default => str((string) $payment->status)->headline()->toString(),
    };
    $paymentStatusClass = match ((string) $payment->status) {
        'paid' => 'member-status-success',
        'waiting_payment', 'pending', 'unpaid', 'waiting_confirmation' => 'member-status-warning',
        'rejected', 'failed', 'expired', 'cancelled', 'canceled' => 'member-status-danger',
        default => 'member-status-neutral',
    };
    $invoiceStatusLabel = match ((string) $payment->invoice?->status) {
        'issued' => 'Diterbitkan',
        'paid' => 'Lunas',
        'rejected' => 'Ditolak',
        'cancelled', 'canceled' => 'Dibatalkan',
        default => $payment->invoice ? str((string) $payment->invoice->status)->headline()->toString() : null,
    };
@endphp

<x-member-layout :portal="$portal" title="Detail Transaksi">
    <section class="member-card-strong relative isolate overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/70 to-transparent" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-gold-400">Transaksi Member</p>
                <h2 class="mt-3 text-3xl font-black leading-tight tracking-tight text-white sm:text-4xl">{{ $payment->payment_code }}</h2>
                <p class="mt-4 text-sm font-medium leading-7 text-zinc-300">{{ $serviceName }}</p>
            </div>
            <a href="{{ route('member.transactions') }}" class="member-button-primary">Semua Transaksi</a>
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="member-card">
            <p class="member-eyebrow">Pembayaran</p>
            <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Status transaksi</h3>
            <dl class="member-soft-panel mt-5 grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="font-semibold text-zinc-500">Layanan</dt>
                    <dd class="mt-1 font-black text-zinc-950 dark:text-white">{{ $serviceName }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-zinc-500">Status</dt>
                    <dd class="mt-1"><span class="member-status-pill {{ $paymentStatusClass }}">{{ $paymentStatusLabel }}</span></dd>
                </div>
                <div>
                    <dt class="font-semibold text-zinc-500">Metode</dt>
                    <dd class="mt-1 font-black text-zinc-950 dark:text-white">{{ str((string) $payment->method)->headline() }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-zinc-500">Nominal</dt>
                    <dd class="mt-1 font-black text-gold-600 dark:text-gold-400">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-zinc-500">Dibuat</dt>
                    <dd class="mt-1 font-black text-zinc-950 dark:text-white">{{ $payment->created_at?->translatedFormat('d M Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-zinc-500">Kedaluwarsa</dt>
                    <dd class="mt-1 font-black text-zinc-950 dark:text-white">{{ $payment->expires_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd>
                </div>
            </dl>

            @if ($payment->failure_reason || $payment->rejected_reason)
                <div class="mt-5 rounded-lg border border-red-500/30 bg-red-500/10 p-4 text-sm font-semibold text-red-700 dark:text-red-200">
                    {{ $payment->failure_reason ?? $payment->rejected_reason }}
                </div>
            @endif
        </section>

        <aside class="member-card">
            <p class="member-eyebrow">Aksi</p>
            <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Lanjutkan proses</h3>
            @if ($canPay)
                <form method="POST" action="{{ route('member.transactions.pay', $payment) }}" class="mt-5">
                    @csrf
                    <button type="submit" class="member-button-primary w-full">Bayar via Midtrans</button>
                </form>
                <p class="mt-3 text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Anda akan diarahkan ke halaman pembayaran Midtrans Sandbox.</p>
            @else
                <div class="member-soft-panel mt-5">
                    <p class="font-black text-zinc-950 dark:text-white">Tidak ada pembayaran yang perlu dilanjutkan.</p>
                    <p class="mt-2 member-copy">Status transaksi saat ini: {{ $paymentStatusLabel }}.</p>
                </div>
            @endif

            @if ($payment->invoice)
                <div class="member-soft-panel mt-4">
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Invoice</p>
                    <p class="mt-2 font-mono text-sm font-black text-zinc-950 dark:text-white">{{ $payment->invoice->invoice_number }}</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $invoiceStatusLabel }}</p>
                </div>
            @endif
        </aside>
    </div>
</x-member-layout>
