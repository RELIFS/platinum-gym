<section class="member-card mt-6">
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
        'searchPlaceholder' => 'Cari kelas, coach, tipe akses...',
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
                            @php
                                $scheduleMeta = $schedule->member_status_meta ?? [
                                    'access_label' => 'Kelas',
                                    'access_class' => 'member-status-neutral',
                                    'capacity_left' => 0,
                                    'capacity_full' => false,
                                    'day_label' => $dayLabels[$schedule->day_of_week] ?? 'Jadwal',
                                    'is_paid' => false,
                                    'is_included' => false,
                                    'is_session_based' => false,
                                    'member_price' => null,
                                    'non_member_price' => null,
                                    'promo_price' => null,
                                    'display_price' => null,
                                    'has_promo' => false,
                                    'button_label' => 'Booking Kelas',
                                    'can_book' => true,
                                    'disabled_reason' => null,
                                    'cta_label' => null,
                                    'cta_url' => null,
                                ];
                                $isFull = (bool) ($scheduleMeta['capacity_full'] ?? false);
                                $isPaid = (bool) ($scheduleMeta['is_paid'] ?? false);
                                $canBook = (bool) ($scheduleMeta['can_book'] ?? true);
                                $confirmMessage = $isPaid && filled($scheduleMeta['display_price'] ?? null)
                                    ? sprintf(
                                        'Lanjut booking %s? Anda akan diarahkan ke pembayaran Midtrans senilai Rp %s.',
                                        addslashes((string) ($schedule->gymClass?->name ?? 'kelas')),
                                        number_format((float) $scheduleMeta['display_price'], 0, ',', '.'),
                                    )
                                    : null;
                            @endphp
                            <article class="member-list-card flex min-w-0 flex-col">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="member-status-pill bg-gold-500/15 text-gold-700 dark:text-gold-300">{{ $scheduleMeta['day_label'] }}</span>
                                    <span class="member-status-pill {{ $scheduleMeta['access_class'] }}">{{ $scheduleMeta['access_label'] }}</span>
                                    @if ($isFull)
                                        <span class="member-status-pill member-status-danger">Kuota Habis</span>
                                    @endif
                                </div>
                                <h5 class="mt-4 min-w-0 break-words text-lg font-black text-zinc-950 dark:text-white">{{ $schedule->gymClass?->name ?? 'Kelas Platinum Gym' }}</h5>
                                <dl class="mt-4 space-y-2 text-sm">
                                    <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Waktu</dt><dd class="font-black text-zinc-950 dark:text-white">{{ substr((string) $schedule->start_time, 0, 5) }} - {{ substr((string) $schedule->end_time, 0, 5) }}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Coach</dt><dd class="text-right font-black text-zinc-950 dark:text-white">{{ $schedule->trainer?->name ?? '-' }}</dd></div>
                                    <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Kuota</dt><dd class="font-black {{ $isFull ? 'text-red-600 dark:text-red-300' : 'text-zinc-950 dark:text-white' }}">{{ $scheduleMeta['capacity_left'] }} tersisa</dd></div>
                                    @if ($isPaid && filled($scheduleMeta['display_price'] ?? null))
                                        <div class="flex justify-between gap-4">
                                            <dt class="font-semibold text-zinc-500">Biaya Kelas</dt>
                                            <dd class="text-right">
                                                <span class="font-black text-gold-600 dark:text-gold-400">Rp {{ number_format((float) $scheduleMeta['display_price'], 0, ',', '.') }}</span>
                                                @if ($scheduleMeta['has_promo'] ?? false)
                                                    <span class="ml-1 text-xs font-bold text-zinc-400 line-through" aria-label="Harga normal kelas">Rp {{ number_format((float) ($scheduleMeta['member_price'] ?? 0), 0, ',', '.') }}</span>
                                                @endif
                                            </dd>
                                        </div>
                                    @elseif (($scheduleMeta['is_included'] ?? false))
                                        <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Biaya</dt><dd class="font-black text-emerald-600 dark:text-emerald-300">Termasuk Membership</dd></div>
                                    @elseif (($scheduleMeta['is_session_based'] ?? false))
                                        <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Biaya</dt><dd class="font-black text-emerald-600 dark:text-emerald-300">Termasuk Paket Sesi</dd></div>
                                    @endif
                                </dl>

                                <x-confirm-form
                                    :action="route('member.booking.store', $schedule)"
                                    method="POST"
                                    :message="(! $isFull && $canBook && $confirmMessage) ? $confirmMessage : ''"
                                    :confirm-label="$scheduleMeta['button_label'] ?? 'Booking Kelas'"
                                    variant="primary"
                                    class="mt-auto grid gap-3 pt-5"
                                    x-data="memberBookingForm({{ (int) $schedule->day_of_week }})"
                                >
                                    <label class="block">
                                        <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Tanggal Kelas</span>
                                        <input
                                            type="date"
                                            name="session_date"
                                            value="{{ old('session_date', $schedule->next_session_date) }}"
                                            min="{{ now()->toDateString() }}"
                                            aria-describedby="schedule-help-{{ $schedule->id }}"
                                            x-on:change="snapToDay($event)"
                                            class="member-form-input mt-2"
                                            @disabled($isFull)
                                        >
                                    </label>
                                    <p id="schedule-help-{{ $schedule->id }}" class="text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Pilih tanggal yang sesuai hari {{ $scheduleMeta['day_label'] }}. Tanggal akan otomatis menyesuaikan jika tidak cocok.</p>

                                    <button
                                        type="submit"
                                        class="member-button-primary w-full {{ $isFull ? 'cursor-not-allowed opacity-60' : '' }}"
                                        @disabled($isFull)
                                        @if ($isFull) aria-disabled="true" @endif
                                    >
                                        {{ $scheduleMeta['button_label'] ?? 'Booking Kelas' }}
                                    </button>
                                </x-confirm-form>
                            </article>
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

<script>
    if (typeof window.memberBookingForm === 'undefined') {
        window.memberBookingForm = function (targetIso) {
            return {
                snapToDay(event) {
                    const input = event.target;
                    const value = input?.value;
                    if (!value) return;
                    const parsed = new Date(value + 'T00:00:00');
                    if (Number.isNaN(parsed.getTime())) return;
                    // JavaScript getDay(): Sunday=0..Saturday=6. ISO: Mon=1..Sun=7.
                    const jsDay = parsed.getDay();
                    const iso = jsDay === 0 ? 7 : jsDay;
                    if (iso === targetIso) return;
                    let diff = (targetIso - iso + 7) % 7;
                    if (diff === 0) diff = 7;
                    const next = new Date(parsed.getTime());
                    next.setDate(next.getDate() + diff);
                    const yyyy = next.getFullYear();
                    const mm = String(next.getMonth() + 1).padStart(2, '0');
                    const dd = String(next.getDate()).padStart(2, '0');
                    input.value = `${yyyy}-${mm}-${dd}`;
                },
            };
        };
    }
</script>
