@php
    $trend = $trend ?? [];
    $series = collect($trend['series'] ?? []);
    $isEmpty = (bool) ($trend['isEmpty'] ?? true);
    $period = (string) ($trend['period'] ?? '14 hari terakhir');
    $chartId = 'admin-operational-trend-chart';
    $dataId = 'admin-operational-trend-data';
    $toneClasses = [
        'gold' => 'text-gold-500',
        'sky' => 'text-sky-500 dark:text-sky-400',
        'emerald' => 'text-emerald-600 dark:text-emerald-400',
    ];
    $summary = $series
        ->map(fn (array $item): string => ($item['name'] ?? '-').': '.((int) ($item['total'] ?? collect($item['values'] ?? [])->sum())))
        ->implode(', ');
@endphp

<section class="admin-card mt-6 min-w-0" aria-label="Tren aktivitas 14 hari terakhir">
    <div class="flex min-w-0 flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div class="min-w-0">
            <div class="flex min-w-0 flex-wrap items-center gap-2">
                <p class="admin-eyebrow">Aktivitas</p>
                <span class="inline-flex min-h-7 items-center rounded-full border border-gold-500/25 bg-gold-500/10 px-3 py-1 text-xs font-black text-gold-700 dark:text-gold-300">
                    {{ $period }}
                </span>
            </div>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">{{ $trend['title'] ?? 'Tren aktivitas' }}</h2>
            <p class="mt-2 admin-copy">{{ $trend['description'] ?? 'Lihat perkembangan check-in, booking, dan pembayaran yang sudah dikonfirmasi selama 14 hari terakhir.' }}</p>
        </div>

        <div class="grid min-w-0 grid-cols-1 gap-2 sm:grid-cols-3" aria-label="Ringkasan tren operasional">
            @foreach ($series as $item)
                @php
                    $tone = $item['tone'] ?? 'gold';
                    $toneClass = $toneClasses[$tone] ?? $toneClasses['gold'];
                    $total = (int) ($item['total'] ?? collect($item['values'] ?? [])->sum());
                @endphp
                <div class="min-w-0 rounded-lg border border-zinc-200 bg-white px-3 py-2 shadow-sm dark:border-white/10 dark:bg-white/[0.045]">
                    <div class="flex min-w-0 items-center gap-2">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $toneClass }}" style="background-color: currentColor"></span>
                        <span class="truncate text-xs font-black text-zinc-600 dark:text-zinc-300">{{ $item['name'] ?? '-' }}</span>
                    </div>
                    <p class="mt-1 text-lg font-black leading-tight text-zinc-950 dark:text-white">{{ $total }}</p>
                </div>
            @endforeach
        </div>
    </div>

    @if ($isEmpty)
        <div class="admin-soft-panel mt-5 flex min-h-56 items-center justify-center text-center">
            Belum ada tren operasional pada periode ini.
        </div>
    @else
        <p class="sr-only" id="{{ $chartId }}-summary">{{ $period }}. {{ $summary }}</p>
        <div class="admin-chart-shell mt-5" aria-describedby="{{ $chartId }}-summary">
            <div id="{{ $chartId }}" class="min-h-[260px] w-full min-w-0 sm:min-h-[300px] lg:min-h-[320px]" role="img" aria-label="Grafik tren operasional {{ $period }}"></div>
            <script type="application/json" id="{{ $dataId }}">@json($trend)</script>
            <noscript>
                <div class="admin-soft-panel mt-3">
                    Grafik interaktif membutuhkan JavaScript. Ringkasan periode ini: {{ $summary }}.
                </div>
            </noscript>
        </div>
    @endif
</section>
