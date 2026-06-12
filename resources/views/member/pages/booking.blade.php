<section class="member-card mt-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="member-eyebrow">Jadwal</p>
            <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Kelas aktif</h3>
        </div>
        <a href="{{ route('public.classes') }}" class="member-button-secondary">Jadwal Publik</a>
    </div>
    <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($classSchedules as $schedule)
            @php($capacity = $schedule->capacity ?? $schedule->gymClass?->capacity ?? 0)
            <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                <span class="member-status-pill bg-gold-500/15 text-gold-700 dark:text-gold-300">{{ $dayLabels[$schedule->day_of_week] ?? 'Jadwal' }}</span>
                <h4 class="mt-4 break-words text-lg font-black text-zinc-950 dark:text-white">{{ $schedule->gymClass?->name ?? 'Kelas Platinum Gym' }}</h4>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Waktu</dt><dd class="font-black text-zinc-950 dark:text-white">{{ substr((string) $schedule->start_time, 0, 5) }} - {{ substr((string) $schedule->end_time, 0, 5) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Coach</dt><dd class="text-right font-black text-zinc-950 dark:text-white">{{ $schedule->trainer?->name ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Kuota</dt><dd class="font-black text-zinc-950 dark:text-white">{{ $schedule->booked_count }}/{{ $capacity }}</dd></div>
                </dl>
                <p class="member-unavailable-note mt-5">Jadwal ini menjadi panduan kelas aktif. Petugas dapat membantu pendaftaran sampai booking member tersedia di portal.</p>
            </article>
        @empty
            <div class="member-soft-panel md:col-span-2 xl:col-span-3">Jadwal kelas belum tersedia.</div>
        @endforelse
    </div>
</section>
