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
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Member</span>
                    <select name="member_id" class="admin-form-input mt-2" required>
                        <option value="">Pilih member</option>
                        @foreach (collect($portal['paymentMembers'] ?? []) as $memberOption)
                            <option value="{{ $memberOption->id }}" @selected((string) old('member_id') === (string) $memberOption->id)>{{ $memberOption->user?->name ?? $memberOption->member_code }} - {{ $memberOption->member_code }}</option>
                        @endforeach
                    </select>
                    @error('member_id') <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300">{{ $message }}</span> @enderror
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Paket</span>
                    <select name="package_id" class="admin-form-input mt-2" required>
                        <option value="">Pilih paket</option>
                        @foreach (collect($portal['paymentPackages'] ?? []) as $packageOption)
                            <option value="{{ $packageOption->id }}" @selected((string) old('package_id') === (string) $packageOption->id)>{{ $packageOption->name }} - {{ str((string) $packageOption->package_kind)->headline() }} - Rp {{ number_format((float) ($packageOption->promo_price ?? $packageOption->price), 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    @error('package_id') <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300">{{ $message }}</span> @enderror
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Trainer Opsional</span>
                    <select name="trainer_id" class="admin-form-input mt-2">
                        <option value="">Tanpa trainer</option>
                        @foreach (collect($portal['paymentTrainers'] ?? []) as $trainerOption)
                            <option value="{{ $trainerOption->id }}" @selected((string) old('trainer_id') === (string) $trainerOption->id)>{{ $trainerOption->name }}</option>
                        @endforeach
                    </select>
                    @error('trainer_id') <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300">{{ $message }}</span> @enderror
                </label>
                <label class="xl:col-span-2">
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Catatan</span>
                    <input type="text" name="note" value="{{ old('note') }}" maxlength="500" class="admin-form-input mt-2" placeholder="Contoh: Pembayaran kasir shift pagi">
                    @error('note') <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300">{{ $message }}</span> @enderror
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
                    <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <p class="font-mono text-sm font-black text-zinc-950 dark:text-white">{{ $payment->payment_code }}</p>
                                <p class="mt-1 text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $payment->member?->user?->name ?? $payment->member?->member_code }} - Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <form method="POST" action="{{ route('admin.payments.approve', $payment) }}">
                                    @csrf
                                    <button type="submit" class="admin-button-primary w-full">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="reason" required maxlength="500" class="min-h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white" placeholder="Alasan">
                                    <button type="submit" class="admin-button-secondary">Tolak</button>
                                </form>
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
        <section class="admin-card mt-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <p class="admin-eyebrow">Filter Laporan</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Periode operasional</h2>
                    <p class="mt-2 admin-copy">Ringkasan dan export CSV memakai periode yang sama.</p>
                </div>
                <a href="{{ route('admin.reports.export', request()->only(['date_from', 'date_to'])) }}" class="admin-button-primary">Export CSV</a>
            </div>
            <form method="GET" action="{{ route('admin.reports') }}" class="mt-5 grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45 md:grid-cols-[1fr_1fr_auto] md:items-end">
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Dari Tanggal</span>
                    <input type="date" name="date_from" value="{{ $portal['filters']['date_from'] ?? now()->startOfMonth()->toDateString() }}" class="admin-form-input mt-2">
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Sampai Tanggal</span>
                    <input type="date" name="date_to" value="{{ $portal['filters']['date_to'] ?? now()->toDateString() }}" class="admin-form-input mt-2">
                </label>
                <button type="submit" class="admin-button-secondary">Terapkan</button>
            </form>
        </section>
    @endif

    @if ($page['key'] === 'audit-log')
        <section class="admin-card mt-6">
            <p class="admin-eyebrow">Filter Audit</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Jejak perubahan sistem</h2>
            <form method="GET" action="{{ route('admin.audit-log') }}" class="mt-5 grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end">
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Dari</span>
                    <input type="date" name="date_from" value="{{ $portal['filters']['date_from'] ?? now()->startOfMonth()->toDateString() }}" class="admin-form-input mt-2">
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Sampai</span>
                    <input type="date" name="date_to" value="{{ $portal['filters']['date_to'] ?? now()->toDateString() }}" class="admin-form-input mt-2">
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
                <button type="submit" class="admin-button-secondary">Terapkan</button>
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
            <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-5 grid gap-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45 lg:grid-cols-2">
                @csrf
                @method('PATCH')
                @foreach (($portal['editableSettings']['fields'] ?? []) as $field)
                    @php($value = old($field['name'], $portal['editableSettings']['values'][$field['name']] ?? ''))
                    <label class="{{ $field['type'] === 'textarea' ? 'lg:col-span-2' : '' }}">
                        <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ $field['label'] }}</span>
                        @if ($field['type'] === 'textarea')
                            <textarea name="{{ $field['name'] }}" rows="3" class="admin-form-input mt-2">{{ $value }}</textarea>
                        @else
                            <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" value="{{ $value }}" class="admin-form-input mt-2">
                        @endif
                        @error($field['name']) <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300">{{ $message }}</span> @enderror
                    </label>
                @endforeach
                <div class="lg:col-span-2">
                    <button type="submit" class="admin-button-primary">Simpan Pengaturan</button>
                </div>
            </form>
        </section>
    @endif

    @if ($page['key'] === 'booking')
        <section class="admin-card mt-6">
            <p class="admin-eyebrow">Tindakan Booking</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Konfirmasi kelas hari ini</h2>
            <form method="POST" action="{{ route('admin.booking.store') }}" class="mt-5 grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_12rem_auto] lg:items-end">
                @csrf
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Member</span>
                    <select name="member_id" class="admin-form-input mt-2" required>
                        <option value="">Pilih member</option>
                        @foreach (collect($portal['bookingMembers'] ?? []) as $memberOption)
                            <option value="{{ $memberOption->id }}" @selected((string) old('member_id') === (string) $memberOption->id)>{{ $memberOption->user?->name ?? $memberOption->member_code }} - {{ $memberOption->member_code }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Jadwal</span>
                    <select name="schedule_id" class="admin-form-input mt-2" required>
                        <option value="">Pilih jadwal</option>
                        @foreach (collect($portal['bookingSchedules'] ?? []) as $scheduleOption)
                            <option value="{{ $scheduleOption->id }}" @selected((string) old('schedule_id') === (string) $scheduleOption->id)>{{ $scheduleOption->gymClass?->name }} - {{ [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'][(int) $scheduleOption->day_of_week] ?? '-' }} {{ substr((string) $scheduleOption->start_time, 0, 5) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Tanggal</span>
                    <input type="date" name="session_date" value="{{ old('session_date', now()->toDateString()) }}" class="admin-form-input mt-2" required>
                </label>
                <button type="submit" class="admin-button-primary">Tambah Booking</button>
            </form>
            <div class="mt-5 grid gap-3">
                @forelse (collect($portal['todayBookings'] ?? [])->whereNotIn('status', ['cancelled', 'canceled']) as $enrollment)
                    <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <p class="font-black text-zinc-950 dark:text-white">{{ $enrollment->member?->user?->name ?? $enrollment->member?->member_code }}</p>
                                <p class="mt-1 text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $enrollment->schedule?->gymClass?->name }} - {{ substr((string) $enrollment->schedule?->start_time, 0, 5) }} - {{ str((string) $enrollment->status)->headline() }}</p>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <form method="POST" action="{{ route('admin.booking.confirm', $enrollment) }}">
                                    @csrf
                                    <button type="submit" class="admin-button-primary w-full">Konfirmasi</button>
                                </form>
                                <form method="POST" action="{{ route('admin.booking.cancel', $enrollment) }}">
                                    @csrf
                                    <button type="submit" class="admin-button-secondary w-full">Batalkan</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="admin-soft-panel">Tidak ada booking kelas hari ini.</div>
                @endforelse
            </div>
        </section>
    @endif
