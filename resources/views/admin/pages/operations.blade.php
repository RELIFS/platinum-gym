    @if ($page['key'] === 'profile')
        @include('admin.pages.profile-overview')
    @endif

    @if ($page['key'] === 'payments')
        <section class="admin-card mt-6" x-data="{ dateFrom: @js($portal['filters']['date_from'] ?? now()->startOfMonth()->toDateString()), dateTo: @js($portal['filters']['date_to'] ?? now()->toDateString()) }">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <p class="admin-eyebrow">Pembayaran Tunai</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Catat transaksi langsung</h2>
                    <p class="mt-2 admin-copy">Gunakan saat member membayar langsung di kasir. Sistem akan mencatat pembayaran dan mengaktifkan layanan dalam satu proses.</p>
                </div>
            </div>
            <form
                method="POST"
                action="{{ route('admin.payments.cash') }}"
                class="admin-panel mt-5 grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)]"
                x-data="adminCashPaymentForm(@js($portal['paymentTrainerOptionsByPackage'] ?? []), @js($portal['paymentPackageTrainerRules'] ?? []), @js((string) old('package_id')), @js((string) old('trainer_id')))"
            >
                @csrf
                <label class="admin-field">
                    <span class="admin-field-label">Member <span class="admin-required" aria-hidden="true">*</span></span>
                    <select name="member_id" class="admin-form-input" required>
                        <option value="">Pilih member</option>
                        @foreach (collect($portal['paymentMembers'] ?? []) as $memberOption)
                            <option value="{{ $memberOption->id }}" @selected((string) old('member_id') === (string) $memberOption->id)>{{ $memberOption->user?->name ?? $memberOption->member_code }} - {{ $memberOption->member_code }}</option>
                        @endforeach
                    </select>
                    @error('member_id') <span class="admin-field-error" role="alert">{{ $message }}</span> @enderror
                </label>
                <label class="admin-field">
                    <span class="admin-field-label">Paket <span class="admin-required" aria-hidden="true">*</span></span>
                    <select name="package_id" class="admin-form-input" required x-model="packageId" x-on:change="syncTrainer()">
                        <option value="">Pilih paket</option>
                        @foreach (collect($portal['paymentPackages'] ?? []) as $packageOption)
                            <option value="{{ $packageOption->id }}" @selected((string) old('package_id') === (string) $packageOption->id)>{{ $packageOption->name }} - {{ str((string) $packageOption->package_kind)->headline() }} - Rp {{ number_format((float) ($packageOption->promo_price ?? $packageOption->price), 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    @error('package_id') <span class="admin-field-error" role="alert">{{ $message }}</span> @enderror
                </label>
                <label class="admin-field">
                    <span class="admin-field-label">Trainer <span class="admin-required" aria-hidden="true" x-show="trainerRequired">*</span></span>
                    <select name="trainer_id" class="admin-form-input" x-model="trainerId" x-bind:disabled="trainerDisabled" x-bind:required="trainerRequired">
                        <option value="" x-text="trainerPlaceholder"></option>
                        <template x-for="trainer in trainerOptions" :key="trainer.id">
                            <option x-bind:value="trainer.id" x-text="trainer.label"></option>
                        </template>
                    </select>
                    <span class="admin-field-help" x-show="trainerRequired">Trainer wajib sesuai spesialisasi paket yang dipilih.</span>
                    @error('trainer_id') <span class="admin-field-error" role="alert">{{ $message }}</span> @enderror
                </label>
                <label class="admin-field xl:col-span-2">
                    <span class="admin-field-label">Catatan</span>
                    <textarea name="note" maxlength="500" rows="3" class="admin-form-input" placeholder="Contoh: Pembayaran kasir shift pagi">{{ old('note') }}</textarea>
                    @error('note') <span class="admin-field-error" role="alert">{{ $message }}</span> @enderror
                </label>
                <div class="flex items-end">
                    <button type="submit" class="admin-button-primary w-full">Catat Pembayaran Tunai</button>
                </div>
            </form>
        </section>

        <section class="admin-card mt-6">
            <p class="admin-eyebrow">Tindakan Pembayaran</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Verifikasi transaksi</h2>
            <div class="mt-5 grid gap-3">
                @forelse (collect($portal['recentPayments'] ?? [])->whereNotIn('status', ['paid', 'rejected', 'failed', 'expired', 'cancelled']) as $payment)
                    @php
                        $paymentBadge = \App\Features\Admin\ViewModels\AdminStatusViewModel::payment($payment->status);
                    @endphp
                    <article class="admin-panel">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-mono text-sm font-black text-zinc-950 dark:text-white">{{ $payment->payment_code }}</p>
                                    <span class="admin-status-pill {{ $paymentBadge['class'] }}">{{ $paymentBadge['label'] }}</span>
                                </div>
                                <p class="mt-1 text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $payment->member?->user?->name ?? $payment->member?->member_code }} - Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <x-confirm-form
                                    :action="route('admin.payments.approve', $payment)"
                                    method="POST"
                                    :message="'Setujui pembayaran ' . $payment->payment_code . ' senilai Rp ' . number_format((float) $payment->amount, 0, ',', '.') . '? Layanan member akan langsung diaktifkan.'"
                                    title="Setujui Pembayaran"
                                    confirm-label="Setujui"
                                    variant="primary"
                                >
                                    <button type="submit" class="admin-button-primary w-full">Setujui</button>
                                </x-confirm-form>
                                <x-confirm-form
                                    :action="route('admin.payments.reject', $payment)"
                                    method="POST"
                                    :message="'Tolak pembayaran ' . $payment->payment_code . '? Transaksi dan layanan terkait akan dibatalkan.'"
                                    title="Tolak Pembayaran"
                                    confirm-label="Tolak"
                                    variant="danger"
                                    class="flex gap-2"
                                >
                                    <label class="sr-only" for="reject-reason-{{ $payment->id }}">Alasan penolakan pembayaran {{ $payment->payment_code }}</label>
                                    <input id="reject-reason-{{ $payment->id }}" type="text" name="reason" required maxlength="500" class="admin-form-input" placeholder="Contoh: Bukti pembayaran belum sesuai" aria-label="Alasan penolakan pembayaran {{ $payment->payment_code }}">
                                    <button type="submit" class="admin-button-danger">Tolak</button>
                                </x-confirm-form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="admin-soft-panel">Tidak ada pembayaran yang menunggu tindakan.</div>
                @endforelse
            </div>
        </section>
    @endif

    @if ($page['key'] === 'reports')
        @php
            $reportsExportBase = route('admin.reports.export');
        @endphp
        <section class="admin-card mt-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <p class="admin-eyebrow">Filter Laporan</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Periode operasional</h2>
                    <p class="mt-2 admin-copy">Ringkasan dan file CSV, Excel, serta PDF memakai periode yang sama.</p>
                </div>
                <div class="grid w-full gap-2 sm:w-auto sm:grid-cols-3">
                    <a x-bind:href="'{{ $reportsExportBase }}?date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo)" class="admin-button-secondary justify-center">
                        @include('admin.partials.icon', ['name' => 'download', 'class' => 'h-4 w-4'])
                        Unduh CSV
                    </a>
                    <a x-bind:href="'{{ $reportsExportBase }}?format=xlsx&date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo)" class="admin-button-secondary justify-center">
                        @include('admin.partials.icon', ['name' => 'download', 'class' => 'h-4 w-4'])
                        Unduh Excel
                    </a>
                    <a x-bind:href="'{{ $reportsExportBase }}?format=pdf&date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo)" class="admin-button-primary justify-center">
                        @include('admin.partials.icon', ['name' => 'download', 'class' => 'h-4 w-4'])
                        Unduh PDF
                    </a>
                </div>
            </div>
            <form method="GET" action="{{ route('admin.reports') }}" class="admin-panel mt-5 grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
                <label class="admin-field">
                    <span class="admin-field-label">Dari tanggal</span>
                    <x-local-date-input id="admin-report-date-from" name="date_from" :value="$portal['filters']['date_from'] ?? now()->startOfMonth()->toDateString()" :max="$portal['filters']['date_to'] ?? now()->toDateString()" class="admin-form-input" />
                </label>
                <label class="admin-field">
                    <span class="admin-field-label">Sampai tanggal</span>
                    <x-local-date-input id="admin-report-date-to" name="date_to" :value="$portal['filters']['date_to'] ?? now()->toDateString()" :min="$portal['filters']['date_from'] ?? now()->startOfMonth()->toDateString()" class="admin-form-input" />
                </label>
                <button type="submit" class="admin-button-primary">Terapkan</button>
            </form>
        </section>
    @endif

    @if ($page['key'] === 'audit-log')
        <section class="admin-card mt-6">
            <p class="admin-eyebrow">Filter Audit</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Jejak perubahan sistem</h2>
            <form method="GET" action="{{ route('admin.audit-log') }}" class="admin-panel mt-5 grid gap-4 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end">
                <label class="admin-field">
                    <span class="admin-field-label">Dari tanggal</span>
                    <x-local-date-input id="admin-audit-date-from" name="date_from" :value="$portal['filters']['date_from'] ?? now()->startOfMonth()->toDateString()" :max="$portal['filters']['date_to'] ?? now()->toDateString()" class="admin-form-input" />
                </label>
                <label class="admin-field">
                    <span class="admin-field-label">Sampai tanggal</span>
                    <x-local-date-input id="admin-audit-date-to" name="date_to" :value="$portal['filters']['date_to'] ?? now()->toDateString()" :min="$portal['filters']['date_from'] ?? now()->startOfMonth()->toDateString()" class="admin-form-input" />
                </label>
                <label class="admin-field">
                    <span class="admin-field-label">Jenis perubahan</span>
                    <select name="event" class="admin-form-input">
                        <option value="">Semua aktivitas</option>
                        @foreach (['created' => 'Dibuat', 'updated' => 'Diperbarui', 'deleted' => 'Dihapus'] as $eventValue => $eventLabel)
                            <option value="{{ $eventValue }}" @selected(($portal['filters']['event'] ?? '') === $eventValue)>{{ $eventLabel }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="admin-field">
                    <span class="admin-field-label">Admin</span>
                    <select name="causer_id" class="admin-form-input">
                        <option value="">Semua admin</option>
                        @foreach (collect($portal['activityUsers'] ?? []) as $activityUser)
                            <option value="{{ $activityUser->id }}" @selected((string) ($portal['filters']['causer_id'] ?? '') === (string) $activityUser->id)>{{ $activityUser->name }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="admin-button-primary">Terapkan</button>
            </form>
        </section>
    @endif

    @if ($page['key'] === 'settings')
        @include('admin.pages.settings-form')
    @endif

    @if ($page['key'] === 'booking')
        @php
            $bookingDayLabels = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
            $bookingMinDate = \App\Features\Bookings\Support\BookingTimePolicy::earliestBookingDate()->toDateString();
            $bookingScheduleOptions = collect($portal['bookingSchedules'] ?? [])
                ->map(fn ($scheduleOption) => [
                    'id' => (string) $scheduleOption->id,
                    'day_of_week' => (int) $scheduleOption->day_of_week,
                    'label' => ($scheduleOption->gymClass?->name ?? 'Kelas Platinum Gym').' - '.($bookingDayLabels[(int) $scheduleOption->day_of_week] ?? '-').' '.substr((string) $scheduleOption->start_time, 0, 5),
                ])
                ->values();
        @endphp
        <section class="admin-card mt-6">
            <p class="admin-eyebrow">Tindakan Booking</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Tambah booking kelas</h2>
            <form
                method="POST"
                action="{{ route('admin.booking.store') }}"
                class="admin-panel relative z-10 mt-5 grid items-start gap-4 overflow-visible md:grid-cols-2 2xl:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)_12rem_auto]"
                x-data="adminBookingForm(@js(old('session_date', $bookingMinDate)), @js($bookingScheduleOptions), @js($portal['bookingMemberScheduleAccess'] ?? []), @js((string) old('member_id')), @js((string) old('schedule_id')), @js($bookingMinDate))"
                x-init="syncDate($refs.scheduleSelect, false)"
                x-on:admin-member-selected="setMember($event.detail.memberId)"
                x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
            >
                @csrf
                @php
                    $memberErrorId = 'admin-booking-member-error';
                    $scheduleErrorId = 'admin-booking-schedule-error';
                    $dateHelpId = 'admin-booking-date-help';
                    $dateErrorId = 'admin-booking-date-error';
                @endphp
                <label class="admin-field relative z-20 md:col-span-2 2xl:col-span-1" x-data="adminMemberCombobox(@js(collect($portal['bookingMembers'] ?? [])->map(fn ($memberOption) => ['id' => (string) $memberOption->id, 'label' => ($memberOption->user?->name ?? $memberOption->member_code).' - '.$memberOption->member_code])->values()), @js((string) old('member_id')))" x-on:click.outside="open = false">
                    <span class="admin-field-label">Member <span class="admin-required" aria-hidden="true">*</span></span>
                    <input type="hidden" name="member_id" x-bind:value="selectedId">
                    <span class="relative block">
                        <input
                            x-ref="memberSearch"
                            type="text"
                            class="admin-form-input pr-12"
                            x-model="query"
                            x-on:focus="open = true"
                            x-on:input="search()"
                            x-on:keydown.arrow-down.prevent="move(1)"
                            x-on:keydown.arrow-up.prevent="move(-1)"
                            x-on:keydown.enter.prevent="chooseHighlighted()"
                            x-on:keydown.escape="open = false"
                            placeholder="Cari nama atau kode member"
                            role="combobox"
                            aria-autocomplete="list"
                            x-bind:aria-expanded="open.toString()"
                            x-bind:aria-activedescendant="activeOptionId"
                            aria-controls="admin-booking-member-list"
                            autocomplete="off"
                            @error('member_id') aria-invalid="true" aria-describedby="{{ $memberErrorId }}" @enderror
                        >
                        <button type="button" class="absolute inset-y-1 right-1 inline-flex h-9 w-9 items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white" x-on:click="clear()" aria-label="Bersihkan pilihan member">
                            @include('admin.partials.icon', ['name' => 'x', 'class' => 'h-4 w-4'])
                        </button>
                        <span id="admin-booking-member-list" class="absolute left-0 top-full z-50 mt-2 max-h-64 w-full overflow-y-auto overscroll-contain rounded-lg border border-zinc-200 bg-white p-2 shadow-2xl dark:border-white/10 dark:bg-zinc-900" x-cloak x-show="open" role="listbox">
                            <template x-for="(option, index) in filtered" :key="option.id">
                                <button type="button" class="block w-full rounded-md px-3 py-2 text-left text-sm font-bold transition" x-bind:id="'admin-booking-member-option-' + option.id" x-bind:class="index === highlighted ? 'bg-gold-500 text-zinc-950' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-white/10'" x-on:mousedown.prevent="choose(option)" role="option" x-bind:aria-selected="selectedId === option.id">
                                    <span x-text="option.label"></span>
                                </button>
                            </template>
                            <span class="block px-3 py-2 text-sm font-semibold text-zinc-500 dark:text-zinc-400" x-show="filtered.length === 0">Member tidak ditemukan.</span>
                        </span>
                    </span>
                    <span class="admin-field-help">Pilih member dari hasil pencarian.</span>
                    @error('member_id') <span id="{{ $memberErrorId }}" class="admin-field-error" role="alert">{{ $message }}</span> @enderror
                </label>
                <label class="admin-field">
                    <span class="admin-field-label">Jadwal <span class="admin-required" aria-hidden="true">*</span></span>
                    <select
                        name="schedule_id"
                        class="admin-form-input"
                        required
                        x-ref="scheduleSelect"
                        x-model="selectedScheduleId"
                        x-bind:disabled="scheduleDisabled"
                        x-on:change="syncDate($event.target)"
                        @error('schedule_id') aria-invalid="true" aria-describedby="{{ $scheduleErrorId }}" @enderror
                    >
                        <option value="" x-text="schedulePlaceholder"></option>
                        <template x-for="schedule in eligibleSchedules" :key="schedule.id">
                            <option x-bind:value="schedule.id" x-bind:data-day-of-week="schedule.day_of_week" x-text="schedule.label"></option>
                        </template>
                    </select>
                    <span class="admin-field-help">Jadwal mengikuti paket aktif member.</span>
                    @error('schedule_id') <span id="{{ $scheduleErrorId }}" class="admin-field-error" role="alert">{{ $message }}</span> @enderror
                </label>
                <label class="admin-field">
                    <span class="admin-field-label">Tanggal <span class="admin-required" aria-hidden="true">*</span></span>
                    <x-local-date-input
                        id="admin-booking-session-date"
                        name="session_date"
                        x-model="sessionDate"
                        :value="old('session_date', $bookingMinDate)"
                        :min="$bookingMinDate"
                        class="admin-form-input"
                        required
                        :described-by="$dateHelpId"
                    />
                    <span id="{{ $dateHelpId }}" class="admin-field-help">Tanggal mengikuti hari jadwal kelas dan minimal 1 hari sebelum jadwal.</span>
                    @error('session_date') <span id="{{ $dateErrorId }}" class="admin-field-error" role="alert">{{ $message }}</span> @enderror
                </label>
                <button type="submit" class="admin-button-primary w-full md:col-span-2 2xl:col-span-1 2xl:mt-8 2xl:w-auto" x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">
                    Tambah Booking
                </button>
            </form>
            <div class="mt-5 grid gap-3">
                @forelse (collect($portal['todayBookings'] ?? [])->whereNotIn('status', ['cancelled', 'canceled']) as $enrollment)
                    @php
                        $bookingBadge = \App\Features\Admin\ViewModels\AdminStatusViewModel::booking($enrollment->status);
                        $bookingStatus = (string) $enrollment->status;
                        $hasAttendance = (bool) $enrollment->getAttribute('attendance_exists');
                        $isPastBooking = (bool) ($enrollment->session_date?->isPast() && ! $enrollment->session_date?->isToday());
                        $canCancelByTime = \App\Features\Bookings\Support\BookingTimePolicy::canCancel($enrollment);
                        $canConfirmBooking = in_array($bookingStatus, ['booked', 'active'], true);
                        $canCancelBooking = ! in_array($bookingStatus, ['attended', 'cancelled', 'canceled'], true) && ! $hasAttendance && ! $isPastBooking && $canCancelByTime;
                        $bookingActionNote = match (true) {
                            $bookingStatus === 'pending_payment' => 'Menunggu pembayaran lunas sebelum bisa dikonfirmasi.',
                            $bookingStatus === 'confirmed' => 'Booking sudah siap untuk proses check-in.',
                            $bookingStatus === 'attended' || $hasAttendance => 'Peserta sudah tercatat hadir.',
                            $isPastBooking => 'Tanggal booking sudah lewat.',
                            ! $canCancelByTime => 'Pembatalan hanya bisa dilakukan paling lambat 3 jam sebelum kelas dimulai.',
                            default => null,
                        };
                    @endphp
                    <article class="admin-panel">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-black text-zinc-950 dark:text-white">{{ $enrollment->member?->user?->name ?? $enrollment->member?->member_code }}</p>
                                    <span class="admin-status-pill {{ $bookingBadge['class'] }}">{{ $bookingBadge['label'] }}</span>
                                </div>
                                <p class="mt-1 text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $enrollment->schedule?->gymClass?->name }} - {{ substr((string) $enrollment->schedule?->start_time, 0, 5) }}</p>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                @if ($canConfirmBooking)
                                    <form method="POST" action="{{ route('admin.booking.confirm', $enrollment) }}">
                                        @csrf
                                        <button type="submit" class="admin-button-primary w-full">Konfirmasi</button>
                                    </form>
                                @endif
                                @if ($canCancelBooking)
                                    <x-confirm-form
                                        :action="route('admin.booking.cancel', $enrollment)"
                                        method="POST"
                                        :message="'Batalkan booking ' . ($enrollment->schedule?->gymClass?->name ?? 'kelas ini') . ' untuk member ini? Tindakan ini tidak bisa dibatalkan.'"
                                        variant="danger"
                                        confirm-label="Batalkan Booking"
                                    >
                                        <button type="submit" class="admin-button-danger w-full">Batalkan Booking</button>
                                    </x-confirm-form>
                                @endif
                                @if ($bookingActionNote)
                                    <p class="max-w-xs rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-bold leading-5 text-zinc-600 dark:border-white/10 dark:bg-white/[0.045] dark:text-zinc-300">{{ $bookingActionNote }}</p>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="admin-soft-panel">Belum ada booking kelas terbaru.</div>
                @endforelse
            </div>
        </section>
    @endif
