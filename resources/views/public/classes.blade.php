<x-public-layout :settings="$settings" title="Jadwal Kelas Platinum Gym Padang" description="Lihat jadwal kelas Aerobic, Zumba, Poundfit, dan Muaythai Platinum Gym Padang.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'Jadwal Kelas',
        'title' => 'Kelas aktif dengan coach dan kuota jelas.',
        'description' => 'Gunakan filter hari dan jenis kelas untuk menemukan jadwal yang sesuai rutinitas Anda.',
        'primaryUrl' => '#jadwal-kelas',
        'primaryLabel' => 'Lihat Jadwal',
        'secondaryUrl' => route('login'),
        'secondaryLabel' => 'Masuk Booking',
    ])

    <section id="jadwal-kelas" class="public-section public-section-muted scroll-mt-24">
        <div class="public-container">
            <form method="GET" action="{{ route('public.classes') }}" class="public-card public-motion-reveal grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end md:p-7" aria-describedby="classes-filter-status" data-motion="reveal">
                <div>
                    <label for="hari" class="mb-2 block text-sm type-control text-zinc-700 dark:text-zinc-300">Hari</label>
                    <select id="hari" name="hari" class="public-input">
                        <option value="">Semua hari</option>
                        @foreach ($dayOptions as $day => $value)
                            <option value="{{ $day }}" @selected($selectedDay === $day)>{{ $dayLabels[$value] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="jenis" class="mb-2 block text-sm type-control text-zinc-700 dark:text-zinc-300">Jenis kelas</label>
                    <select id="jenis" name="jenis" class="public-input">
                        <option value="">Semua jenis</option>
                        @foreach ($classTypeOptions as $value => $label)
                            <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="public-button-primary w-full md:w-auto">Terapkan</button>
                    <a href="{{ route('public.classes') }}" class="public-button-secondary w-full md:w-auto">Reset</a>
                </div>
            </form>

            <p id="classes-filter-status" class="mt-5 break-words text-sm type-control text-zinc-600 dark:text-zinc-400" role="status">
                Menampilkan {{ $schedules->count() }} jadwal{{ $selectedDay ? ' untuk hari '.$dayLabels[$dayOptions[$selectedDay]] : '' }}{{ $selectedType ? ' jenis '.$classTypeOptions[$selectedType] : '' }}.
            </p>

            <div class="mt-10 space-y-12">
                @foreach ($classSections as $section)
                    <section id="kelas-{{ $section['key'] }}" aria-labelledby="kelas-title-{{ $section['key'] }}" class="public-motion-reveal scroll-mt-24" data-motion="reveal">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="public-eyebrow">{{ $section['label'] }}</p>
                                <h2 id="kelas-title-{{ $section['key'] }}" class="public-heading-balance mt-3 text-3xl type-title text-zinc-950 dark:text-zinc-100">{{ $section['label'] }}</h2>
                                <p class="mt-3 max-w-2xl text-sm leading-7 text-zinc-600 dark:text-zinc-400">{{ $section['description'] }}</p>
                            </div>
                            <span class="inline-flex min-h-10 w-fit items-center rounded-full border border-zinc-200 bg-white px-4 py-2 text-xs type-control uppercase tracking-[0.11em] text-zinc-600 shadow-sm dark:border-white/10 dark:bg-white/[0.045] dark:text-zinc-300">
                                {{ $section['schedules']->count() }} jadwal
                            </span>
                        </div>

                        <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                            @forelse ($section['schedules'] as $schedule)
                                @include('public.partials.schedule-card', ['schedule' => $schedule, 'dayLabels' => $dayLabels, 'settings' => $settings])
                            @empty
                                <div class="public-card md:col-span-2 xl:col-span-3">
                                    <h3 class="text-xl type-title text-zinc-950 dark:text-zinc-100">Jadwal tidak ditemukan.</h3>
                                    <p class="mt-2 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Jadwal {{ $section['label'] }} belum tersedia. Coba ubah filter hari atau jenis kelas.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endforeach

                @if ($classSections->isEmpty())
                    <div class="public-card">
                        <h2 class="text-xl type-title text-zinc-950 dark:text-zinc-100">Jadwal tidak ditemukan.</h2>
                        <p class="mt-2 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Coba ubah filter hari atau jenis kelas.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="public-section public-section-plain">
        <div class="public-container grid gap-6 lg:grid-cols-3">
            <article class="public-card public-motion-card public-motion-reveal" data-motion="reveal card">
                <h2 class="text-xl type-title text-zinc-950 dark:text-zinc-100">Included Class</h2>
                <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Aerobic dan Zumba dapat termasuk dalam paket senam sesuai membership aktif.</p>
            </article>
            <article class="public-card public-motion-card public-motion-reveal" data-motion="reveal card" data-motion-delay="80">
                <h2 class="text-xl type-title text-zinc-950 dark:text-zinc-100">Paid Class</h2>
                <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Poundfit dan beberapa sesi khusus dapat memiliki harga terpisah sesuai jadwal dan kuota.</p>
            </article>
            <article class="public-card public-motion-card public-motion-reveal" data-motion="reveal card" data-motion-delay="160">
                <h2 class="text-xl type-title text-zinc-950 dark:text-zinc-100">Booking</h2>
                <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Jadwal, coach, kuota, dan harga ditampilkan di website. Member dapat masuk untuk mengajukan booking digital sesuai kuota kelas.</p>
                <a href="{{ route('login') }}" class="public-button-primary mt-6 w-full">Masuk untuk Booking</a>
            </article>
        </div>
    </section>
</x-public-layout>
