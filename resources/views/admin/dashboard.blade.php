@php
    $admin = $portal['admin'];
    $stats = collect($portal['stats']);
    $queue = collect($portal['queue']);
    $quickLinks = collect($portal['quickLinks']);
    $moduleSummaries = collect($portal['moduleSummaries']);
    $recentMembers = collect($portal['recentMembers']);
    $recentPayments = collect($portal['recentPayments']);
    $todayBookings = collect($portal['todayBookings']);
    $todayCheckIns = collect($portal['todayCheckIns']);
@endphp

<x-admin-layout :portal="$portal" :navigation="$navigation" title="Dashboard Admin">
    <section class="admin-page-header">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 max-w-3xl">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="admin-eyebrow">Admin Area</p>
                    <span class="admin-readonly-pill">Read-only v1</span>
                </div>
                <h2 class="admin-title mt-3">Workbench operasional</h2>
                <p class="mt-3 admin-copy">Pantau member, pembayaran, booking kelas, check-in, konten website, dan katalog layanan dari data database tanpa aksi mutasi.</p>
            </div>

            <dl class="grid min-w-0 gap-3 sm:grid-cols-3 lg:w-[28rem]">
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-white/10 dark:bg-zinc-950/45">
                    <dt class="text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Admin</dt>
                    <dd class="mt-1 truncate text-sm font-black text-zinc-950 dark:text-white">{{ $admin->name }}</dd>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-white/10 dark:bg-zinc-950/45">
                    <dt class="text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Tanggal</dt>
                    <dd class="mt-1 text-sm font-black text-zinc-950 dark:text-white">{{ now()->translatedFormat('d M Y') }}</dd>
                </div>
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-white/10 dark:bg-zinc-950/45">
                    <dt class="text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Role</dt>
                    <dd class="mt-1 truncate text-sm font-black text-emerald-700 dark:text-emerald-300">{{ $admin->getRoleNames()->implode(', ') ?: 'Admin' }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-3" aria-label="Status hari ini">
            @foreach ($queue as $item)
                <a href="{{ route($item['route']) }}" class="group flex min-h-24 min-w-0 items-start justify-between gap-3 rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-gold-500/60 hover:shadow-[0_16px_42px_rgba(254,172,24,0.12)] focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-zinc-950/45">
                    <span class="min-w-0">
                        <span class="block text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ $item['label'] }}</span>
                        <span class="mt-2 block text-sm font-semibold leading-5 text-zinc-600 dark:text-zinc-400">{{ $item['description'] }}</span>
                    </span>
                    <span class="shrink-0 text-3xl font-black tabular-nums text-zinc-950 group-hover:text-gold-600 dark:text-white dark:group-hover:text-gold-400">{{ $item['value'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan admin">
        @foreach ($stats as $stat)
            <article class="admin-stat">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</p>
                <p class="mt-3 break-words text-2xl font-black tabular-nums text-zinc-950 dark:text-white">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm font-medium leading-6 text-zinc-500 dark:text-zinc-400">{{ $stat['description'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,0.74fr)_minmax(0,1.26fr)]">
        <section class="admin-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="admin-eyebrow">Akses Cepat</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Menu kerja</h2>
                </div>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach ($quickLinks as $link)
                    <a href="{{ route($link['route']) }}" class="group rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-gold-500/60 hover:shadow-[0_16px_42px_rgba(254,172,24,0.12)] focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-zinc-950/45">
                        <span class="grid h-10 w-10 place-items-center rounded-md bg-gold-500/10 text-gold-600 dark:text-gold-400">
                            @include('admin.partials.icon', ['name' => $link['icon'], 'class' => 'h-5 w-5'])
                        </span>
                        <span class="mt-3 block text-sm font-black text-zinc-950 dark:text-white">{{ $link['label'] }}</span>
                        <span class="mt-1 block text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">{{ $link['description'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="admin-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="admin-eyebrow">Modul</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Ringkasan fitur</h2>
                </div>
                <a href="{{ route('admin.reports') }}" class="admin-button-secondary">Laporan</a>
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($moduleSummaries as $module)
                    <a href="{{ route($module['route']) }}" class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 transition hover:border-gold-500/60 hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-zinc-950/45 dark:hover:bg-white/[0.05]">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">{{ $module['label'] }}</p>
                        <p class="mt-2 text-2xl font-black tabular-nums text-zinc-950 dark:text-white">{{ $module['value'] }}</p>
                        <p class="mt-1 text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">{{ $module['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="admin-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="admin-eyebrow">Anggota</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Member terbaru</h2>
                </div>
                <a href="{{ route('admin.members') }}" class="admin-button-secondary">Lihat</a>
            </div>
            <div class="mt-5 space-y-3">
                @forelse ($recentMembers->take(5) as $member)
                    <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-black text-zinc-950 dark:text-white">{{ $member->user?->name ?? '-' }}</p>
                                <p class="mt-1 font-mono text-xs font-bold text-zinc-500 dark:text-zinc-400">{{ $member->member_code }}</p>
                            </div>
                            <span class="admin-status-pill bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">{{ str((string) $member->status)->headline() }}</span>
                        </div>
                    </article>
                @empty
                    <div class="admin-soft-panel">Belum ada member.</div>
                @endforelse
            </div>
        </section>

        <section class="admin-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="admin-eyebrow">Keuangan</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Pembayaran terbaru</h2>
                </div>
                <a href="{{ route('admin.payments') }}" class="admin-button-secondary">Lihat</a>
            </div>
            <div class="mt-5 space-y-3">
                @forelse ($recentPayments->take(5) as $payment)
                    <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-mono text-sm font-black text-zinc-950 dark:text-white">{{ $payment->payment_code }}</p>
                                <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $payment->member?->user?->name ?? $payment->member?->member_code ?? '-' }}</p>
                            </div>
                            <p class="shrink-0 text-sm font-black tabular-nums text-gold-600 dark:text-gold-400">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-zinc-200 pt-3 dark:border-white/10">
                            <span class="text-xs font-bold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ str((string) $payment->method)->headline() }}</span>
                            <span class="admin-status-pill bg-zinc-100 text-zinc-700 dark:bg-white/[0.07] dark:text-zinc-300">{{ str((string) $payment->status)->headline() }}</span>
                        </div>
                    </article>
                @empty
                    <div class="admin-soft-panel">Belum ada pembayaran.</div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        @include('admin.partials.data-table', ['module' => ['title' => 'Booking Hari Ini', 'description' => 'Peserta kelas yang tercatat untuk tanggal ini.', 'empty' => 'Belum ada booking hari ini.', 'columns' => ['Member', 'Kelas', 'Jam', 'Status'], 'rows' => $todayBookings->map(fn ($enrollment) => [$enrollment->member?->user?->name ?? $enrollment->member?->member_code ?? '-', $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym', substr((string) $enrollment->schedule?->start_time, 0, 5), str((string) $enrollment->status)->headline()->toString()])]])
        @include('admin.partials.data-table', ['module' => ['title' => 'Check-in Hari Ini', 'description' => 'Aktivitas masuk gym yang tercatat hari ini.', 'empty' => 'Belum ada check-in hari ini.', 'columns' => ['Member', 'Tanggal', 'Jam', 'Metode'], 'rows' => $todayCheckIns->map(fn ($checkIn) => [$checkIn->member?->user?->name ?? $checkIn->member?->member_code ?? '-', $checkIn->check_in_date?->translatedFormat('d M Y') ?? '-', $checkIn->check_in_at?->format('H:i') ?? '-', str((string) $checkIn->method)->headline()->toString()])]])
    </div>
</x-admin-layout>