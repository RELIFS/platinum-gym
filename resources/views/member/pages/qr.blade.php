@php($qrTokenIsActive = (bool) ($portal['qrTokenIsActive'] ?? false))

<div class="mt-6 grid gap-6 lg:grid-cols-[22rem_minmax(0,1fr)]">
    <section class="member-card-strong text-center">
        <div class="mx-auto grid aspect-square max-w-64 place-items-center rounded-lg bg-white p-8 text-zinc-950 shadow-[0_24px_70px_rgba(0,0,0,0.32)]" aria-hidden="true">
            @include('member.partials.icon', ['name' => 'qr', 'class' => 'h-40 w-40'])
        </div>
        <p class="mt-5 text-xs font-black uppercase tracking-[0.2em] text-gold-400">{{ $member->member_code }}</p>
        <h3 class="mt-2 text-2xl font-black text-white">{{ $user->name }}</h3>
        <p class="mt-3 text-sm font-semibold leading-6 text-zinc-300">{{ $qrTokenIsActive ? 'Status QR aktif. Kode check-in akan ditampilkan saat layanan check-in digital dibuka.' : 'Kartu QR member akan siap digunakan setelah layanan check-in digital dibuka.' }}</p>
    </section>
    <section class="member-card">
        <p class="member-eyebrow">Status QR</p>
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Kartu check-in digital</h3>
        <div class="member-soft-panel mt-5">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Status</dt><dd class="font-black text-zinc-950 dark:text-white">{{ $qrStatusLabel }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Berlaku Sampai</dt><dd class="text-right font-black text-zinc-950 dark:text-white">{{ $qrToken?->expires_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="font-semibold text-zinc-500">Terakhir Dipakai</dt><dd class="text-right font-black text-zinc-950 dark:text-white">{{ $qrToken?->last_used_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd></div>
            </dl>
        </div>
        <p class="member-unavailable-note mt-5">Kode QR dibuat otomatis oleh sistem saat fitur check-in digital siap digunakan.</p>
    </section>
</div>
