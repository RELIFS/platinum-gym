@php
    $qrTokenIsActive = (bool) ($portal['qrTokenIsActive'] ?? false);
    $qrSvg = $qrTokenIsActive && $qrToken ? app(\App\Support\QrSvgRenderer::class)->render($qrToken->token, 220) : '';
    $qrStatusClass = $qrTokenIsActive ? 'member-status-success' : 'member-status-warning';
@endphp

<div class="mt-6 grid gap-6 lg:grid-cols-[22rem_minmax(0,1fr)]">
    <section class="member-card-strong text-center">
        <div class="mx-auto grid aspect-square max-w-64 place-items-center rounded-lg bg-white p-6 text-zinc-950 shadow-[0_24px_70px_rgba(0,0,0,0.32)]" aria-label="QR member Platinum Gym">
            @if ($qrTokenIsActive && filled($qrSvg))
                <div class="h-52 w-52 overflow-hidden rounded-md text-zinc-950 [&_svg]:h-full [&_svg]:w-full" aria-hidden="true">
                    {!! $qrSvg !!}
                </div>
            @else
                @include('member.partials.icon', ['name' => 'qr', 'class' => 'h-40 w-40'])
            @endif
        </div>
        <p class="mt-5 text-xs font-black uppercase tracking-[0.2em] text-gold-400">{{ $member->member_code }}</p>
        <h3 class="mt-2 text-2xl font-black text-white">{{ $user->name }}</h3>
        <p class="mt-3 text-sm font-semibold leading-6 text-zinc-300">{{ $qrTokenIsActive ? 'QR aktif dan dapat dipindai admin untuk check-in.' : 'QR member aktif otomatis setelah membership berhasil dibayar.' }}</p>
    </section>
    <section class="member-card">
        <p class="member-eyebrow">Status QR</p>
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Kartu digital check-in</h3>
        <div class="member-soft-panel mt-5">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Status</dt><dd><span class="member-status-pill {{ $qrStatusClass }}">{{ $qrStatusLabel }}</span></dd></div>
                <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Berlaku Sampai</dt><dd class="text-right font-black text-zinc-950 dark:text-white">{{ $qrToken?->expires_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Terakhir Dipakai</dt><dd class="text-right font-black text-zinc-950 dark:text-white">{{ $qrToken?->last_used_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd></div>
            </dl>
        </div>
        <p class="member-unavailable-note mt-5">Tunjukkan QR ini ke petugas saat check-in. Token mentah tidak ditampilkan pada halaman member.</p>
    </section>
</div>
