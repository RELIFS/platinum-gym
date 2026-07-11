@php
    $admin = $portal['admin'];
    $stats = collect($portal['stats']);
    $queue = collect($portal['queue']);
    $operationalTrend = $portal['operationalTrend'] ?? [];
    $moduleSummaries = collect($portal['moduleSummaries']);
    $recentMembers = collect($portal['recentMembers']);
    $recentPayments = collect($portal['recentPayments']);
@endphp

<x-admin-layout :portal="$portal" :navigation="$navigation" title="Dashboard Admin">
    <section class="admin-page-header">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 max-w-3xl">
                <p class="admin-eyebrow">Dashboard</p>
                <h2 class="admin-title mt-3">Ringkasan admin</h2>
                <p class="mt-3 admin-copy">Pantau aktivitas gym, pembayaran, booking, check-in, dan data penting lainnya dari satu halaman.</p>
            </div>

            <dl class="grid min-w-0 gap-3 sm:grid-cols-3 lg:w-[28rem]">
                <div class="admin-panel p-3">
                    <dt class="text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Admin</dt>
                    <dd class="mt-1 truncate text-sm type-control text-zinc-950 dark:text-zinc-100">{{ $admin->name }}</dd>
                </div>
                <div class="admin-panel p-3">
                    <dt class="text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Tanggal</dt>
                    <dd class="mt-1 text-sm type-control text-zinc-950 dark:text-zinc-100">{{ now()->translatedFormat('d M Y') }}</dd>
                </div>
                <div class="admin-panel p-3">
                    <dt class="text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Peran</dt>
                    <dd class="mt-1 truncate text-sm type-control text-emerald-700 dark:text-emerald-300">{{ $admin->getRoleNames()->implode(', ') ?: 'Admin' }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-3" aria-label="Perlu dicek hari ini">
            @foreach ($queue as $item)
                <a href="{{ route($item['route']) }}" class="group flex min-h-24 min-w-0 items-start justify-between gap-3 rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-gold-600/55 hover:shadow-[0_10px_28px_rgba(254,172,24,0.08)] focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:border-white/10 dark:bg-zinc-950/45 dark:focus-visible:ring-gold-400/40">
                    <span class="min-w-0">
                        <span class="block text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">{{ $item['label'] }}</span>
                        <span class="mt-2 block text-sm type-control leading-5 text-zinc-600 dark:text-zinc-400">{{ $item['description'] }}</span>
                    </span>
                    <span class="shrink-0 text-3xl type-emphasis tabular-nums text-zinc-950 group-hover:text-gold-display dark:text-zinc-100">{{ $item['value'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan hari ini">
        @foreach ($stats as $stat)
            <article class="admin-metric-card">
                <p class="text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">{{ $stat['label'] }}</p>
                <p class="mt-3 break-words text-2xl type-emphasis tabular-nums text-zinc-950 dark:text-zinc-100">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm type-compact leading-6 text-zinc-500 dark:text-zinc-400">{{ $stat['description'] }}</p>
            </article>
        @endforeach
    </section>

    @include('admin.partials.operational-trend-chart', ['trend' => $operationalTrend])

    <div class="mt-6 grid gap-6">
        <section class="admin-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="admin-eyebrow">Menu</p>
                    <h2 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Akses cepat</h2>
                </div>
                <a href="{{ route('admin.reports') }}" class="admin-button-secondary">Buka laporan</a>
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($moduleSummaries as $module)
                    <a href="{{ route($module['route']) }}" class="admin-action-card">
                        <p class="text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">{{ $module['label'] }}</p>
                        <p class="mt-2 text-2xl type-emphasis tabular-nums text-zinc-950 dark:text-zinc-100">{{ $module['value'] }}</p>
                        <p class="mt-1 text-xs type-control leading-5 text-zinc-500 dark:text-zinc-400">{{ $module['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <section class="admin-card mt-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="admin-eyebrow">Terbaru</p>
                <h2 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Aktivitas terbaru</h2>
                <p class="mt-2 admin-copy">Lihat data terbaru tanpa perlu membuka setiap halaman.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-4 xl:grid-cols-2">
            <div class="admin-panel">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-base type-title text-zinc-950 dark:text-zinc-100">Member terbaru</h3>
                    <a href="{{ route('admin.members') }}" class="text-sm type-control text-zinc-700 dark:text-gold-400 underline-offset-4 hover:underline">Lihat member</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($recentMembers->take(3) as $member)
                        <article class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.04]">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate type-control text-zinc-950 dark:text-zinc-100">{{ $member->user?->name ?? '-' }}</p>
                                    <p class="mt-1 font-mono text-xs type-control text-zinc-500 dark:text-zinc-400">{{ $member->member_code }}</p>
                                </div>
                                @php
                                    $memberBadge = \App\Features\Admin\ViewModels\AdminStatusViewModel::member($member->status);
                                @endphp
                                <span class="admin-status-pill {{ $memberBadge['class'] }}">{{ $memberBadge['label'] }}</span>
                            </div>
                        </article>
                    @empty
                        <div class="admin-soft-panel">Belum ada member terbaru.</div>
                    @endforelse
                </div>
            </div>

            <div class="admin-panel">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-base type-title text-zinc-950 dark:text-zinc-100">Pembayaran terbaru</h3>
                    <a href="{{ route('admin.payments') }}" class="text-sm type-control text-zinc-700 dark:text-gold-400 underline-offset-4 hover:underline">Lihat pembayaran</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($recentPayments->take(3) as $payment)
                        <article class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.04]">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-mono text-sm type-control text-zinc-950 dark:text-zinc-100">{{ $payment->payment_code }}</p>
                                    <p class="mt-1 text-xs type-control text-zinc-500 dark:text-zinc-400">{{ $payment->member?->user?->name ?? $payment->member?->member_code ?? '-' }}</p>
                                </div>
                                <p class="shrink-0 text-sm type-control tabular-nums text-gold-text">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-zinc-200 pt-3 dark:border-white/10">
                                <span class="text-xs type-control uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ str((string) $payment->method)->headline() }}</span>
                                @php
                                    $paymentBadge = \App\Features\Admin\ViewModels\AdminStatusViewModel::payment($payment->status);
                                @endphp
                                <span class="admin-status-pill {{ $paymentBadge['class'] }}">{{ $paymentBadge['label'] }}</span>
                            </div>
                        </article>
                    @empty
                        <div class="admin-soft-panel">Belum ada pembayaran terbaru.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</x-admin-layout>
