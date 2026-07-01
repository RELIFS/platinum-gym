@php
    $qrTokenIsActive = (bool) ($portal['qrTokenIsActive'] ?? false);
    $qrActiveForSession = $qrTokenIsActive && ($qrStatusLabel ?? null) === 'Aktif untuk sesi';
    $qrSvg = $qrTokenIsActive && $qrToken ? app(\App\Support\QrSvgRenderer::class)->render($qrToken->token, 220) : '';
    $qrStatusClass = $qrTokenIsActive ? 'member-status-success' : 'member-status-warning';
    $pendingPayment = collect($payments)->first(fn ($payment) => in_array((string) $payment->status, ['waiting_payment', 'pending', 'unpaid', 'waiting_confirmation'], true));
    $inactiveTitle = match ($qrStatusLabel) {
        'Dicabut' => 'QR dicabut',
        default => 'QR belum aktif',
    };
    $inactiveBody = match (true) {
        (bool) $pendingPayment => 'Selesaikan pembayaran yang masih menunggu agar QR member bisa digunakan kembali.',
        (bool) $qrToken => 'Aktifkan membership atau paket sesi Muaythai/Poundfit untuk menggunakan QR.',
        default => 'QR member diterbitkan otomatis setelah membership atau paket sesi Muaythai/Poundfit berhasil dibayar dan aktif.',
    };
    $inactiveCtaRoute = $pendingPayment ? route('member.transactions.show', $pendingPayment) : route('member.membership');
    $inactiveCtaLabel = $pendingPayment ? 'Lanjutkan Pembayaran' : 'Pilih Paket';
    $recentCheckInRows = collect($portal['recentCheckInRows'] ?? []);
@endphp

<div class="mx-auto mt-6 grid w-full max-w-6xl min-w-0 gap-6 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] lg:items-stretch" data-qr-member-layout>
    <section class="member-card-pass mx-auto w-full max-w-sm min-w-0 text-center lg:max-w-none flex flex-col" data-qr-member-visual>
        <div class="mx-auto grid w-full max-w-[18rem] flex-1 place-items-center rounded-lg border border-zinc-200 bg-zinc-50 p-5 text-zinc-950 shadow-[0_10px_28px_rgba(24,24,27,0.055)] dark:border-white/10 dark:bg-zinc-900 sm:max-w-xs sm:p-6 lg:max-w-sm" aria-label="QR member Platinum Gym">
            @if ($qrTokenIsActive && filled($qrSvg))
                <div class="grid aspect-square w-full max-w-[15.5rem] place-items-center rounded-md bg-white p-2 text-zinc-950 shadow-inner sm:max-w-[17rem] [&_svg]:h-full [&_svg]:w-full" aria-hidden="true">
                    {!! $qrSvg !!}
                </div>
            @else
                <div class="grid h-24 w-24 place-items-center rounded-full border border-amber-500/25 bg-amber-500/10 text-amber-700 dark:text-amber-300" aria-hidden="true">
                    @include('member.partials.icon', ['name' => 'lock', 'class' => 'h-10 w-10'])
                </div>
            @endif
        </div>
        <p class="mt-5 break-words text-xs font-black uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">{{ $member->member_code }}</p>
        <h3 class="mt-2 break-words text-2xl font-black text-zinc-950 dark:text-white">{{ $user->name }}</h3>
        <p class="mt-3 text-sm font-semibold leading-6 text-zinc-600 dark:text-zinc-300">{{ $qrTokenIsActive ? ($qrActiveForSession ? 'QR ini bisa digunakan admin untuk penggunaan sesi paket aktif Anda.' : 'QR ini tetap sama selama akun member aktif. Tunjukkan ke admin saat check-in atau penggunaan sesi.') : $inactiveBody }}</p>
        @if ($qrTokenIsActive)
            <a href="{{ route('member.qr.download') }}" class="member-button-primary mx-auto mt-5 w-full sm:max-w-xs">Download QR</a>
        @else
            <a href="{{ $inactiveCtaRoute }}" class="member-button-primary mx-auto mt-5 w-full sm:max-w-xs">{{ $inactiveCtaLabel }}</a>
        @endif
    </section>

    <section class="member-card flex min-w-0 flex-col" data-qr-member-status>
        <p class="member-eyebrow">Status QR</p>
        <h3 class="member-section-title">{{ $qrTokenIsActive ? 'Kartu digital check-in' : $inactiveTitle }}</h3>
        <div class="member-soft-panel mt-5 flex-1">
            <dl class="grid gap-3 text-sm">
                <div class="member-data-row"><dt class="font-semibold text-zinc-500 dark:text-zinc-400">Status</dt><dd><span class="member-status-pill {{ $qrStatusClass }}">{{ $qrStatusLabel }}</span></dd></div>
                <div class="member-data-row"><dt class="font-semibold text-zinc-500 dark:text-zinc-400">Berlaku Selama</dt><dd class="max-w-full break-words font-black text-zinc-950 dark:text-white">{{ $qrTokenIsActive ? ($qrActiveForSession ? 'Paket sesi aktif' : 'Membership aktif') : '-' }}</dd></div>
                <div class="member-data-row"><dt class="font-semibold text-zinc-500 dark:text-zinc-400">Terakhir Dipakai</dt><dd class="max-w-full break-words font-black text-zinc-950 dark:text-white">{{ $qrToken?->last_used_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd></div>
            </dl>
        </div>
        <p class="member-unavailable-note mt-5">{{ $qrTokenIsActive ? 'Tunjukkan QR ini ke petugas saat check-in membership atau penggunaan sesi. Token mentah tidak ditampilkan pada halaman member.' : 'Token mentah tidak ditampilkan. Admin hanya dapat scan QR setelah membership atau paket sesi aktif.' }}</p>
    </section>
</div>

<section class="member-card mt-6 min-w-0">
    <div class="member-section-header">
        <div class="min-w-0">
            <h3 class="member-section-title">Riwayat Check-in</h3>
        </div>
    </div>

    @if ($recentCheckInRows->isNotEmpty())
        <div class="member-table-wrap mt-5">
            <table class="min-w-[44rem] divide-y divide-zinc-200 text-left text-sm dark:divide-white/10 md:min-w-full">
                <caption class="sr-only">Riwayat check-in member</caption>
                <thead class="bg-zinc-50 text-xs uppercase tracking-[0.14em] text-zinc-500 dark:bg-white/[0.04] dark:text-zinc-400">
                    <tr>
                        <th scope="col" class="px-5 py-4 font-black">Tanggal</th>
                        <th scope="col" class="px-5 py-4 font-black">Jam</th>
                        <th scope="col" class="px-5 py-4 font-black">Paket</th>
                        <th scope="col" class="px-5 py-4 font-black">Status</th>
                        <th scope="col" class="px-5 py-4 text-right font-black">Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                    @foreach ($recentCheckInRows as $row)
                        <tr class="member-table-row">
                            <td class="px-5 py-4 font-black text-zinc-950 dark:text-white">{{ $row['date_label'] }}</td>
                            <td class="px-5 py-4 font-semibold text-zinc-600 dark:text-zinc-300">{{ $row['time_label'] }}</td>
                            <td class="max-w-xs px-5 py-4 font-bold text-zinc-700 dark:text-zinc-200"><span class="break-words">{{ $row['package_label'] }}</span></td>
                            <td class="px-5 py-4"><span class="member-status-pill {{ $row['status_class'] }}">{{ $row['status_label'] }}</span></td>
                            <td class="px-5 py-4 text-right font-black text-zinc-950 dark:text-white">{{ $row['remaining_label'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        @include('member.partials.empty-state', [
            'icon' => 'qr',
            'title' => 'Belum ada check-in',
            'body' => 'Riwayat masuk gym akan tampil setelah admin mengonfirmasi check-in.',
            'class' => 'mt-5',
        ])
    @endif
</section>
