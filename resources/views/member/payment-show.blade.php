@php
    $payable = $payment->payable;
    $serviceName = $payable?->package?->name ?? $payable?->schedule?->gymClass?->name ?? 'Layanan Platinum Gym';
    $paymentStatus = $paymentStatus ?? ['label' => str((string) $payment->status)->headline()->toString(), 'class' => 'member-status-neutral', 'can_pay' => false];
    $canPay = (bool) ($paymentStatus['can_pay'] ?? false);
    $paymentStatusLabel = $paymentStatus['label'];
    $paymentStatusClass = $paymentStatus['class'];
    $invoiceStatusLabel = $invoiceStatusLabel ?? null;
    $expiresAtIso = $payment->expires_at?->toIso8601String();
    $isWaitingPayment = $payment->status === 'waiting_payment';
    $showCountdown = $isWaitingPayment && filled($expiresAtIso);
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

            @if ($showCountdown)
                <div
                    class="mt-5 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3"
                    role="status"
                    aria-live="polite"
                    x-data="memberPaymentCountdown('{{ $expiresAtIso }}')"
                    x-init="start()"
                    x-on:visibilitychange.window="onVisibility()"
                >
                    <p class="text-xs font-black uppercase tracking-[0.14em] text-amber-700 dark:text-amber-300">Sisa Waktu Pembayaran</p>
                    <p class="mt-2 font-mono text-2xl font-black text-amber-800 dark:text-amber-200" x-text="display"></p>
                    <p class="mt-1 text-xs font-semibold leading-5 text-amber-700/80 dark:text-amber-200/80" x-show="!expired">Selesaikan pembayaran sebelum waktu habis. Halaman akan menampilkan status terbaru saat dibuka kembali.</p>
                    <p class="mt-1 text-xs font-bold leading-5 text-red-700 dark:text-red-300" x-show="expired" x-cloak>Waktu pembayaran sudah habis. Status transaksi akan diperbarui setelah halaman direfresh.</p>
                </div>
            @endif

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
                <p class="mt-3 text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Anda akan diarahkan ke halaman pembayaran Midtrans.</p>
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

    @if ($showCountdown)
        <script>
            if (typeof window.memberPaymentCountdown === 'undefined') {
                window.memberPaymentCountdown = function (expiresIso) {
                    return {
                        expiresAt: new Date(expiresIso).getTime(),
                        display: '--:--:--',
                        expired: false,
                        timer: null,
                        start() {
                            this.tick();
                            this.timer = setInterval(() => this.tick(), 1000);
                        },
                        tick() {
                            const now = Date.now();
                            const diff = this.expiresAt - now;
                            if (Number.isNaN(this.expiresAt) || diff <= 0) {
                                this.display = '00:00:00';
                                this.expired = true;
                                if (this.timer) {
                                    clearInterval(this.timer);
                                    this.timer = null;
                                }
                                return;
                            }
                            const totalSeconds = Math.floor(diff / 1000);
                            const hours = Math.floor(totalSeconds / 3600);
                            const minutes = Math.floor((totalSeconds % 3600) / 60);
                            const seconds = totalSeconds % 60;
                            const pad = (n) => String(n).padStart(2, '0');
                            this.display = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
                        },
                        onVisibility() {
                            if (document.visibilityState === 'visible') {
                                this.tick();
                            }
                        },
                    };
                };
            }
        </script>
    @endif
</x-member-layout>
