<div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)]">
    <section class="member-card">
        <p class="member-eyebrow">Paket Aktif</p>
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Status membership</h3>
        @if ($activeMembership)
            <div class="mt-5 rounded-lg border border-emerald-500/25 bg-emerald-500/10 p-5">
                <span class="member-status-pill bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">Aktif</span>
                <h4 class="mt-4 text-2xl font-black text-zinc-950 dark:text-white">{{ $activeMembership->package?->name ?? $activeMembership->code }}</h4>
                <p class="mt-2 member-copy">{{ $activeMembership->start_date?->translatedFormat('d M Y') }} sampai {{ $activeMembership->end_date?->translatedFormat('d M Y') }}.</p>
                <p class="mt-4 text-2xl font-black text-gold-600 dark:text-gold-400">Rp {{ number_format((float) $activeMembership->price, 0, ',', '.') }}</p>
            </div>
        @else
            <div class="member-soft-panel mt-5">
                <h4 class="font-black text-zinc-950 dark:text-white">Belum ada membership aktif</h4>
                <p class="mt-2 member-copy">Paket aktif akan tampil setelah pembelian dan verifikasi selesai. Untuk saat ini, Anda dapat melihat katalog layanan Platinum Gym.</p>
                <a href="{{ route('public.services') }}" class="member-button-secondary mt-4 w-full">Lihat Katalog Layanan</a>
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
                <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Pilihan tersedia</h3>
            </div>
            <a href="{{ route('public.services') }}" class="member-button-secondary">Lihat Publik</a>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @forelse ($packages as $package)
                <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-gold-600 dark:text-gold-400">{{ str((string) $package->package_kind)->headline() }}</p>
                    <h4 class="mt-2 break-words font-black text-zinc-950 dark:text-white">{{ $package->name }}</h4>
                    <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $package->description ?? 'Paket Platinum Gym Padang.' }}</p>
                    <p class="mt-4 text-xl font-black text-zinc-950 dark:text-white">Rp {{ number_format((float) ($package->promo_price ?? $package->price), 0, ',', '.') }}</p>
                    <p class="member-unavailable-note mt-4">Pembelian paket dibantu petugas Platinum Gym sampai pembayaran member tersedia di portal.</p>
                </article>
            @empty
                <div class="member-soft-panel md:col-span-2">Paket belum tersedia.</div>
            @endforelse
        </div>
    </section>
</div>
