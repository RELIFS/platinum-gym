<section class="member-card mt-6">
    <div class="member-section-header">
        <div>
            <p class="member-eyebrow">Booking</p>
            <h3 class="member-section-title">Riwayat kelas</h3>
        </div>
        <a href="{{ route('member.booking') }}" class="member-button-secondary">Booking Kelas</a>
    </div>

    @include('member.partials.filter-toolbar', [
        'filters' => $portal['pageFilters'] ?? [],
        'searchLabel' => 'Cari riwayat booking',
        'searchPlaceholder' => 'Cari kelas, pelatih, status...',
        'selects' => [
            [
                'name' => 'status',
                'label' => 'Filter status booking',
                'placeholder' => 'Semua status',
                'options' => $portal['filterOptions']['bookingStatuses'] ?? [],
            ],
        ],
    ])

    @if ($recentEnrollments->count() > 0)
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @foreach ($recentEnrollments as $enrollment)
                @php($bookingMeta = $enrollment->member_status_meta ?? ['label' => str((string) $enrollment->status)->headline()->toString(), 'class' => 'member-status-neutral', 'can_cancel' => false])
                <article class="member-list-card">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="break-words type-title text-zinc-950 dark:text-zinc-100">{{ $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym' }}</h4>
                            <p class="mt-2 text-sm type-compact text-zinc-500 dark:text-zinc-400">{{ $enrollment->session_date?->translatedFormat('l, d M Y') }} - {{ substr((string) $enrollment->schedule?->start_time, 0, 5) }}</p>
                        </div>
                        <span class="member-status-pill {{ $bookingMeta['class'] }}">{{ $bookingMeta['label'] }}</span>
                    </div>
                    @if ($bookingMeta['can_cancel'])
                        <x-confirm-form
                            :action="route('member.bookings.destroy', $enrollment)"
                            method="DELETE"
                            :message="'Yakin ingin membatalkan booking ' . ($enrollment->schedule?->gymClass?->name ?? 'kelas ini') . '? Tindakan ini tidak bisa dibatalkan.'"
                            variant="danger"
                            confirm-label="Batalkan Jadwal Booking"
                            class="mt-4"
                        >
                            <button type="submit" class="member-button-danger w-full">Batalkan Jadwal Booking</button>
                            <p class="mt-2 text-xs type-control leading-5 text-zinc-500 dark:text-zinc-400">Booking bisa dibatalkan paling lambat 3 jam sebelum kelas dimulai.</p>
                        </x-confirm-form>
                    @endif
                </article>
            @endforeach
        </div>
    @else
        @include('member.partials.empty-state', [
            'icon' => 'calendar',
            'title' => 'Belum ada riwayat booking',
            'body' => 'Riwayat kelas akan tampil setelah pendaftaran atau booking tercatat di sistem.',
            'class' => 'mt-5 md:col-span-2',
        ])
    @endif

    @include('member.partials.pagination', ['paginator' => $recentEnrollments, 'label' => 'riwayat booking'])
</section>
