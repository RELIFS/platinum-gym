<x-app-layout title="Keamanan Akun | Platinum Gym Padang">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs type-control uppercase tracking-[0.12em] text-gold-text">Akun Login</p>
                <h1 class="mt-1 text-2xl type-title text-zinc-950 dark:text-zinc-100">Keamanan Akun</h1>
            </div>
            @if ($user->hasRole('member') && $user->member)
                <a href="{{ route('member.profile') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 text-sm type-control text-zinc-700 transition hover:border-gold-600/60 hover:text-gold-text focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-200 dark:focus-visible:ring-gold-400/40">
                    Profil Member
                </a>
            @endif
        </div>
    </x-slot>

    <x-dashboard.page description="Kelola data login, verifikasi email, kata sandi, dan penghapusan akun Platinum Gym Padang.">
        <section class="relative isolate mb-6 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-950 p-5 text-zinc-100 shadow-[0_24px_70px_rgba(24,24,27,0.22)] sm:p-6">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/80 to-transparent" aria-hidden="true"></div>
            <div class="public-surface-grid absolute inset-0 opacity-[0.05]" aria-hidden="true"></div>
            <div class="relative grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-end">
                <div>
                    <p class="text-xs type-control uppercase tracking-[0.14em] text-gold-400">Credential Center</p>
                    <h2 class="mt-3 text-3xl type-title leading-tight text-zinc-100 sm:text-4xl">Akun login tetap aman dan rapi</h2>
                    <p class="mt-4 max-w-3xl text-sm type-compact leading-7 text-zinc-300">Gunakan halaman ini untuk mengubah email dan kata sandi. Data member seperti foto profil, alamat, kontak darurat, dan data mahasiswa dikelola dari portal member.</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/[0.06] p-4">
                    <p class="text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-500 dark:text-zinc-400">Akun Aktif</p>
                    <p class="mt-2 break-words text-lg type-control text-zinc-100">{{ $user->name }}</p>
                    <p class="mt-1 break-words text-sm type-control text-zinc-300">{{ $user->email }}</p>
                    <span class="mt-4 inline-flex rounded-full bg-gold-500 px-3 py-1 text-xs type-control text-zinc-950">{{ $user->hasVerifiedEmail() ? 'Email Terverifikasi' : 'Email Belum Terverifikasi' }}</span>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
            <div class="space-y-6">
                <x-dashboard.card>
                    @include('profile.partials.update-profile-information-form')
                </x-dashboard.card>

                <x-dashboard.card>
                    @include('profile.partials.update-password-form')
                </x-dashboard.card>
            </div>

            <aside class="space-y-6">
                <x-dashboard.card title="Akses cepat" description="Pilih area yang sesuai dengan kebutuhan akun.">
                    <div class="grid gap-3">
                        @if ($user->hasRole('member') && $user->member)
                            <a href="{{ route('member.profile') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-gold-500 px-4 text-sm type-emphasis text-zinc-950 transition hover:bg-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/50 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-gold-400/45 dark:focus-visible:ring-offset-zinc-950">Edit Profil Member</a>
                        @endif
                        <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 text-sm type-control text-zinc-700 transition hover:border-gold-600/60 hover:text-gold-text focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-200 dark:focus-visible:ring-gold-400/40">Kembali ke Dashboard</a>
                    </div>
                </x-dashboard.card>

                <x-dashboard.card>
                    @include('profile.partials.delete-user-form')
                </x-dashboard.card>
            </aside>
        </div>
    </x-dashboard.page>
</x-app-layout>
