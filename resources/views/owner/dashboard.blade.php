@php
    $owner = $portal['owner'];
    $kpis = collect($portal['kpis']);
    $trend = $portal['businessTrend'];
    $methodBreakdown = collect($portal['revenueByMethod']);
    $serviceBreakdown = collect($portal['revenueByService']);
    $recentPayments = collect($portal['recentPayments']);
    $expiringMemberships = collect($portal['expiringMemberships']);
    $trendSummary = collect($trend['series'] ?? [])
        ->map(function (array $item): string {
            $value = ($item['format'] ?? '') === 'money'
                ? 'Rp '.number_format((float) ($item['total'] ?? 0), 0, ',', '.')
                : (string) ($item['total'] ?? 0);

            return ($item['name'] ?? '-').': '.$value;
        })
        ->implode(', ');
@endphp

<x-owner-layout :portal="$portal" :navigation="$navigation" title="Dashboard Owner">
    <section class="owner-page-header">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 max-w-3xl">
                <p class="owner-eyebrow">Owner</p>
                <h2 class="owner-title mt-3">Ringkasan bisnis</h2>
                <p class="mt-3 owner-copy">Pantau pendapatan, transaksi, member, membership, dan booking kelas dari satu halaman yang ringkas.</p>
            </div>

            <dl class="grid min-w-0 gap-3 sm:grid-cols-3 lg:w-[28rem]">
                <div class="owner-panel p-3">
                    <dt class="text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Owner</dt>
                    <dd class="mt-1 truncate text-sm type-control text-zinc-950 dark:text-zinc-100">{{ $owner->name }}</dd>
                </div>
                <div class="owner-panel p-3">
                    <dt class="text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Periode</dt>
                    <dd class="mt-1 text-sm type-control text-zinc-950 dark:text-zinc-100">{{ $portal['filters']->periodLabel() }}</dd>
                </div>
                <div class="owner-panel p-3">
                    <dt class="text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Akses</dt>
                    <dd class="mt-1 truncate text-sm type-control text-emerald-700 dark:text-emerald-300">Read-only</dd>
                </div>
            </dl>
        </div>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5" aria-label="Ringkasan bisnis owner">
        @foreach ($kpis as $kpi)
            <article class="owner-card p-4 sm:p-5">
                <p class="text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">{{ $kpi['label'] }}</p>
                <p class="mt-3 break-words text-2xl type-emphasis tabular-nums text-zinc-950 dark:text-zinc-100">{{ $kpi['value'] }}</p>
                <p class="mt-2 text-sm type-compact leading-6 text-zinc-500 dark:text-zinc-400">{{ $kpi['description'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="owner-card mt-6" aria-label="Tren pendapatan owner">
        <div class="flex min-w-0 flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
                <div class="flex min-w-0 flex-wrap items-center gap-2">
                    <p class="owner-eyebrow">Insight</p>
                    <span class="inline-flex min-h-7 items-center rounded-full border border-gold-500/25 bg-gold-500/10 px-3 py-1 text-xs type-control text-gold-text">{{ $trend['period'] }}</span>
                </div>
                <h2 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">{{ $trend['title'] }}</h2>
                <p class="mt-2 owner-copy">{{ $trend['description'] }}</p>
            </div>

            <a href="{{ route('owner.reports.finance') }}" class="owner-button-secondary">Lihat laporan</a>
        </div>

        @if ($trend['isEmpty'])
            <div class="owner-panel mt-5 flex min-h-56 items-center justify-center text-center">
                Belum ada pendapatan terkonfirmasi pada periode ini.
            </div>
        @else
            <p class="sr-only" id="owner-business-trend-summary">{{ $trend['period'] }}. {{ $trendSummary }}</p>
            <div class="owner-chart-shell mt-5" aria-describedby="owner-business-trend-summary">
                <div id="owner-business-trend-chart" class="min-h-[260px] w-full min-w-0 sm:min-h-[300px] lg:min-h-[320px]" role="img" aria-label="Grafik tren pendapatan {{ $trend['period'] }}"></div>
                <script type="application/json" id="owner-business-trend-data">@json($trend)</script>
            </div>
        @endif
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="owner-card">
            <p class="owner-eyebrow">Pendapatan</p>
            <h2 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Sumber pendapatan</h2>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="owner-panel">
                    <h3 class="text-sm type-title text-zinc-950 dark:text-zinc-100">Metode pembayaran</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($methodBreakdown as $item)
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm type-control text-zinc-950 dark:text-zinc-100" title="{{ $item['label'] }}">{{ $item['label'] }}</p>
                                    <p class="text-xs type-control text-zinc-500 dark:text-zinc-400">{{ $item['count'] }} transaksi</p>
                                </div>
                                <p class="shrink-0 text-sm type-control text-zinc-700 dark:text-gold-400">{{ $item['total'] }}</p>
                            </div>
                        @empty
                            <p class="owner-copy">Belum ada transaksi lunas.</p>
                        @endforelse
                    </div>
                </div>

                <div class="owner-panel">
                    <h3 class="text-sm type-title text-zinc-950 dark:text-zinc-100">Jenis layanan</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($serviceBreakdown as $item)
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm type-control text-zinc-950 dark:text-zinc-100" title="{{ $item['label'] }}">{{ $item['label'] }}</p>
                                    <p class="text-xs type-control text-zinc-500 dark:text-zinc-400">{{ $item['count'] }} transaksi</p>
                                </div>
                                <p class="shrink-0 text-sm type-control text-zinc-700 dark:text-gold-400">{{ $item['total'] }}</p>
                            </div>
                        @empty
                            <p class="owner-copy">Belum ada layanan berbayar.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="owner-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="owner-eyebrow">Terbaru</p>
                    <h2 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Transaksi terkonfirmasi</h2>
                </div>
                <a href="{{ route('owner.reports.finance') }}" class="owner-button-secondary">Buka keuangan</a>
            </div>
            <div class="mt-5 space-y-3">
                @forelse ($recentPayments as $payment)
                    <article class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-mono text-sm type-control text-zinc-950 dark:text-zinc-100" title="{{ $payment->payment_code }}">{{ $payment->payment_code }}</p>
                                <p class="mt-1 text-xs type-control text-zinc-500 dark:text-zinc-400">{{ $payment->member?->user?->name ?? $payment->member?->member_code ?? '-' }}</p>
                            </div>
                            <p class="shrink-0 text-sm type-control tabular-nums text-gold-text">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                        </div>
                    </article>
                @empty
                    <div class="owner-panel">Belum ada transaksi terkonfirmasi.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="owner-card mt-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="owner-eyebrow">Membership</p>
                <h2 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Membership akan berakhir</h2>
                <p class="mt-2 owner-copy">Daftar member aktif yang masa membership-nya berakhir dalam 14 hari.</p>
            </div>
            <a href="{{ route('owner.reports.members') }}" class="owner-button-secondary">Lihat member</a>
        </div>
        <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($expiringMemberships as $membership)
                <article class="owner-panel">
                    <p class="type-control text-zinc-950 dark:text-zinc-100">{{ $membership->member?->user?->name ?? $membership->member?->member_code ?? '-' }}</p>
                    <p class="mt-1 text-xs type-control text-zinc-500 dark:text-zinc-400">{{ $membership->package?->name ?? 'Membership' }}</p>
                    <p class="mt-3 text-sm type-control text-amber-700 dark:text-amber-300">Berakhir {{ $membership->end_date?->translatedFormat('d M Y') }}</p>
                </article>
            @empty
                <div class="owner-panel md:col-span-2 xl:col-span-3">Tidak ada membership aktif yang berakhir dalam 14 hari.</div>
            @endforelse
        </div>
    </section>
</x-owner-layout>
