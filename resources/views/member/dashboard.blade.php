@php
    $user = $portal['user'];
    $member = $portal['member'];
    $activeMembership = $portal['activeMembership'];
    $latestMembership = $portal['latestMembership'];
    $activePackageSessions = $portal['activePackageSessions'];
    $payments = $portal['payments'];
    $upcomingEnrollments = $portal['upcomingEnrollments'];
    $qrToken = $portal['qrToken'];
    $stats = $portal['stats'];
    $activity = $portal['activity'];
    $statusLabel = match ((string) $member->status) {
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'suspended' => 'Ditangguhkan',
        default => str((string) $member->status)->headline()->toString(),
    };
    $quickActions = [
        ['label' => 'Profil', 'description' => 'Data akun dan identitas', 'route' => 'member.profile', 'icon' => 'user'],
        ['label' => 'Membership', 'description' => 'Status paket member', 'route' => 'member.membership', 'icon' => 'card'],
        ['label' => 'Booking Kelas', 'description' => 'Jadwal kelas aktif', 'route' => 'member.booking', 'icon' => 'calendar'],
        ['label' => 'Transaksi', 'description' => 'Pembayaran dan invoice', 'route' => 'member.transactions', 'icon' => 'receipt'],
        ['label' => 'QR Member', 'description' => 'Kartu check-in digital', 'route' => 'member.qr', 'icon' => 'qr'],
        ['label' => 'AI Assistant', 'description' => 'Bantuan layanan member', 'route' => 'member.ai-assistant', 'icon' => 'spark'],
    ];
@endphp

<x-member-layout :portal="$portal" title="Dashboard Member">
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,0.65fr)]">
        <section class="member-card-strong relative isolate overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/70 to-transparent" aria-hidden="true"></div>
            <div class="public-surface-grid absolute inset-0 opacity-10" aria-hidden="true"></div>
            <div class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_15rem] lg:items-start">
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-gold-400">Member Pass</p>
                    <h2 class="mt-3 break-words text-3xl font-black leading-tight tracking-tight text-white sm:text-4xl">
                        Selamat datang, {{ $user->name }}
                    </h2>
                    <p class="mt-4 max-w-2xl text-sm font-medium leading-7 text-zinc-300">
                        Status, membership, booking, dan transaksi Anda dalam satu area member Platinum Gym Padang.
                    </p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg border border-white/10 bg-white/[0.06] p-4">
                            <p class="text-[0.68rem] font-black uppercase tracking-[0.16em] text-zinc-400">Kode Member</p>
                            <p class="mt-2 break-words font-mono text-lg font-black text-white">{{ $member->member_code }}</p>
                        </div>
                        <div class="rounded-lg border border-white/10 bg-white/[0.06] p-4">
                            <p class="text-[0.68rem] font-black uppercase tracking-[0.16em] text-zinc-400">Status</p>
                            <p class="mt-2 font-black text-emerald-300">{{ $statusLabel }}</p>
                        </div>
                        <div class="rounded-lg border border-white/10 bg-white/[0.06] p-4">
                            <p class="text-[0.68rem] font-black uppercase tracking-[0.16em] text-zinc-400">Bergabung</p>
                            <p class="mt-2 font-black text-white">{{ $member->joined_at?->translatedFormat('d M Y') ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-white/10 bg-white p-4 text-zinc-950 shadow-[0_24px_70px_rgba(0,0,0,0.32)]">
                    <div class="grid aspect-square place-items-center rounded-md border border-zinc-200 bg-[linear-gradient(135deg,#fff,#f4f4f5)] p-5">
                        @include('member.partials.icon', ['name' => 'qr', 'class' => 'h-28 w-28 text-zinc-950'])
                    </div>
                    <p class="mt-3 text-center text-xs font-black uppercase tracking-[0.18em] text-zinc-500">QR Member</p>
                    <p class="mt-1 text-center text-sm font-black text-zinc-950">{{ $qrToken && ! $qrToken->is_revoked ? 'Token aktif' : 'Siap diterbitkan' }}</p>
                </div>
            </div>
        </section>

        <section class="member-card">
            <p class="member-eyebrow">Aksi Cepat</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Mulai dari sini</h2>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                @foreach ($quickActions as $action)
                    <a href="{{ route($action['route']) }}" class="group flex min-h-16 items-center gap-3 rounded-lg border border-zinc-200 bg-white p-3 transition hover:border-gold-500/60 hover:shadow-[0_16px_42px_rgba(254,172,24,0.12)] focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-zinc-950/45 dark:hover:text-gold-400">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-gold-500/10 text-gold-700 dark:text-gold-400">
                            @include('member.partials.icon', ['name' => $action['icon'], 'class' => 'h-5 w-5'])
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-black text-zinc-950 dark:text-white">{{ $action['label'] }}</span>
                            <span class="mt-0.5 block truncate text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $action['description'] }}</span>
                        </span>
                        @include('member.partials.icon', ['name' => 'arrow', 'class' => 'h-4 w-4 shrink-0 text-zinc-400 group-hover:text-gold-500'])
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan member">
        @foreach ($stats as $stat)
            <article class="member-stat">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</p>
                <p class="mt-3 break-words text-2xl font-black text-zinc-950 dark:text-white">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm font-medium leading-6 text-zinc-500 dark:text-zinc-400">{{ $stat['description'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <section class="member-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="member-eyebrow">Membership</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Status paket Anda</h2>
                </div>
                <a href="{{ route('member.membership') }}" class="member-button-secondary">Lihat Detail</a>
            </div>

            @if ($activeMembership)
                <div class="mt-5 rounded-lg border border-emerald-500/25 bg-emerald-500/10 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-black text-emerald-800 dark:text-emerald-300">{{ $activeMembership->package?->name ?? $activeMembership->code }}</p>
                            <p class="mt-2 text-sm font-medium leading-6 text-zinc-600 dark:text-zinc-300">
                                Berlaku {{ $activeMembership->start_date?->translatedFormat('d M Y') }} sampai {{ $activeMembership->end_date?->translatedFormat('d M Y') }}.
                            </p>
                        </div>
                        <span class="member-status-pill bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">Aktif</span>
                    </div>
                </div>
            @else
                <div class="member-soft-panel mt-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-gold-500/15 text-gold-600 dark:text-gold-400">
                            @include('member.partials.icon', ['name' => 'card', 'class' => 'h-5 w-5'])
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-black text-zinc-950 dark:text-white">Belum ada membership aktif</h3>
                            <p class="mt-1 member-copy">Paket pembelian digital akan aktif saat modul transaksi siap. Anda tetap bisa melihat katalog paket.</p>
                            <a href="{{ route('member.membership') }}" class="member-button-primary mt-4">Lihat Membership</a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($activePackageSessions->isNotEmpty())
                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    @foreach ($activePackageSessions as $session)
                        <article class="rounded-lg border border-zinc-200 p-4 dark:border-white/10">
                            <p class="text-sm font-black text-zinc-950 dark:text-white">{{ $session->package?->name ?? $session->code }}</p>
                            <p class="mt-2 text-2xl font-black text-gold-600 dark:text-gold-400">{{ $session->remaining_sessions }}</p>
                            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">sesi tersisa</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="member-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="member-eyebrow">Aktivitas</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Terbaru</h2>
                </div>
                <span class="member-status-pill bg-zinc-100 text-zinc-600 dark:bg-white/[0.07] dark:text-zinc-300">{{ $activity->count() }}</span>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($activity as $item)
                    <article class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-white/10 dark:bg-zinc-950/45">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-gold-500"></span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-zinc-950 dark:text-white">{{ $item['title'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $item['description'] }}</p>
                                <p class="mt-2 text-[0.68rem] font-bold uppercase tracking-[0.14em] text-zinc-400">{{ $item['date']?->translatedFormat('d M Y H:i') ?? $item['date'] }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="member-soft-panel text-center">
                        @include('member.partials.icon', ['name' => 'empty', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
                        <p class="mt-3 font-black text-zinc-950 dark:text-white">Belum ada aktivitas</p>
                        <p class="mt-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">Riwayat akan muncul setelah transaksi, booking, atau check-in tercatat.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="member-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="member-eyebrow">Booking Kelas</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Jadwal mendatang</h2>
                </div>
                <a href="{{ route('member.booking') }}" class="member-button-secondary">Lihat Jadwal</a>
            </div>

            <div class="mt-5 grid gap-3">
                @forelse ($upcomingEnrollments as $enrollment)
                    <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-black text-zinc-950 dark:text-white">{{ $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym' }}</p>
                                <p class="mt-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                    {{ $enrollment->session_date?->translatedFormat('l, d M Y') }} - {{ substr((string) $enrollment->schedule?->start_time, 0, 5) }}
                                </p>
                            </div>
                            <span class="member-status-pill bg-gold-500/15 text-gold-700 dark:text-gold-300">{{ str((string) $enrollment->status)->headline() }}</span>
                        </div>
                    </article>
                @empty
                    <div class="member-soft-panel">
                        <h3 class="font-black text-zinc-950 dark:text-white">Belum ada booking mendatang</h3>
                        <p class="mt-1 member-copy">Booking digital akan tersedia saat modul kelas aktif. Jadwal publik tetap bisa dilihat sekarang.</p>
                        <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                            <a href="{{ route('member.booking') }}" class="member-button-primary">Buka Booking Kelas</a>
                            <a href="{{ route('public.classes') }}" class="member-button-secondary">Jadwal Publik</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="member-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="member-eyebrow">Transaksi</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Pembayaran terbaru</h2>
                </div>
                <a href="{{ route('member.transactions') }}" class="member-button-secondary">Lihat Transaksi</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($payments->take(4) as $payment)
                    <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-mono text-sm font-black text-zinc-950 dark:text-white">{{ $payment->payment_code }}</p>
                                <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $payment->created_at?->translatedFormat('d M Y H:i') }}</p>
                            </div>
                            <p class="shrink-0 text-sm font-black text-gold-600 dark:text-gold-400">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-zinc-200 pt-3 dark:border-white/10">
                            <span class="text-xs font-bold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ str((string) $payment->method)->headline() }}</span>
                            <span class="member-status-pill bg-zinc-100 text-zinc-700 dark:bg-white/[0.07] dark:text-zinc-300">{{ str((string) $payment->status)->headline() }}</span>
                        </div>
                    </article>
                @empty
                    <div class="member-soft-panel">
                        <h3 class="font-black text-zinc-950 dark:text-white">Belum ada transaksi</h3>
                        <p class="mt-1 member-copy">Invoice dan pembayaran akan tampil setelah modul payment aktif.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-member-layout>
