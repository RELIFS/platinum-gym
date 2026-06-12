<div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
    <section class="member-card">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
            <div class="grid h-20 w-20 shrink-0 place-items-center rounded-lg bg-gold-500 text-3xl font-black text-zinc-950 shadow-[0_18px_44px_rgba(254,172,24,0.28)]" aria-hidden="true">
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
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Kelola akun login</h3>
        <p class="mt-3 member-copy">Nama, email, nomor WhatsApp, dan kata sandi dikelola melalui halaman profil akun.</p>
        <a href="{{ route('profile.edit') }}" class="member-button-primary mt-5 w-full">Edit Akun Login</a>
    </aside>
</div>
