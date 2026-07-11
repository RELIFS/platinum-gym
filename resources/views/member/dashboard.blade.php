@php
    $user = $portal['user'];
    $member = $portal['member'];
    $activeMembership = $portal['activeMembership'];
    $latestMembership = $portal['latestMembership'];
    $activePackageSessions = $portal['activePackageSessions'];
    $payments = $portal['payments'];
    $upcomingEnrollments = $portal['upcomingEnrollments'];
    $qrToken = $portal['qrToken'] ?? null;
    $qrTokenIsActive = (bool) ($portal['qrTokenIsActive'] ?? false);
    $qrStatusLabel = $portal['qrStatusLabel'] ?? 'Belum diterbitkan';
    $qrActiveForSession = $qrTokenIsActive && $qrStatusLabel === 'Aktif untuk sesi';
    $dashboardQrSvg = $qrTokenIsActive && $qrToken
        ? app(\App\Support\QrSvgRenderer::class)->render($qrToken->token, 160)
        : '';
    $stats = $portal['stats'];
    $activity = $portal['activity'];
    $statusLabel = match ((string) $member->status) {
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'suspended' => 'Ditangguhkan',
        default => str((string) $member->status)->headline()->toString(),
    };
    $pendingPayments = $payments->filter(fn ($payment) => in_array((string) $payment->status, ['waiting_payment', 'pending', 'unpaid', 'waiting_confirmation'], true));
    $focusItems = collect([
        [
            'label' => $activeMembership ? 'Membership aktif' : 'Aktifkan membership',
            'value' => $activeMembership?->package?->name ?? 'Belum ada paket aktif',
            'route' => 'member.membership',
            'icon' => 'card',
            'tone' => $activeMembership ? 'member-status-success' : 'member-status-warning',
        ],
        [
            'label' => $pendingPayments->isNotEmpty() ? 'Transaksi perlu dicek' : 'Transaksi aman',
            'value' => $pendingPayments->isNotEmpty() ? $pendingPayments->count().' transaksi belum selesai' : 'Tidak ada pembayaran tertunda',
            'route' => 'member.transactions',
            'icon' => 'receipt',
            'tone' => $pendingPayments->isNotEmpty() ? 'member-status-warning' : 'member-status-neutral',
        ],
        [
            'label' => $upcomingEnrollments->isNotEmpty() ? 'Booking mendatang' : 'Belum ada booking',
            'value' => $upcomingEnrollments->first()?->schedule?->gymClass?->name ?? 'Pilih kelas dari jadwal aktif',
            'route' => 'member.booking',
            'icon' => 'calendar',
            'tone' => $upcomingEnrollments->isNotEmpty() ? 'member-status-info' : 'member-status-neutral',
        ],
        [
            'label' => $qrTokenIsActive ? ($qrActiveForSession ? 'QR sesi siap' : 'QR siap dipakai') : 'QR belum aktif',
            'value' => $qrTokenIsActive ? ($qrActiveForSession ? 'Buka untuk Gunakan Sesi' : 'Buka saat check-in') : $qrStatusLabel,
            'route' => 'member.qr',
            'icon' => 'qr',
            'tone' => $qrTokenIsActive ? 'member-status-success' : 'member-status-warning',
        ],
    ]);
@endphp

<x-member-layout :portal="$portal" title="Dashboard Member">
    <section class="member-card mb-6">
        <div class="member-section-header">
            <div>
                <p class="member-eyebrow">Fokus Hari Ini</p>
                <h2 class="member-section-title">Ringkasan tindakan member</h2>
            </div>
            <a href="{{ route('member.notifications') }}" class="member-button-secondary">Cek Notifikasi</a>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($focusItems as $item)
                <a href="{{ route($item['route']) }}" class="group min-w-0 rounded-lg border border-zinc-200 bg-zinc-50/80 p-4 transition hover:border-gold-600/45 hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:border-white/10 dark:bg-white/[0.04] dark:hover:bg-white/[0.07] dark:focus-visible:ring-gold-400/40">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-gold-500/10 text-gold-text" aria-hidden="true">
                            @include('member.partials.icon', ['name' => $item['icon'], 'class' => 'h-5 w-5'])
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="member-status-pill {{ $item['tone'] }}">{{ $item['label'] }}</span>
                            <span class="mt-3 block break-words text-sm type-control leading-6 text-zinc-950 dark:text-zinc-100">{{ $item['value'] }}</span>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <section class="member-card-pass relative isolate overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/70 to-transparent" aria-hidden="true"></div>
            <div class="public-surface-grid absolute inset-0 opacity-[0.06]" aria-hidden="true"></div>
            <div class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_15rem] lg:items-start">
                <div class="min-w-0">
                    <p class="text-xs type-control uppercase tracking-[0.12em] text-zinc-700 dark:text-gold-400">Member Pass</p>
                    <h2 class="public-heading-balance mt-3 break-words text-2xl type-title leading-tight text-zinc-950 dark:text-zinc-100 sm:text-4xl">
                        Selamat datang, {{ $user->name }}
                    </h2>
                    <p class="mt-4 max-w-2xl text-sm type-compact leading-7 text-zinc-600 dark:text-zinc-300">
                        Status akun, membership, jadwal kelas, dan transaksi Anda tersusun dalam satu area member Platinum Gym Padang.
                    </p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="member-soft-panel">
                            <p class="text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Kode Member</p>
                            <p class="mt-2 break-words font-mono text-lg type-control text-zinc-950 dark:text-zinc-100">{{ $member->member_code }}</p>
                        </div>
                        <div class="member-soft-panel">
                            <p class="text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Status</p>
                            <p class="mt-2 type-control text-emerald-700 dark:text-emerald-300">{{ $statusLabel }}</p>
                        </div>
                        <div class="member-soft-panel">
                            <p class="text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Bergabung</p>
                            <p class="mt-2 type-control text-zinc-950 dark:text-zinc-100">{{ $member->joined_at?->translatedFormat('d M Y') ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-4 text-zinc-950 shadow-[0_14px_34px_rgba(24,24,27,0.06)] dark:border-white/10 dark:bg-zinc-950 dark:text-zinc-100 dark:shadow-[0_24px_70px_rgba(0,0,0,0.32)]">
                    <a href="{{ route('member.qr') }}" class="block rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:focus-visible:ring-gold-400/40" aria-label="Buka halaman QR member">
                        <div class="grid aspect-square place-items-center rounded-md border border-zinc-200 bg-zinc-50 p-4 dark:border-white/10 dark:bg-zinc-900">
                            @if ($qrTokenIsActive && filled($dashboardQrSvg))
                                <div class="h-full w-full overflow-hidden rounded-md text-zinc-950 [&_svg]:h-full [&_svg]:w-full" aria-hidden="true">
                                    {!! $dashboardQrSvg !!}
                                </div>
                            @else
                                <span class="grid h-24 w-24 place-items-center rounded-full border border-amber-500/25 bg-amber-500/10 text-amber-700 dark:text-amber-300">@include('member.partials.icon', ['name' => 'lock', 'class' => 'h-10 w-10'])</span>
                            @endif
                        </div>
                    </a>
                    <p class="mt-3 text-center text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">QR Member</p>
                    <p class="mt-1 text-center text-sm type-control {{ $qrTokenIsActive ? 'text-emerald-700' : 'text-zinc-500' }}">{{ $qrTokenIsActive ? ($qrActiveForSession ? 'QR aktif untuk sesi' : 'QR aktif') : $qrStatusLabel }}</p>
                    <a href="{{ route('member.qr') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-md border border-zinc-200 bg-zinc-100 px-3 py-2 text-xs type-control uppercase tracking-[0.11em] text-zinc-700 transition hover:border-gold-600/45 hover:text-gold-text focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:border-white/10 dark:bg-white/[0.08] dark:text-zinc-100 dark:focus-visible:ring-gold-400/40">{{ $qrTokenIsActive ? 'Buka QR' : 'Aktivasi QR' }}</a>
                </div>
            </div>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan member">
        @foreach ($stats as $stat)
            <article class="member-stat">
                <p class="text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">{{ $stat['label'] }}</p>
                <p class="mt-3 break-words text-2xl type-emphasis text-zinc-950 dark:text-zinc-100">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm type-compact leading-6 text-zinc-500 dark:text-zinc-400">{{ $stat['description'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <section class="member-card">
            <div class="member-section-header">
                <div>
                    <p class="member-eyebrow">Membership</p>
                    <h2 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Status paket Anda</h2>
                </div>
                <a href="{{ route('member.membership') }}" class="member-button-secondary">Lihat Detail</a>
            </div>

            @if ($activeMembership)
                <div class="mt-5 rounded-lg border border-emerald-500/25 bg-emerald-500/10 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm type-control text-emerald-800 dark:text-emerald-300">{{ $activeMembership->package?->name ?? $activeMembership->code }}</p>
                            <p class="mt-2 text-sm type-compact leading-6 text-zinc-600 dark:text-zinc-300">{{ $activeMembership->validityLabel() }}.</p>
                        </div>
                        <span class="member-status-pill member-status-success">Aktif</span>
                    </div>
                </div>
            @else
                <div class="member-soft-panel mt-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-gold-500/15 text-gold-text">
                            @include('member.partials.icon', ['name' => 'card', 'class' => 'h-5 w-5'])
                        </span>
                        <div class="min-w-0">
                            <h3 class="type-title text-zinc-950 dark:text-zinc-100">Belum ada membership aktif</h3>
                            <p class="mt-1 member-copy">Paket aktif akan tampil setelah transaksi tercatat dan diverifikasi oleh petugas. Katalog paket tetap bisa dilihat kapan saja.</p>
                            <a href="{{ route('member.membership') }}" class="member-button-primary mt-4">Lihat Membership</a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($activePackageSessions->isNotEmpty())
                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    @foreach ($activePackageSessions as $session)
                        <article class="member-soft-panel">
                            <p class="text-sm type-control text-zinc-950 dark:text-zinc-100">{{ $session->package?->name ?? $session->code }}</p>
                            <p class="mt-2 text-2xl type-emphasis text-zinc-950 dark:text-gold-400">{{ $session->remaining_sessions }}</p>
                            <p class="text-xs type-control text-zinc-500 dark:text-zinc-400">sesi tersisa</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="member-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="member-eyebrow">Aktivitas</p>
                    <h2 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Terbaru</h2>
                </div>
                            <span class="member-status-pill member-status-neutral">{{ $activity->count() }}</span>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($activity as $item)
                    <article class="member-list-card p-3">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-gold-500"></span>
                            <div class="min-w-0">
                                <p class="truncate text-sm type-control text-zinc-950 dark:text-zinc-100">{{ $item['title'] }}</p>
                                <p class="mt-1 text-xs type-control text-zinc-500 dark:text-zinc-400">{{ $item['description'] }}</p>
                                <p class="mt-2 text-xs type-control uppercase tracking-[0.12em] text-zinc-400">{{ $item['date']?->translatedFormat('d M Y H:i') ?? $item['date'] }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="member-soft-panel text-center">
                        @include('member.partials.icon', ['name' => 'empty', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
                        <p class="mt-3 type-control text-zinc-950 dark:text-zinc-100">Aktivitas belum tercatat</p>
                        <p class="mt-1 text-sm type-compact text-zinc-500 dark:text-zinc-400">Riwayat member akan muncul setelah transaksi, booking, atau check-in tercatat di sistem.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="member-card">
            <div class="member-section-header">
                <div>
                <p class="member-eyebrow">Jadwal Kelas</p>
                <h2 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Jadwal mendatang</h2>
                </div>
                <a href="{{ route('member.booking') }}" class="member-button-secondary">Lihat Jadwal</a>
            </div>

            <div class="mt-5 grid gap-3">
                @forelse ($upcomingEnrollments as $enrollment)
                    @php($enrollmentMeta = $enrollment->member_status_meta ?? ['label' => str((string) $enrollment->status)->headline()->toString(), 'class' => 'member-status-neutral'])
                    <article class="member-list-card">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="type-control text-zinc-950 dark:text-zinc-100">{{ $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym' }}</p>
                                <p class="mt-1 text-sm type-compact text-zinc-500 dark:text-zinc-400">
                                    {{ $enrollment->session_date?->translatedFormat('l, d M Y') }} - {{ substr((string) $enrollment->schedule?->start_time, 0, 5) }}
                                </p>
                            </div>
                            <span class="member-status-pill {{ $enrollmentMeta['class'] }}">{{ $enrollmentMeta['label'] }}</span>
                        </div>
                    </article>
                @empty
                    <div class="member-soft-panel">
                        <h3 class="type-title text-zinc-950 dark:text-zinc-100">Belum ada jadwal terdaftar</h3>
                        <p class="mt-1 member-copy">Jadwal kelas yang terdaftar untuk akun Anda akan tampil di sini. Jadwal publik tetap bisa dilihat sekarang.</p>
                        <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                            <a href="{{ route('member.booking') }}" class="member-button-primary">Buka Jadwal Kelas</a>
                            <a href="{{ route('public.classes') }}" class="member-button-secondary">Jadwal Publik</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="member-card">
            <div class="member-section-header">
                <div>
                    <p class="member-eyebrow">Transaksi</p>
                    <h2 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Pembayaran terbaru</h2>
                </div>
                <a href="{{ route('member.transactions') }}" class="member-button-secondary">Lihat Transaksi</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($payments->take(4) as $payment)
                    @php($paymentMeta = $payment->member_status_meta ?? ['label' => str((string) $payment->status)->headline()->toString(), 'class' => 'member-status-neutral'])
                    <article class="member-list-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-mono text-sm type-control text-zinc-950 dark:text-zinc-100">{{ $payment->payment_code }}</p>
                                <p class="mt-1 text-xs type-control text-zinc-500 dark:text-zinc-400">{{ $payment->created_at?->translatedFormat('d M Y H:i') }}</p>
                            </div>
                            <p class="shrink-0 text-sm type-control text-zinc-700 dark:text-gold-400">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-zinc-200 pt-3 dark:border-white/10">
                            <span class="text-xs type-control uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ str((string) $payment->method)->headline() }}</span>
                            <span class="member-status-pill {{ $paymentMeta['class'] }}">{{ $paymentMeta['label'] }}</span>
                        </div>
                    </article>
                @empty
                    <div class="member-soft-panel">
                        <h3 class="type-title text-zinc-950 dark:text-zinc-100">Belum ada transaksi</h3>
                        <p class="mt-1 member-copy">Riwayat pembayaran membership, paket sesi, atau kelas akan tampil setelah transaksi tercatat.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-member-layout>
