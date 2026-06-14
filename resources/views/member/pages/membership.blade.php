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
                <p class="mt-2 member-copy">Pilih paket membership di katalog, checkout, lalu selesaikan pembayaran Midtrans Sandbox untuk mengaktifkan layanan.</p>
            </div>
        @endif

        @if ($activePackageSessions->isNotEmpty())
            <div class="mt-5 grid gap-3">
                @foreach ($activePackageSessions as $session)
                    <article class="member-soft-panel">
                        <p class="font-black text-zinc-950 dark:text-white">{{ $session->package?->name ?? $session->code }}</p>
                        <p class="mt-2 text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $session->remaining_sessions }} dari {{ $session->total_sessions }} sesi tersisa</p>
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
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @forelse ($packages as $package)
                @php
                    $isMembership = $package->package_kind === 'membership';
                    $needsMembership = (bool) $package->requires_active_membership;
                    $canCheckout = $isMembership || ! $needsMembership || (bool) $activeMembership;
                    $checkoutRoute = $isMembership ? route('member.membership.checkout', $package) : route('member.package-sessions.checkout', $package);
                    $packageKind = match ((string) $package->package_kind) {
                        'membership' => 'Membership',
                        'personal_trainer' => 'Personal Trainer',
                        'muaythai' => 'Muaythai',
                        default => str((string) $package->package_kind)->replace('_', ' ')->headline()->toString(),
                    };
                @endphp
                <article class="rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-gold-500/50 hover:shadow-[0_18px_48px_rgba(254,172,24,0.10)] dark:border-white/10 dark:bg-zinc-950/45">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-gold-600 dark:text-gold-400">{{ $packageKind }}</p>
                        @if ($isMembership)
                            <span class="member-status-pill member-status-info">Membership</span>
                        @else
                            <span class="member-status-pill member-status-neutral">Sesi</span>
                        @endif
                    </div>
                    <h4 class="mt-3 break-words font-black text-zinc-950 dark:text-white">{{ $package->name }}</h4>
                    <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $package->description ?? 'Paket Platinum Gym Padang.' }}</p>
                    <p class="mt-4 text-xl font-black text-zinc-950 dark:text-white">Rp {{ number_format((float) ($package->promo_price ?? $package->price), 0, ',', '.') }}</p>

                    @if ($needsMembership && ! $activeMembership)
                        <p class="member-unavailable-note mt-4">Paket ini membutuhkan membership aktif.</p>
                    @endif

                    @if ($canCheckout)
                        <form method="POST" action="{{ $checkoutRoute }}" class="mt-4">
                            @csrf
                            <button type="submit" class="member-button-primary w-full">
                                {{ $isMembership ? 'Checkout Membership' : 'Checkout Paket Sesi' }}
                            </button>
                        </form>
                    @else
                        <div class="mt-4 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm font-bold text-zinc-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-400">Aktifkan membership terlebih dahulu.</div>
                    @endif
                </article>
            @empty
                <div class="member-soft-panel md:col-span-2">Paket belum tersedia.</div>
            @endforelse
        </div>
    </section>
</div>
