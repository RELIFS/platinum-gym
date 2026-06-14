<section class="member-card mt-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="member-eyebrow">Jadwal</p>
            <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Booking kelas aktif</h3>
        </div>
        <a href="{{ route('member.bookings') }}" class="member-button-secondary">Riwayat Booking</a>
    </div>
    <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($classSchedules as $schedule)
            @php
                $capacity = $schedule->capacity ?? $schedule->gymClass?->capacity ?? 0;
                $accessType = (string) $schedule->gymClass?->access_type;
                $accessLabel = match ($accessType) {
                    'included' => 'Membership',
                    'paid' => 'Bayar Kelas',
                    'session_based' => 'Paket Sesi',
                    default => 'Kelas',
                };
                $capacityLeft = max((int) $capacity - (int) $schedule->booked_count, 0);
            @endphp
            <article class="rounded-lg border border-zinc-200 bg-white p-4 transition hover:border-gold-500/50 hover:shadow-[0_18px_48px_rgba(254,172,24,0.10)] dark:border-white/10 dark:bg-zinc-950/45">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="member-status-pill bg-gold-500/15 text-gold-700 dark:text-gold-300">{{ $dayLabels[$schedule->day_of_week] ?? 'Jadwal' }}</span>
                    <span class="member-status-pill {{ $accessType === 'paid' ? 'member-status-warning' : 'member-status-neutral' }}">{{ $accessLabel }}</span>
                </div>
                <h4 class="mt-4 break-words text-lg font-black text-zinc-950 dark:text-white">{{ $schedule->gymClass?->name ?? 'Kelas Platinum Gym' }}</h4>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Waktu</dt><dd class="font-black text-zinc-950 dark:text-white">{{ substr((string) $schedule->start_time, 0, 5) }} - {{ substr((string) $schedule->end_time, 0, 5) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Coach</dt><dd class="text-right font-black text-zinc-950 dark:text-white">{{ $schedule->trainer?->name ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Kuota</dt><dd class="font-black text-zinc-950 dark:text-white">{{ $capacityLeft }} tersisa</dd></div>
                </dl>

                <form method="POST" action="{{ route('member.booking.store', $schedule) }}" class="mt-5 grid gap-3">
                    @csrf
                    <label class="block">
                        <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Tanggal Kelas</span>
                        <input type="date" name="session_date" value="{{ old('session_date', $schedule->next_session_date) }}" min="{{ now()->toDateString() }}" aria-describedby="schedule-help-{{ $schedule->id }}" class="mt-2 min-h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                    </label>
                    <p id="schedule-help-{{ $schedule->id }}" class="text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Pilih tanggal yang sesuai hari {{ $dayLabels[$schedule->day_of_week] ?? 'jadwal' }}.</p>
                    <button type="submit" class="member-button-primary w-full">Booking Kelas</button>
                </form>
            </article>
        @empty
            <div class="member-soft-panel md:col-span-2 xl:col-span-3">Jadwal kelas belum tersedia.</div>
        @endforelse
    </div>
</section>
