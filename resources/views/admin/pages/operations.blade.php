    @if ($page['key'] === 'profile')
        <section class="admin-card mt-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <p class="admin-eyebrow">Akun Admin</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Kelola akun login</h2>
                    <p class="mt-2 admin-copy">Perbarui nama, email, dan kata sandi akun admin Anda di halaman keamanan akun.</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="admin-button-primary shrink-0">
                    @include('admin.partials.icon', ['name' => 'shield', 'class' => 'h-4 w-4'])
                    Edit Akun Saya
                </a>
            </div>
        </section>
    @endif

    @if ($page['key'] === 'payments')
        <section class="admin-card mt-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <p class="admin-eyebrow">Pembayaran Cash</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Catat transaksi langsung</h2>
                    <p class="mt-2 admin-copy">Gunakan untuk pembayaran di kasir. Sistem membuat transaksi, invoice, dan aktivasi layanan dalam satu proses.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.payments.cash') }}" class="mt-5 grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)]">
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
                    <button type="submit" class="admin-button-primary w-full">Catat Cash</button>
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
                    <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
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
                                    <input id="reject-reason-{{ $payment->id }}" type="text" name="reason" required maxlength="500" class="min-h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white" placeholder="Alasan" aria-label="Alasan penolakan pembayaran {{ $payment->payment_code }}">
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
                    <p class="mt-2 admin-copy">Ringkasan dan export CSV memakai periode yang sama.</p>
                </div>
                <a x-bind:href="'{{ $reportsExportBase }}?date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo)" class="admin-button-primary">
                    @include('admin.partials.icon', ['name' => 'download', 'class' => 'h-4 w-4'])
                    Export CSV
                </a>
            </div>
            <form method="GET" action="{{ route('admin.reports') }}" class="mt-5 grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45 md:grid-cols-[1fr_1fr_auto] md:items-end">
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
            <form method="GET" action="{{ route('admin.audit-log') }}" class="mt-5 grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end" x-data="{ dateFrom: '{{ $portal['filters']['date_from'] ?? now()->startOfMonth()->toDateString() }}', dateTo: '{{ $portal['filters']['date_to'] ?? now()->toDateString() }}' }">
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Dari</span>
                    <input type="date" name="date_from" x-model="dateFrom" x-bind:max="dateTo" class="admin-form-input mt-2">
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Sampai</span>
                    <input type="date" name="date_to" x-model="dateTo" x-bind:min="dateFrom" class="admin-form-input mt-2">
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Event</span>
                    <select name="event" class="admin-form-input mt-2">
                        <option value="">Semua event</option>
                        @foreach (['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted'] as $eventValue => $eventLabel)
                            <option value="{{ $eventValue }}" @selected(($portal['filters']['event'] ?? '') === $eventValue)>{{ $eventLabel }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">User</span>
                    <select name="causer_id" class="admin-form-input mt-2">
                        <option value="">Semua user</option>
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
        <section class="admin-card mt-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <p class="admin-eyebrow">Pengaturan Website</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Informasi publik website</h2>
                    <p class="mt-2 admin-copy">Perbarui informasi publik yang tampil di website. Informasi sensitif tidak ditampilkan.</p>
                </div>
            </div>
            <x-confirm-form
                :action="route('admin.settings.update')"
                method="PATCH"
                message="Simpan perubahan pengaturan website? Nilai baru langsung tampil di website publik."
                title="Simpan Pengaturan"
                confirm-label="Simpan"
                variant="primary"
                class="mt-5 grid gap-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45 lg:grid-cols-2"
            >
                @foreach (($portal['editableSettings']['fields'] ?? []) as $field)
                    @php
                        $value = old($field['name'], $portal['editableSettings']['values'][$field['name']] ?? '');
                        $normalizedKey = str($field['name'])->lower()->toString();
                        $isSensitiveField = str_contains($normalizedKey, 'secret')
                            || str_contains($normalizedKey, 'token')
                            || str_contains($normalizedKey, 'password')
                            || str_contains($normalizedKey, 'credential')
                            || str_contains($normalizedKey, 'api_key')
                            || str_contains($normalizedKey, 'private')
                            || str_ends_with($normalizedKey, '_key');
                    @endphp
                    <label class="{{ $field['type'] === 'textarea' ? 'lg:col-span-2' : '' }}">
                        <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ $field['label'] }}</span>
                        @if ($field['type'] === 'textarea')
                            <textarea name="{{ $field['name'] }}" rows="3" class="admin-form-input mt-2">{{ $value }}</textarea>
                        @elseif ($isSensitiveField)
                            <div class="relative mt-2" x-data="{ show: false }">
                                <input x-bind:type="show ? 'text' : 'password'" name="{{ $field['name'] }}" value="{{ $value }}" autocomplete="off" spellcheck="false" class="admin-form-input pr-12">
                                <button type="button" x-on:click="show = !show" class="absolute inset-y-0 right-2 my-auto inline-flex h-8 w-8 items-center justify-center rounded-md text-zinc-500 transition hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:text-zinc-400 dark:hover:text-gold-400" x-bind:aria-label="show ? 'Sembunyikan nilai' : 'Tampilkan nilai'" x-bind:aria-pressed="show.toString()">
                                    <span x-show="!show">@include('admin.partials.icon', ['name' => 'eye', 'class' => 'h-5 w-5'])</span>
                                    <span x-show="show" x-cloak>@include('admin.partials.icon', ['name' => 'eye-off', 'class' => 'h-5 w-5'])</span>
                                </button>
                            </div>
                        @else
                            <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" value="{{ $value }}" class="admin-form-input mt-2">
                        @endif
                        @error($field['name']) <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span> @enderror
                    </label>
                @endforeach
                <div class="lg:col-span-2">
                    <button type="submit" class="admin-button-primary">Simpan Pengaturan</button>
                </div>
            </x-confirm-form>
        </section>
    @endif

    @if ($page['key'] === 'booking')
        <section class="admin-card mt-6">
            <p class="admin-eyebrow">Tindakan Booking</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Konfirmasi kelas hari ini</h2>
            <form method="POST" action="{{ route('admin.booking.store') }}" class="mt-5 grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_12rem_auto] lg:items-end">
                @csrf
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Member <span class="text-red-500" aria-hidden="true">*</span></span>
                    <select name="member_id" class="admin-form-input mt-2" required>
                        <option value="">Pilih member</option>
                        @foreach (collect($portal['bookingMembers'] ?? []) as $memberOption)
                            <option value="{{ $memberOption->id }}" @selected((string) old('member_id') === (string) $memberOption->id)>{{ $memberOption->user?->name ?? $memberOption->member_code }} - {{ $memberOption->member_code }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Jadwal <span class="text-red-500" aria-hidden="true">*</span></span>
                    <select name="schedule_id" class="admin-form-input mt-2" required>
                        <option value="">Pilih jadwal</option>
                        @foreach (collect($portal['bookingSchedules'] ?? []) as $scheduleOption)
                            <option value="{{ $scheduleOption->id }}" @selected((string) old('schedule_id') === (string) $scheduleOption->id)>{{ $scheduleOption->gymClass?->name }} - {{ [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'][(int) $scheduleOption->day_of_week] ?? '-' }} {{ substr((string) $scheduleOption->start_time, 0, 5) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Tanggal <span class="text-red-500" aria-hidden="true">*</span></span>
                    <input type="date" name="session_date" value="{{ old('session_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}" class="admin-form-input mt-2" required>
                </label>
                <button type="submit" class="admin-button-primary">Tambah Booking</button>
            </form>
            <div class="mt-5 grid gap-3">
                @forelse (collect($portal['todayBookings'] ?? [])->whereNotIn('status', ['cancelled', 'canceled']) as $enrollment)
                    @php
                        $bookingBadge = \App\Features\Admin\ViewModels\AdminStatusViewModel::booking($enrollment->status);
                    @endphp
                    <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-black text-zinc-950 dark:text-white">{{ $enrollment->member?->user?->name ?? $enrollment->member?->member_code }}</p>
                                    <span class="admin-status-pill {{ $bookingBadge['class'] }}">{{ $bookingBadge['label'] }}</span>
                                </div>
                                <p class="mt-1 text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $enrollment->schedule?->gymClass?->name }} - {{ substr((string) $enrollment->schedule?->start_time, 0, 5) }}</p>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <form method="POST" action="{{ route('admin.booking.confirm', $enrollment) }}">
                                    @csrf
                                    <button type="submit" class="admin-button-primary w-full">Konfirmasi</button>
                                </form>
                                <x-confirm-form
                                    :action="route('admin.booking.cancel', $enrollment)"
                                    method="POST"
                                    :message="'Batalkan booking ' . ($enrollment->schedule?->gymClass?->name ?? 'kelas ini') . ' untuk member ini? Tindakan ini tidak bisa dibatalkan.'"
                                    variant="danger"
                                    confirm-label="Batalkan"
                                >
                                    <button type="submit" class="admin-button-danger w-full">Batalkan</button>
                                </x-confirm-form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="admin-soft-panel">Tidak ada booking kelas hari ini.</div>
                @endforelse
            </div>
        </section>
    @endif
