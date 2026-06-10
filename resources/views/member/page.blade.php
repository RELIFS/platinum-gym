@php
    $user = $portal['user'];
    $member = $portal['member'];
    $activeMembership = $portal['activeMembership'];
    $latestMembership = $portal['latestMembership'];
    $activePackageSessions = $portal['activePackageSessions'];
    $payments = $portal['payments'];
    $upcomingEnrollments = $portal['upcomingEnrollments'];
    $recentEnrollments = $portal['recentEnrollments'];
    $qrToken = $portal['qrToken'];
    $packages = $portal['packages'];
    $classSchedules = $portal['classSchedules'];
    $notifications = $portal['notifications'];
    $dayLabels = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    $statusLabel = match ((string) $member->status) {
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'suspended' => 'Ditangguhkan',
        default => str((string) $member->status)->headline()->toString(),
    };
@endphp

<x-member-layout :portal="$portal" :title="$page['title']">
    <section class="member-card-strong relative isolate overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/70 to-transparent" aria-hidden="true"></div>
        <div class="public-surface-grid absolute inset-0 opacity-10" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-gold-400">Member Portal</p>
                <h2 class="mt-3 text-3xl font-black leading-tight tracking-tight text-white sm:text-4xl">{{ $page['title'] }}</h2>
                <p class="mt-4 text-sm font-medium leading-7 text-zinc-300">{{ $page['description'] }}</p>
            </div>
            <a href="{{ route('member.dashboard') }}" class="member-button-primary">Dashboard</a>
        </div>
    </section>

    @switch($page['key'])
        @case('profil')
            <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <section class="member-card">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                        <div class="grid h-20 w-20 shrink-0 place-items-center rounded-lg bg-gold-500 text-3xl font-black text-zinc-950 shadow-[0_18px_44px_rgba(254,172,24,0.28)]">
                            {{ str($user->name)->substr(0, 1)->upper() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="member-eyebrow">Identitas</p>
                            <h3 class="mt-2 break-words text-2xl font-black text-zinc-950 dark:text-white">{{ $user->name }}</h3>
                            <p class="mt-2 break-words text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="member-soft-panel">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Kode Member</p>
                                    <p class="mt-2 break-words font-mono text-lg font-black text-zinc-950 dark:text-white">{{ $member->member_code }}</p>
                                </div>
                                <div class="member-soft-panel">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Status</p>
                                    <p class="mt-2 text-lg font-black text-emerald-700 dark:text-emerald-400">{{ $statusLabel }}</p>
                                </div>
                                <div class="member-soft-panel">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">No. WhatsApp</p>
                                    <p class="mt-2 break-words text-lg font-black text-zinc-950 dark:text-white">{{ $user->phone ?? '-' }}</p>
                                </div>
                                <div class="member-soft-panel">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Bergabung</p>
                                    <p class="mt-2 text-lg font-black text-zinc-950 dark:text-white">{{ $member->joined_at?->translatedFormat('d M Y') ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="member-card">
                    <p class="member-eyebrow">Profil Akun</p>
                    <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Kelola data login</h3>
                    <p class="mt-3 member-copy">Nama, email, nomor WhatsApp, dan kata sandi dikelola melalui halaman profil akun.</p>
                    <a href="{{ route('profile.edit') }}" class="member-button-primary mt-5 w-full">Edit Profil Akun</a>
                </aside>
            </div>
            @break

        @case('membership')
            <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)]">
                <section class="member-card">
                    <p class="member-eyebrow">Paket Aktif</p>
                    <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Status membership</h3>
                    @if ($activeMembership)
                        <div class="mt-5 rounded-lg border border-emerald-500/25 bg-emerald-500/10 p-5">
                            <span class="member-status-pill bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">Aktif</span>
                            <h4 class="mt-4 text-2xl font-black text-zinc-950 dark:text-white">{{ $activeMembership->package?->name ?? $activeMembership->code }}</h4>
                            <p class="mt-2 member-copy">{{ $activeMembership->start_date?->translatedFormat('d M Y') }} sampai {{ $activeMembership->end_date?->translatedFormat('d M Y') }}.</p>
                            <p class="mt-4 text-2xl font-black text-gold-600 dark:text-gold-400">Rp {{ number_format((float) $activeMembership->price, 0, ',', '.') }}</p>
                        </div>
                    @else
                        <div class="member-soft-panel mt-5">
                            <h4 class="font-black text-zinc-950 dark:text-white">Belum ada membership aktif</h4>
                            <p class="mt-2 member-copy">Pilih paket dari daftar layanan saat pembelian digital sudah aktif.</p>
                            <button type="button" disabled class="member-button-disabled mt-4 w-full" aria-disabled="true">Pembelian segera aktif</button>
                        </div>
                    @endif

                    @if ($activePackageSessions->isNotEmpty())
                        <div class="mt-5 grid gap-3">
                            @foreach ($activePackageSessions as $session)
                                <article class="member-soft-panel">
                                    <p class="font-black text-zinc-950 dark:text-white">{{ $session->package?->name ?? $session->code }}</p>
                                    <p class="mt-2 text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $session->remaining_sessions }} dari {{ $session->total_sessions }} sesi tersisa</p>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="member-card">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="member-eyebrow">Katalog Paket</p>
                            <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Pilihan tersedia</h3>
                        </div>
                        <a href="{{ route('public.services') }}" class="member-button-secondary">Lihat Publik</a>
                    </div>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @forelse ($packages as $package)
                            <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-gold-600 dark:text-gold-400">{{ str((string) $package->package_kind)->headline() }}</p>
                                <h4 class="mt-2 break-words font-black text-zinc-950 dark:text-white">{{ $package->name }}</h4>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $package->description ?? 'Paket Platinum Gym Padang.' }}</p>
                                <p class="mt-4 text-xl font-black text-zinc-950 dark:text-white">Rp {{ number_format((float) ($package->promo_price ?? $package->price), 0, ',', '.') }}</p>
                                <button type="button" disabled class="member-button-disabled mt-4 w-full" aria-disabled="true">Belum aktif</button>
                            </article>
                        @empty
                            <div class="member-soft-panel md:col-span-2">Paket belum tersedia.</div>
                        @endforelse
                    </div>
                </section>
            </div>
            @break

        @case('booking-kelas')
            <section class="member-card mt-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="member-eyebrow">Jadwal</p>
                        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Kelas aktif</h3>
                    </div>
                    <a href="{{ route('public.classes') }}" class="member-button-secondary">Jadwal Publik</a>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($classSchedules as $schedule)
                        @php($capacity = $schedule->capacity ?? $schedule->gymClass?->capacity ?? 0)
                        <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                            <span class="member-status-pill bg-gold-500/15 text-gold-700 dark:text-gold-300">{{ $dayLabels[$schedule->day_of_week] ?? 'Jadwal' }}</span>
                            <h4 class="mt-4 break-words text-lg font-black text-zinc-950 dark:text-white">{{ $schedule->gymClass?->name ?? 'Kelas Platinum Gym' }}</h4>
                            <dl class="mt-4 space-y-2 text-sm">
                                <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Waktu</dt><dd class="font-black text-zinc-950 dark:text-white">{{ substr((string) $schedule->start_time, 0, 5) }} - {{ substr((string) $schedule->end_time, 0, 5) }}</dd></div>
                                <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Coach</dt><dd class="text-right font-black text-zinc-950 dark:text-white">{{ $schedule->trainer?->name ?? '-' }}</dd></div>
                                <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Kuota</dt><dd class="font-black text-zinc-950 dark:text-white">{{ $schedule->booked_count }}/{{ $capacity }}</dd></div>
                            </dl>
                            <button type="button" disabled class="member-button-disabled mt-5 w-full" aria-disabled="true">Booking segera aktif</button>
                        </article>
                    @empty
                        <div class="member-soft-panel md:col-span-2 xl:col-span-3">Jadwal kelas belum tersedia.</div>
                    @endforelse
                </div>
            </section>
            @break

        @case('riwayat-booking')
            <section class="member-card mt-6">
                <p class="member-eyebrow">Booking</p>
                <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Riwayat kelas</h3>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @forelse ($recentEnrollments as $enrollment)
                        <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h4 class="break-words font-black text-zinc-950 dark:text-white">{{ $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym' }}</h4>
                                    <p class="mt-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $enrollment->session_date?->translatedFormat('l, d M Y') }} - {{ substr((string) $enrollment->schedule?->start_time, 0, 5) }}</p>
                                </div>
                                <span class="member-status-pill bg-zinc-100 text-zinc-700 dark:bg-white/[0.07] dark:text-zinc-300">{{ str((string) $enrollment->status)->headline() }}</span>
                            </div>
                        </article>
                    @empty
                        <div class="member-soft-panel md:col-span-2">
                            <h4 class="font-black text-zinc-950 dark:text-white">Belum ada riwayat booking</h4>
                            <p class="mt-2 member-copy">Riwayat kelas akan tampil setelah booking tercatat.</p>
                        </div>
                    @endforelse
                </div>
            </section>
            @break

        @case('transaksi')
            <section class="member-card mt-6">
                <p class="member-eyebrow">Transaksi</p>
                <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Riwayat pembayaran</h3>

                <div class="mt-5 space-y-3 md:hidden">
                    @forelse ($payments as $payment)
                        <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-mono text-sm font-black text-zinc-950 dark:text-white">{{ $payment->payment_code }}</p>
                                    <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $payment->created_at?->translatedFormat('d M Y H:i') }}</p>
                                </div>
                                <span class="member-status-pill bg-zinc-100 text-zinc-700 dark:bg-white/[0.07] dark:text-zinc-300">{{ str((string) $payment->status)->headline() }}</span>
                            </div>
                            <p class="mt-4 text-xl font-black text-gold-600 dark:text-gold-400">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                        </article>
                    @empty
                        <div class="member-soft-panel">Belum ada transaksi.</div>
                    @endforelse
                </div>

                <div class="mt-5 hidden overflow-x-auto rounded-lg border border-zinc-200 dark:border-white/10 md:block">
                    <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-white/10">
                        <thead class="bg-zinc-50 text-xs uppercase tracking-[0.14em] text-zinc-500 dark:bg-white/[0.04] dark:text-zinc-400">
                            <tr>
                                <th class="px-5 py-4 font-black">Kode</th>
                                <th class="px-5 py-4 font-black">Tanggal</th>
                                <th class="px-5 py-4 font-black">Metode</th>
                                <th class="px-5 py-4 font-black">Jumlah</th>
                                <th class="px-5 py-4 font-black">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                            @forelse ($payments as $payment)
                                <tr class="bg-white dark:bg-zinc-950/35">
                                    <td class="px-5 py-4 font-mono font-black text-zinc-950 dark:text-white">{{ $payment->payment_code }}</td>
                                    <td class="px-5 py-4 font-semibold text-zinc-600 dark:text-zinc-300">{{ $payment->created_at?->translatedFormat('d M Y') }}</td>
                                    <td class="px-5 py-4 font-semibold text-zinc-600 dark:text-zinc-300">{{ str((string) $payment->method)->headline() }}</td>
                                    <td class="px-5 py-4 font-black text-zinc-950 dark:text-white">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4"><span class="member-status-pill bg-zinc-100 text-zinc-700 dark:bg-white/[0.07] dark:text-zinc-300">{{ str((string) $payment->status)->headline() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center font-semibold text-zinc-500">Belum ada transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            @break

        @case('qr')
            <div class="mt-6 grid gap-6 lg:grid-cols-[22rem_minmax(0,1fr)]">
                <section class="member-card-strong text-center">
                    <div class="mx-auto grid aspect-square max-w-64 place-items-center rounded-lg bg-white p-8 text-zinc-950 shadow-[0_24px_70px_rgba(0,0,0,0.32)]">
                        @include('member.partials.icon', ['name' => 'qr', 'class' => 'h-40 w-40'])
                    </div>
                    <p class="mt-5 text-xs font-black uppercase tracking-[0.2em] text-gold-400">{{ $member->member_code }}</p>
                    <h3 class="mt-2 text-2xl font-black text-white">{{ $user->name }}</h3>
                </section>
                <section class="member-card">
                    <p class="member-eyebrow">Status QR</p>
                    <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Kartu check-in digital</h3>
                    <div class="member-soft-panel mt-5">
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Token</dt><dd class="font-black text-zinc-950 dark:text-white">{{ $qrToken && ! $qrToken->is_revoked ? 'Aktif' : 'Belum diterbitkan' }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Kedaluwarsa</dt><dd class="text-right font-black text-zinc-950 dark:text-white">{{ $qrToken?->expires_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Terakhir Dipakai</dt><dd class="text-right font-black text-zinc-950 dark:text-white">{{ $qrToken?->last_used_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd></div>
                        </dl>
                    </div>
                    <button type="button" disabled class="member-button-disabled mt-5 w-full" aria-disabled="true">Regenerate segera aktif</button>
                </section>
            </div>
            @break

        @case('notifikasi')
            <section class="member-card mt-6">
                <p class="member-eyebrow">Notifikasi</p>
                <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Pemberitahuan member</h3>
                <div class="mt-5 space-y-3">
                    @forelse ($notifications as $notification)
                        @php($title = data_get($notification->data, 'title', class_basename($notification->type)))
                        <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                            <div class="flex items-start gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-gold-500/10 text-gold-600 dark:text-gold-400">
                                    @include('member.partials.icon', ['name' => 'bell', 'class' => 'h-5 w-5'])
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-black text-zinc-950 dark:text-white">{{ $title }}</p>
                                    <p class="mt-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ data_get($notification->data, 'body', 'Notifikasi akun member.') }}</p>
                                    <p class="mt-2 text-xs font-bold uppercase tracking-[0.14em] text-zinc-400">{{ $notification->created_at?->translatedFormat('d M Y H:i') }}</p>
                                </div>
                                @if (is_null($notification->read_at))
                                    <span class="h-2.5 w-2.5 rounded-full bg-gold-500" aria-hidden="true"></span>
                                    <span class="sr-only">Belum dibaca</span>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="member-soft-panel text-center">
                            @include('member.partials.icon', ['name' => 'bell', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
                            <p class="mt-3 font-black text-zinc-950 dark:text-white">Belum ada notifikasi</p>
                            <p class="mt-1 member-copy">Pemberitahuan membership, booking, dan pembayaran akan tampil di sini.</p>
                        </div>
                    @endforelse
                </div>
            </section>
            @break

        @case('ai-assistant')
            <section class="member-card mt-6">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-center">
                    <div>
                        <p class="member-eyebrow">AI Assistant</p>
                        <h3 class="mt-2 text-2xl font-black text-zinc-950 dark:text-white">Asisten member Platinum Gym</h3>
                        <p class="mt-4 member-copy">Asisten akan membantu pertanyaan layanan, membership, jadwal kelas, dan progres latihan saat modul percakapan member diaktifkan.</p>
                        <button type="button" disabled class="member-button-disabled mt-5" aria-disabled="true">Chat segera aktif</button>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-950 p-6 text-white shadow-[0_24px_70px_rgba(0,0,0,0.24)] dark:border-white/10">
                        <div class="grid h-16 w-16 place-items-center rounded-lg bg-gold-500 text-zinc-950">
                            @include('member.partials.icon', ['name' => 'spark', 'class' => 'h-8 w-8'])
                        </div>
                        <p class="mt-5 text-sm font-semibold leading-7 text-zinc-300">Mode aman: asisten belum mengakses data transaksi atau data sensitif member.</p>
                    </div>
                </div>
            </section>
            @break
    @endswitch
</x-member-layout>
