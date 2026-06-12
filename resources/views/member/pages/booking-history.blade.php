<section class="member-card mt-6">
    <p class="member-eyebrow">Booking</p>
    <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Riwayat kelas</h3>
    <div class="mt-5 grid gap-4 md:grid-cols-2">
        @forelse ($recentEnrollments as $enrollment)
            <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h4 class="break-words font-black text-zinc-950 dark:text-white">{{ $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym' }}</h4>
                        <p class="mt-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $enrollment->session_date?->translatedFormat('l, d M Y') }} - {{ substr((string) $enrollment->schedule?->start_time, 0, 5) }}</p>
                    </div>
                    <span class="member-status-pill bg-zinc-100 text-zinc-700 dark:bg-white/[0.07] dark:text-zinc-300">{{ str((string) $enrollment->status)->headline() }}</span>
                </div>
            </article>
        @empty
            <div class="member-soft-panel md:col-span-2">
                <h4 class="font-black text-zinc-950 dark:text-white">Belum ada riwayat booking</h4>
                <p class="mt-2 member-copy">Riwayat kelas akan tampil setelah booking tercatat.</p>
            </div>
        @endforelse
    </div>
</section>
