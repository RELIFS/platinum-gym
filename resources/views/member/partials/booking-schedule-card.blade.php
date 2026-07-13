@php
    $scheduleMeta = $schedule->member_status_meta ?? [
        'access_label' => 'Kelas',
        'access_class' => 'member-status-neutral',
        'capacity_left' => 0,
        'capacity_full' => false,
        'day_label' => 'Jadwal',
        'is_paid' => false,
        'is_included' => false,
        'is_session_based' => false,
        'member_price' => null,
        'display_price' => null,
        'has_promo' => false,
        'button_label' => 'Booking Kelas',
        'can_book' => true,
        'disabled_reason' => null,
    ];
    $isFull = (bool) ($scheduleMeta['capacity_full'] ?? false);
    $isPaid = (bool) ($scheduleMeta['is_paid'] ?? false);
    $canBook = (bool) ($scheduleMeta['can_book'] ?? true);
    $isDisabled = $isFull || ! $canBook;
    $staffRoleLabel = $schedule->staff_role_label ?? \App\Features\Classes\Support\ClassStaffPresenter::roleLabel($schedule);
    $staffDisplayName = $schedule->staff_display_name ?? \App\Features\Classes\Support\ClassStaffPresenter::memberBookingDisplayName($schedule->trainer, $schedule);
    $timeLabel = $schedule->time_label ?? \App\Features\Classes\Support\ClassStaffPresenter::timeLabel($schedule);
    $confirmMessage = $isPaid && filled($scheduleMeta['display_price'] ?? null)
        ? sprintf(
            'Lanjut booking %s? Anda akan diarahkan ke pembayaran Midtrans senilai Rp %s.',
            addslashes((string) ($schedule->gymClass?->name ?? 'kelas')),
            number_format((float) $scheduleMeta['display_price'], 0, ',', '.'),
        )
        : null;
    $initialSessionDate = old('session_date', $schedule->next_session_date);
    $bookingFormState = 'memberBookingForm('
        .(int) $schedule->day_of_week
        .', '
        .\Illuminate\Support\Js::from($initialSessionDate)
        .', '
        .\Illuminate\Support\Js::from($bookingMinDate)
        .')';
@endphp

<article class="member-list-card flex min-w-0 flex-col">
    <div class="flex flex-wrap items-center gap-2">
        <span class="member-status-pill bg-gold-500/15 text-gold-text">{{ $scheduleMeta['day_label'] }}</span>
        <span class="member-status-pill {{ $scheduleMeta['access_class'] }}">{{ $scheduleMeta['access_label'] }}</span>
        @if ($isFull)
            <span class="member-status-pill member-status-danger">Kuota Habis</span>
        @endif
    </div>
    <h5 class="mt-4 min-w-0 break-words text-lg type-title text-zinc-950 dark:text-zinc-100">{{ $schedule->gymClass?->name ?? 'Kelas Platinum Gym' }}</h5>
    <dl class="mt-4 space-y-2 text-sm">
        <div class="flex justify-between gap-4"><dt class="type-control text-zinc-500">Waktu</dt><dd class="type-control text-zinc-950 dark:text-zinc-100">{{ $timeLabel }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="shrink-0 type-control text-zinc-500">{{ $staffRoleLabel }}</dt><dd class="min-w-0 break-words text-right type-control text-zinc-950 dark:text-zinc-100">{{ $staffDisplayName }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="type-control text-zinc-500">Kuota</dt><dd class="type-control {{ $isFull ? 'text-red-600 dark:text-red-300' : 'text-zinc-950 dark:text-zinc-100' }}">{{ $scheduleMeta['capacity_left'] }} tersisa</dd></div>
        @if ($isPaid && filled($scheduleMeta['display_price'] ?? null))
            <div class="flex justify-between gap-4">
                <dt class="type-control text-zinc-500">Biaya Kelas</dt>
                <dd class="text-right">
                    <span class="type-emphasis text-gold-text">Rp {{ number_format((float) $scheduleMeta['display_price'], 0, ',', '.') }}</span>
                    @if ($scheduleMeta['has_promo'] ?? false)
                        <span class="ml-1 text-xs type-control text-zinc-400 line-through" aria-label="Harga normal kelas">Rp {{ number_format((float) ($scheduleMeta['member_price'] ?? 0), 0, ',', '.') }}</span>
                    @endif
                </dd>
            </div>
        @elseif (($scheduleMeta['is_included'] ?? false))
            <div class="flex justify-between gap-4"><dt class="type-control text-zinc-500">Biaya</dt><dd class="type-control text-emerald-600 dark:text-emerald-300">Termasuk Membership</dd></div>
        @elseif (($scheduleMeta['is_session_based'] ?? false))
            <div class="flex justify-between gap-4"><dt class="type-control text-zinc-500">Biaya</dt><dd class="type-control text-emerald-600 dark:text-emerald-300">Termasuk Paket Sesi</dd></div>
        @endif
    </dl>

    <x-confirm-form
        :action="route('member.booking.store', $schedule)"
        method="POST"
        :message="(! $isDisabled && $confirmMessage) ? $confirmMessage : ''"
        :confirm-label="$scheduleMeta['button_label'] ?? 'Booking Kelas'"
        variant="primary"
        class="mt-auto grid gap-3 pt-5"
        x-data="{{ $bookingFormState }}"
    >
        <label class="block">
            <span class="text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Tanggal Kelas</span>
            <x-local-date-input
                id="member-booking-session-date-{{ $schedule->id }}"
                name="session_date"
                x-model="sessionDate"
                :value="$initialSessionDate"
                :min="$bookingMinDate"
                picker="flatpickr"
                :allowed-weekdays="[(int) $schedule->day_of_week]"
                class="member-form-input mt-2"
                :disabled="$isDisabled"
                described-by="schedule-help-{{ $schedule->id }}"
            />
        </label>
        <p id="schedule-help-{{ $schedule->id }}" class="text-xs type-control leading-5 text-zinc-500 dark:text-zinc-400">Pilih tanggal sesuai hari {{ $scheduleMeta['day_label'] }}. Booking minimal 1 hari sebelum jadwal.</p>

        <button
            type="submit"
            class="member-button-primary w-full {{ $isDisabled ? 'cursor-not-allowed opacity-60' : '' }}"
            @disabled($isDisabled)
            @if ($isDisabled) aria-disabled="true" @endif
        >
            {{ $scheduleMeta['button_label'] ?? 'Booking Kelas' }}
        </button>
    </x-confirm-form>
</article>
