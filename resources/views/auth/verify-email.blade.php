<x-guest-layout>
    <div class="w-full">
        <a href="{{ route('register') }}" class="mb-8 inline-flex items-center gap-2 rounded-lg px-1 py-2 text-sm font-semibold text-zinc-500 transition hover:text-gold-600 dark:text-zinc-400 dark:hover:text-gold-500">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Ganti email pendaftaran
        </a>

        <div class="mb-8">
            <div class="mb-5 inline-flex h-14 w-14 items-center justify-center rounded-lg border border-gold-500/30 bg-gold-500/10 text-gold-600 dark:text-gold-500">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4.75 6.75H19.25V17.25H4.75V6.75Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                    <path d="M5.25 7.25L12 12.75L18.75 7.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <h2 class="mb-3 text-3xl font-extrabold leading-tight text-zinc-950 dark:text-white">
                Verifikasi <span class="text-gold-500">Email</span>
            </h2>
            <p class="auth-panel-copy">
                Kami sudah mengirim link verifikasi ke alamat email Anda. Klik link tersebut untuk mengaktifkan akun Platinum Gym Padang.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                Link verifikasi baru sudah dikirim. Periksa kotak masuk, spam, atau folder promosi email Anda.
            </div>
        @endif

        <div class="rounded-lg border border-zinc-200 bg-white/80 p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/70">
            <p class="mb-5 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                Tidak menerima email? Anda bisa mengirim ulang link verifikasi. Pastikan alamat email yang dipakai saat daftar sudah benar.
            </p>

            <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                @csrf

                <button type="submit" class="auth-button-primary">
                    Kirim Ulang Email Verifikasi
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="auth-button-secondary">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
