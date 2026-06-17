@php
    $trainerOptions = $portal['trainerOptions'] ?? [];
@endphp

<div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)]">
    <section class="member-card">
        <p class="member-eyebrow">Paket Aktif</p>
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Status membership</h3>
        @if ($activeMembership)
            <div class="mt-5 rounded-lg border border-emerald-500/25 bg-emerald-500/10 p-5">
                <span class="member-status-pill member-status-success">Aktif</span>
                <h4 class="mt-4 text-2xl font-black text-zinc-950 dark:text-white">{{ $activeMembership->package?->name ?? $activeMembership->code }}</h4>
                <p class="mt-2 member-copy">{{ $activeMembership->start_date?->translatedFormat('d M Y') }} sampai {{ $activeMembership->end_date?->translatedFormat('d M Y') }}.</p>
                <p class="mt-4 text-2xl font-black text-gold-600 dark:text-gold-400">Rp {{ number_format((float) $activeMembership->price, 0, ',', '.') }}</p>
            </div>
        @else
            <div class="member-soft-panel mt-5">
                <h4 class="font-black text-zinc-950 dark:text-white">Belum ada membership aktif</h4>
                <p class="mt-2 member-copy">Pilih paket membership di katalog, checkout, lalu selesaikan pembayaran Midtrans untuk mengaktifkan layanan.</p>
                <a href="{{ route('member.membership', ['kind' => 'membership']) }}" class="member-button-secondary mt-4 inline-flex">Lihat Paket Membership</a>
            </div>
        @endif

        @if ($activePackageSessions->isNotEmpty())
            <div class="mt-5 grid gap-3">
                @foreach ($activePackageSessions as $session)
                    <article class="member-soft-panel">
                        <p class="font-black text-zinc-950 dark:text-white">{{ $session->package?->name ?? $session->code }}</p>
                        <p class="mt-2 text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $session->remaining_sessions }} dari {{ $session->total_sessions }} sesi tersisa</p>
                        @if ($session->trainer)
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">Coach {{ $session->trainer->name }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="member-card">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="member-eyebrow">Katalog Paket</p>
                <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Pilih layanan</h3>
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

        @if ($packages->count() > 0)
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ($packages as $package)
                    @php
                        $packageMeta = $package->member_status_meta ?? [
                            'kind_label' => str((string) $package->package_kind)->replace('_', ' ')->headline()->toString(),
                            'is_membership' => false,
                            'type_label' => 'Sesi',
                            'requires_trainer' => false,
                            'price' => (float) ($package->price ?? 0),
                            'promo_price' => filled($package->promo_price) ? (float) $package->promo_price : null,
                            'has_promo' => false,
                            'display_price' => (float) ($package->promo_price ?? $package->price ?? 0),
                        ];
                        $isMembership = (bool) $packageMeta['is_membership'];
                        $needsMembership = (bool) $package->requires_active_membership;
                        $canCheckout = $isMembership || ! $needsMembership || (bool) $activeMembership;
                        $checkoutRoute = $isMembership ? route('member.membership.checkout', $package) : route('member.package-sessions.checkout', $package);
                        $requiresTrainer = (bool) ($packageMeta['requires_trainer'] ?? false);
                        $packageTrainerList = $trainerOptions[$package->id] ?? [];
                        $trainerSelectId = 'trainer-select-'.$package->id;
                        $confirmMessage = sprintf(
                            'Lanjut checkout %s seharga Rp %s? Anda akan diarahkan ke pembayaran Midtrans.',
                            addslashes((string) $package->name),
                            number_format((float) $packageMeta['display_price'], 0, ',', '.'),
                        );
                    @endphp
                    <article class="rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-gold-500/50 hover:shadow-[0_18px_48px_rgba(254,172,24,0.10)] dark:border-white/10 dark:bg-zinc-950/45">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-gold-600 dark:text-gold-400">{{ $packageMeta['kind_label'] }}</p>
                            <span class="member-status-pill {{ $isMembership ? 'member-status-info' : 'member-status-neutral' }}">{{ $packageMeta['type_label'] }}</span>
                        </div>
                        <h4 class="mt-3 break-words font-black text-zinc-950 dark:text-white">{{ $package->name }}</h4>
                        <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $package->description ?? 'Paket Platinum Gym Padang.' }}</p>

                        <div class="mt-4 flex flex-wrap items-baseline gap-2">
                            <p class="text-xl font-black text-zinc-950 dark:text-white">Rp {{ number_format((float) $packageMeta['display_price'], 0, ',', '.') }}</p>
                            @if ($packageMeta['has_promo'])
                                <p class="text-sm font-bold text-zinc-400 line-through dark:text-zinc-500" aria-label="Harga normal">Rp {{ number_format((float) $packageMeta['price'], 0, ',', '.') }}</p>
                                <span class="member-status-pill member-status-warning">Promo</span>
                            @endif
                        </div>

                        @if ($needsMembership && ! $activeMembership)
                            <p class="member-unavailable-note mt-4">Paket ini membutuhkan membership aktif.</p>
                        @endif

                        @if ($canCheckout)
                            <x-confirm-form
                                :action="$checkoutRoute"
                                method="POST"
                                :message="$confirmMessage"
                                variant="primary"
                                confirm-label="Lanjut Checkout"
                                class="mt-4 grid gap-3"
                            >
                                @if ($requiresTrainer)
                                    <div>
                                        <label for="{{ $trainerSelectId }}" class="block text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Pilih Trainer</label>
                                        @if (! empty($packageTrainerList))
                                            <select id="{{ $trainerSelectId }}" name="trainer_id" required class="mt-2 min-h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                                <option value="">Pilih trainer...</option>
                                                @foreach ($packageTrainerList as $trainerOption)
                                                    <option value="{{ $trainerOption['id'] }}" @selected((int) old('trainer_id') === (int) $trainerOption['id'])>{{ $trainerOption['label'] }}</option>
                                                @endforeach
                                            </select>
                                            <p class="mt-2 text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Trainer akan menerima notifikasi penjadwalan setelah pembayaran berhasil.</p>
                                        @else
                                            <p class="member-unavailable-note mt-2">Belum ada trainer aktif untuk paket ini. Hubungi admin Platinum Gym.</p>
                                        @endif
                                        <x-input-error class="mt-2" :messages="$errors->get('trainer_id')" />
                                    </div>
                                @endif
                                <button type="submit" class="member-button-primary w-full" @disabled($requiresTrainer && empty($packageTrainerList))>
                                    {{ $isMembership ? 'Checkout Membership' : 'Checkout Paket Sesi' }}
                                </button>
                            </x-confirm-form>
                        @else
                            <div class="mt-4 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm font-bold text-zinc-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-400">
                                Aktifkan membership terlebih dahulu.
                                <a href="{{ route('member.membership', ['kind' => 'membership']) }}" class="ml-1 font-black text-gold-700 underline-offset-2 hover:underline dark:text-gold-400">Lihat membership</a>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            @include('member.partials.empty-state', [
                'icon' => 'card',
                'title' => 'Paket tidak ditemukan',
                'body' => 'Ubah kata kunci atau filter jenis paket untuk melihat layanan yang tersedia.',
                'class' => 'mt-5 md:col-span-2',
            ])
        @endif

        @include('member.partials.pagination', ['paginator' => $packages, 'label' => 'paket membership'])
    </section>
</div>
