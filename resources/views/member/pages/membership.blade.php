@php
    $trainerOptions = $portal['trainerOptions'] ?? [];
    $packageGroups = collect($portal['packageGroups'] ?? []);
    $activeMemberships = collect($portal['activeMemberships'] ?? ($activeMembership ? [$activeMembership] : []));
@endphp

<section class="member-card mt-6 min-w-0">
    <div class="member-section-header">
        <div class="min-w-0">
            <p class="member-eyebrow">Paket Aktif</p>
            <h3 class="member-section-title">Status membership</h3>
        </div>
        @unless ($activeMembership)
            <a href="{{ route('member.membership', ['kind' => 'membership']) }}" class="member-button-secondary">Lihat Paket Membership</a>
        @endunless
    </div>

    <div class="mt-5 grid gap-4 xl:grid-cols-[minmax(0,1.18fr)_minmax(0,0.82fr)]">
        @if ($activeMemberships->isNotEmpty())
            <div class="min-w-0 overflow-hidden rounded-lg border border-emerald-500/25 bg-emerald-500/10 p-4 sm:p-5">
                <div class="flex min-w-0 flex-wrap items-center justify-between gap-3">
                    <span class="member-status-pill member-status-success">Aktif</span>
                    @if ($activeMemberships->count() > 1)
                        <span class="member-status-pill member-status-neutral">{{ $activeMemberships->count() }} paket aktif</span>
                    @endif
                </div>

                <div class="member-horizontal-rail -mx-1 mt-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-1 pb-3" aria-label="Daftar membership aktif" tabindex="0">
                    @foreach ($activeMemberships as $membership)
                        <article class="min-w-0 shrink-0 basis-[min(100%,20rem)] snap-start rounded-lg border border-emerald-500/20 bg-white/75 p-4 dark:border-emerald-400/15 dark:bg-zinc-950/45 sm:basis-80 xl:basis-[21rem]">
                            <h4 class="break-words text-xl font-black text-zinc-950 dark:text-white">{{ $membership->package?->name ?? $membership->code }}</h4>
                            <p class="mt-2 member-copy">{{ $membership->validityLabel() }}.</p>
                            <p class="mt-4 text-2xl font-black text-gold-600 dark:text-gold-400">Rp {{ number_format((float) $membership->price, 0, ',', '.') }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        @else
            <div class="member-soft-panel min-w-0">
                <h4 class="font-black text-zinc-950 dark:text-white">Belum ada membership aktif</h4>
                <p class="mt-2 member-copy">Pilih paket membership di katalog, checkout, lalu selesaikan pembayaran Midtrans untuk mengaktifkan layanan.</p>
                <a href="{{ route('member.membership', ['kind' => 'membership']) }}" class="member-button-primary mt-4 w-full sm:w-auto">Lihat Paket Membership</a>
            </div>
        @endif

        <div class="grid min-w-0 content-start gap-3">
            @forelse ($activePackageSessions as $session)
                <article class="member-soft-panel min-w-0">
                    <div class="flex min-w-0 flex-wrap items-start justify-between gap-2">
                        <p class="min-w-0 break-words font-black leading-snug text-zinc-950 dark:text-white">{{ $session->package?->name ?? $session->code }}</p>
                        <span class="member-status-pill member-status-info shrink-0">Sesi aktif</span>
                    </div>
                    <p class="mt-3 inline-flex min-h-8 items-center rounded-full bg-emerald-500/10 px-3 text-sm font-black text-emerald-700 dark:text-emerald-300">{{ $session->remaining_sessions }} dari {{ $session->total_sessions }} sesi tersisa</p>
                    @if ($session->trainer)
                        <div class="mt-3 min-w-0 rounded-md border border-zinc-200 bg-white/70 px-3 py-2 dark:border-white/10 dark:bg-white/[0.03]">
                            <p class="text-xs font-black text-zinc-500 dark:text-zinc-400">Trainer</p>
                            <p class="mt-1 break-words text-sm font-bold text-zinc-700 dark:text-zinc-200">{{ $session->trainer->name }}</p>
                        </div>
                    @endif
                </article>
            @empty
                <article class="member-soft-panel min-w-0">
                    <p class="font-black text-zinc-950 dark:text-white">Belum ada paket sesi aktif</p>
                    <p class="mt-2 member-copy">Paket sesi aktif akan tampil di sini setelah pembayaran berhasil.</p>
                </article>
            @endforelse
        </div>
    </div>
</section>

<section class="member-card mt-6 min-w-0 pb-20 sm:pb-24 lg:pb-6">
    <div class="member-section-header">
        <div class="min-w-0">
            <p class="member-eyebrow">Katalog Paket</p>
            <h3 class="member-section-title">Pilih layanan</h3>
        </div>
        <a href="{{ route('member.transactions') }}" class="member-button-secondary">Lihat Transaksi</a>
    </div>

    @include('member.partials.filter-toolbar', [
        'filters' => $portal['pageFilters'] ?? [],
        'searchLabel' => 'Cari paket',
        'searchPlaceholder' => 'Cari nama paket, tipe, kategori...',
        'selects' => [
            [
                'name' => 'kind',
                'label' => 'Filter jenis paket',
                'placeholder' => 'Semua paket',
                'options' => $portal['filterOptions']['packageKinds'] ?? [],
            ],
        ],
    ])

    @if ($packageGroups->isNotEmpty())
        <div class="mt-6 space-y-7">
            @foreach ($packageGroups as $group)
                <section class="min-w-0" aria-labelledby="package-group-{{ $group['key'] }}">
                    <div class="mb-4 flex min-w-0 flex-wrap items-center justify-between gap-3 border-b border-zinc-200 pb-3 dark:border-white/10">
                        <h4 id="package-group-{{ $group['key'] }}" class="break-words text-lg font-black text-zinc-950 dark:text-white">{{ $group['title'] }}</h4>
                        <span class="member-status-pill member-status-neutral">{{ $group['packages']->count() }} paket</span>
                    </div>
                    <div class="grid min-w-0 gap-4 md:grid-cols-2 2xl:grid-cols-3">
                        @foreach ($group['packages'] as $package)
                            @include('member.partials.package-card', [
                                'package' => $package,
                                'trainerOptions' => $trainerOptions,
                            ])
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        @include('member.partials.empty-state', [
            'icon' => 'card',
            'title' => 'Paket tidak ditemukan',
            'body' => 'Ubah kata kunci atau filter jenis paket untuk melihat layanan yang tersedia.',
            'class' => 'mt-5',
        ])
    @endif

    @include('member.partials.pagination', ['paginator' => $packages, 'label' => 'paket membership'])
</section>
