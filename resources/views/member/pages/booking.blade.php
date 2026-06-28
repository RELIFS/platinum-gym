<section class="member-card mt-6">
    @php($bookingMinDate = \App\Features\Bookings\Support\BookingTimePolicy::earliestBookingDate()->toDateString())

    <div class="member-section-header">
        <div>
            <p class="member-eyebrow">Jadwal</p>
            <h3 class="member-section-title">Booking kelas aktif</h3>
        </div>
        <a href="{{ route('member.bookings') }}" class="member-button-secondary">Riwayat Booking</a>
    </div>

    @include('member.partials.filter-toolbar', [
        'filters' => $portal['pageFilters'] ?? [],
        'searchLabel' => 'Cari jadwal kelas',
        'searchPlaceholder' => 'Cari kelas, instruktur, coach, tipe akses...',
        'selects' => [
            [
                'name' => 'day',
                'label' => 'Filter hari kelas',
                'placeholder' => 'Semua hari',
                'options' => $portal['filterOptions']['days'] ?? [],
            ],
            [
                'name' => 'access',
                'label' => 'Filter akses kelas',
                'placeholder' => 'Semua akses',
                'options' => $portal['filterOptions']['classAccess'] ?? [],
            ],
        ],
    ])

    @if ($classSchedules->count() > 0)
        <div class="mt-6 space-y-8 pb-20 sm:pb-24 lg:pb-6">
            @foreach (($portal['classScheduleGroups'] ?? []) as $scheduleGroup)
                <section class="min-w-0" aria-labelledby="class-group-{{ $scheduleGroup['key'] }}">
                    <div class="mb-4 flex min-w-0 flex-wrap items-end justify-between gap-3 border-b border-zinc-200 pb-3 dark:border-white/10">
                        <div class="min-w-0">
                            <p class="member-eyebrow">Kelas</p>
                            <h4 id="class-group-{{ $scheduleGroup['key'] }}" class="break-words text-xl font-black text-zinc-950 dark:text-white">{{ $scheduleGroup['title'] }}</h4>
                        </div>
                        <span class="member-status-pill member-status-neutral">{{ $scheduleGroup['schedules']->count() }} jadwal</span>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($scheduleGroup['schedules'] as $schedule)
                            @include('member.partials.booking-schedule-card', [
                                'schedule' => $schedule,
                                'bookingMinDate' => $bookingMinDate,
                            ])
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        @include('member.partials.empty-state', [
            'icon' => 'calendar',
            'title' => 'Jadwal kelas tidak ditemukan',
            'body' => 'Ubah kata kunci, hari, atau akses kelas untuk melihat jadwal aktif.',
            'class' => 'mt-5',
        ])
    @endif

    @include('member.partials.pagination', ['paginator' => $classSchedules, 'label' => 'jadwal kelas'])
</section>
