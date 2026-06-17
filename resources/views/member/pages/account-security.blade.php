<x-member-layout :portal="$portal" title="Keamanan Akun">
    <section class="member-card-strong relative isolate overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/70 to-transparent" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-gold-400">Akun Login</p>
                <h2 class="mt-3 text-3xl font-black leading-tight tracking-tight text-white sm:text-4xl">Keamanan Akun</h2>
                <p class="mt-4 text-sm font-medium leading-7 text-zinc-300">Kelola data login: nama, email, dan kata sandi. Untuk biodata fisik dan kontak darurat, gunakan halaman Profil Member.</p>
            </div>
            <a href="{{ route('member.profile') }}" class="member-button-primary">Profil Member</a>
        </div>
    </section>

    <div class="mt-6">
        @include('member.partials.account-security-forms')
    </div>
</x-member-layout>
