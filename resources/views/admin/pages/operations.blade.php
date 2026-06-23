    @if ($page['key'] === 'profile')
        @include('admin.pages.profile-overview')
    @endif

    @if ($page['key'] === 'payments')
        <section class="admin-card mt-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <p class="admin-eyebrow">Pembayaran Tunai</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Catat transaksi langsung</h2>
                    <p class="mt-2 admin-copy">Gunakan saat member membayar langsung di kasir. Sistem akan mencatat pembayaran dan mengaktifkan layanan dalam satu proses.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.payments.cash') }}" class="admin-panel mt-5 grid gap-3 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)]">
                @csrf
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Member <span class="text-red-500" aria-hidden="true">*</span></span>
                    <select name="member_id" class="admin-form-input mt-2" required>
                        <option value="">Pilih member</option>
                        @foreach (collect($portal['paymentMembers'] ?? []) as $memberOption)
                            <option value="{{ $memberOption->id }}" @selected((string) old('member_id') === (string) $memberOption->id)>{{ $memberOption->user?->name ?? $memberOption->member_code }} - {{ $memberOption->member_code }}</option>
                        @endforeach
                    </select>
                    @error('member_id') <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span> @enderror
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Paket <span class="text-red-500" aria-hidden="true">*</span></span>
                    <select name="package_id" class="admin-form-input mt-2" required>
                        <option value="">Pilih paket</option>
                        @foreach (collect($portal['paymentPackages'] ?? []) as $packageOption)
                            <option value="{{ $packageOption->id }}" @selected((string) old('package_id') === (string) $packageOption->id)>{{ $packageOption->name }} - {{ str((string) $packageOption->package_kind)->headline() }} - Rp {{ number_format((float) ($packageOption->promo_price ?? $packageOption->price), 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    @error('package_id') <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span> @enderror
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Trainer Opsional</span>
                    <select name="trainer_id" class="admin-form-input mt-2">
                        <option value="">Tanpa trainer</option>
                        @foreach (collect($portal['paymentTrainers'] ?? []) as $trainerOption)
                            <option value="{{ $trainerOption->id }}" @selected((string) old('trainer_id') === (string) $trainerOption->id)>{{ $trainerOption->name }}</option>
                        @endforeach
                    </select>
                    @error('trainer_id') <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span> @enderror
                </label>
                <label class="xl:col-span-2">
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Catatan</span>
                    <textarea name="note" maxlength="500" rows="3" class="admin-form-input mt-2" placeholder="Contoh: Pembayaran kasir shift pagi">{{ old('note') }}</textarea>
                    @error('note') <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span> @enderror
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
                                    <input id="reject-reason-{{ $payment->id }}" type="text" name="reason" required maxlength="500" class="min-h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white" placeholder="Tulis alasan penolakan" aria-label="Alasan penolakan pembayaran {{ $payment->payment_code }}">
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
        <section class="admin-card mt-6" x-data="{ dateFrom: @js($portal['filters']['date_from'] ?? now()->startOfMonth()->toDateString()), dateTo: @js($portal['filters']['date_to'] ?? now()->toDateString()) }">
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
            <form method="GET" action="{{ route('admin.reports') }}" class="admin-panel mt-5 grid gap-3 md:grid-cols-[1fr_1fr_auto] md:items-end">
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Dari Tanggal</span>
                    <input type="date" name="date_from" x-model="dateFrom" x-bind:max="dateTo" class="admin-form-input mt-2">
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Sampai Tanggal</span>
                    <input type="date" name="date_to" x-model="dateTo" x-bind:min="dateFrom" class="admin-form-input mt-2">
                </label>
                <button type="submit" class="admin-button-primary">Terapkan</button>
            </form>
        </section>
    @endif

    @if ($page['key'] === 'audit-log')
        <section class="admin-card mt-6">
            <p class="admin-eyebrow">Filter Audit</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Jejak perubahan sistem</h2>
            <form method="GET" action="{{ route('admin.audit-log') }}" class="admin-panel mt-5 grid gap-3 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end" x-data="{ dateFrom: '{{ $portal['filters']['date_from'] ?? now()->startOfMonth()->toDateString() }}', dateTo: '{{ $portal['filters']['date_to'] ?? now()->toDateString() }}' }">
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Dari</span>
                    <input type="date" name="date_from" x-model="dateFrom" x-bind:max="dateTo" class="admin-form-input mt-2">
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Sampai</span>
                    <input type="date" name="date_to" x-model="dateTo" x-bind:min="dateFrom" class="admin-form-input mt-2">
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Jenis Perubahan</span>
                    <select name="event" class="admin-form-input mt-2">
                        <option value="">Semua aktivitas</option>
                        @foreach (['created' => 'Dibuat', 'updated' => 'Diperbarui', 'deleted' => 'Dihapus'] as $eventValue => $eventLabel)
                            <option value="{{ $eventValue }}" @selected(($portal['filters']['event'] ?? '') === $eventValue)>{{ $eventLabel }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Admin</span>
                    <select name="causer_id" class="admin-form-input mt-2">
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
        @endphp
        <section class="admin-card mt-6">
            <p class="admin-eyebrow">Tindakan Booking</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Konfirmasi kelas hari ini</h2>
            <form
                method="POST"
                action="{{ route('admin.booking.store') }}"
                class="admin-panel mt-5 grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_12rem_auto] lg:items-end"
                x-data="adminBookingForm(@js(old('session_date', now()->toDateString())))"
                x-init="syncDate($refs.scheduleSelect, false)"
                x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
            >
                @csrf
                @php
                    $memberErrorId = 'admin-booking-member-error';
                    $scheduleErrorId = 'admin-booking-schedule-error';
                    $dateHelpId = 'admin-booking-date-help';
                    $dateErrorId = 'admin-booking-date-error';
                @endphp
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Member <span class="text-red-500" aria-hidden="true">*</span></span>
                    <select
                        name="member_id"
                        class="admin-form-input mt-2"
                        required
                        @error('member_id') aria-invalid="true" aria-describedby="{{ $memberErrorId }}" @enderror
                    >
                        <option value="">Pilih member</option>
                        @foreach (collect($portal['bookingMembers'] ?? []) as $memberOption)
                            <option value="{{ $memberOption->id }}" @selected((string) old('member_id') === (string) $memberOption->id)>{{ $memberOption->user?->name ?? $memberOption->member_code }} - {{ $memberOption->member_code }}</option>
                        @endforeach
                    </select>
                    @error('member_id') <span id="{{ $memberErrorId }}" class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span> @enderror
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Jadwal <span class="text-red-500" aria-hidden="true">*</span></span>
                    <select
                        name="schedule_id"
                        class="admin-form-input mt-2"
                        required
                        x-ref="scheduleSelect"
                        x-on:change="syncDate($event.target)"
                        @error('schedule_id') aria-invalid="true" aria-describedby="{{ $scheduleErrorId }}" @enderror
                    >
                        <option value="">Pilih jadwal</option>
                        @foreach (collect($portal['bookingSchedules'] ?? []) as $scheduleOption)
                            <option value="{{ $scheduleOption->id }}" data-day-of-week="{{ (int) $scheduleOption->day_of_week }}" @selected((string) old('schedule_id') === (string) $scheduleOption->id)>{{ $scheduleOption->gymClass?->name }} - {{ $bookingDayLabels[(int) $scheduleOption->day_of_week] ?? '-' }} {{ substr((string) $scheduleOption->start_time, 0, 5) }}</option>
                        @endforeach
                    </select>
                    @error('schedule_id') <span id="{{ $scheduleErrorId }}" class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span> @enderror
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Tanggal <span class="text-red-500" aria-hidden="true">*</span></span>
                    <input
                        type="date"
                        name="session_date"
                        x-model="sessionDate"
                        min="{{ now()->toDateString() }}"
                        class="admin-form-input mt-2"
                        required
                        aria-describedby="{{ $dateHelpId }}{{ $errors->has('session_date') ? ' '.$dateErrorId : '' }}"
                        @error('session_date') aria-invalid="true" @enderror
                    >
                    <span id="{{ $dateHelpId }}" class="mt-2 block text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Tanggal otomatis disesuaikan dengan hari jadwal kelas.</span>
                    @error('session_date') <span id="{{ $dateErrorId }}" class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span> @enderror
                </label>
                <button type="submit" class="admin-button-primary" x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">
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
                        $canConfirmBooking = in_array($bookingStatus, ['booked', 'active'], true);
                        $canCancelBooking = ! in_array($bookingStatus, ['attended', 'cancelled', 'canceled'], true) && ! $hasAttendance && ! $isPastBooking;
                        $bookingActionNote = match (true) {
                            $bookingStatus === 'pending_payment' => 'Menunggu pembayaran lunas sebelum bisa dikonfirmasi.',
                            $bookingStatus === 'confirmed' => 'Booking sudah siap untuk proses check-in.',
                            $bookingStatus === 'attended' || $hasAttendance => 'Peserta sudah tercatat hadir.',
                            $isPastBooking => 'Tanggal booking sudah lewat.',
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
                    <div class="admin-soft-panel">Tidak ada booking kelas hari ini.</div>
                @endforelse
            </div>
        </section>
        <script>
            if (typeof window.adminBookingForm === 'undefined') {
                window.adminBookingForm = function (initialDate) {
                    return {
                        submitting: false,
                        sessionDate: initialDate,
                        syncDate(select, shouldUpdate = true) {
                            const option = select?.selectedOptions?.[0];
                            const targetIso = Number(option?.dataset?.dayOfWeek || 0);
                            if (! targetIso) return;
                            const nextDate = this.nextDateForIso(targetIso, this.sessionDate || this.today());
                            if (shouldUpdate || ! this.sessionDate) {
                                this.sessionDate = nextDate;
                            }
                        },
                        nextDateForIso(targetIso, fromDate) {
                            let parsed = this.parseLocalDate(fromDate) || this.parseLocalDate(this.today());
                            const today = this.parseLocalDate(this.today());
                            if (parsed < today) parsed = today;
                            const jsDay = parsed.getDay();
                            const currentIso = jsDay === 0 ? 7 : jsDay;
                            const diff = (targetIso - currentIso + 7) % 7;
                            parsed.setDate(parsed.getDate() + diff);

                            return this.formatLocalDate(parsed);
                        },
                        parseLocalDate(value) {
                            if (! value) return null;
                            const parsed = new Date(`${value}T00:00:00`);

                            return Number.isNaN(parsed.getTime()) ? null : parsed;
                        },
                        formatLocalDate(date) {
                            const yyyy = date.getFullYear();
                            const mm = String(date.getMonth() + 1).padStart(2, '0');
                            const dd = String(date.getDate()).padStart(2, '0');

                            return `${yyyy}-${mm}-${dd}`;
                        },
                        today() {
                            return this.formatLocalDate(new Date());
                        },
                    };
                };
            }
        </script>
    @endif
