<x-guest-layout>
    <div class="w-full">
        <a href="{{ route('register') }}" class="mb-8 inline-flex min-h-11 items-center gap-2 rounded-lg px-1 py-2 text-sm font-semibold text-zinc-500 transition hover:text-gold-600 dark:text-zinc-400 dark:hover:text-gold-500">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Ganti email pendaftaran
        </a>

        <div class="mb-6">
            <div class="mb-5 inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-gold-500/30 bg-gold-500/10 text-gold-600 shadow-sm shadow-gold-500/10 dark:text-gold-500">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4.75 6.75H19.25V17.25H4.75V6.75Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                    <path d="M5.25 7.25L12 12.75L18.75 7.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <h2 class="mb-3 text-3xl font-extrabold leading-tight text-zinc-950 dark:text-white">
                Verifikasi <span class="text-gold-500">Email</span>
            </h2>
            <p class="auth-panel-copy">
                Masukkan kode 6 digit yang dikirim ke <span class="font-bold text-zinc-800 dark:text-zinc-100">{{ $maskedEmail }}</span>. Kode berlaku 10 menit.
            </p>
        </div>

        @if (session('status') == 'verification-code-sent')
            <div class="mb-5 flex gap-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-medium text-emerald-700 dark:text-emerald-300" role="status">
                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M5 10.5L8.25 13.75L15 6.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div>
                    <p class="font-bold">Kode baru sudah dikirim.</p>
                    <p class="mt-1 leading-6">Gunakan kode terbaru yang masuk ke email Anda.</p>
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-zinc-200 bg-white/85 p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/70">
            <form method="POST" action="{{ route('verification.code.verify') }}" class="mb-6">
                @csrf

                <label for="verification-code" class="auth-label">Masukkan kode verifikasi</label>
                <input
                    id="verification-code"
                    name="code"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    autocomplete="one-time-code"
                    value="{{ old('code') }}"
                    class="auth-input mt-2 text-center text-2xl font-black tracking-[0.32em] sm:text-3xl"
                    placeholder="000000"
                    aria-describedby="verification-code-help{{ $errors->has('code') ? ' verification-code-error' : '' }}"
                    @error('code') aria-invalid="true" @enderror
                    required
                >
                <p id="verification-code-help" class="mt-3 text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Jangan bagikan kode ini kepada siapa pun, termasuk pihak yang mengaku dari Platinum Gym.</p>
                @error('code')
                    <p id="verification-code-error" class="auth-error" role="alert">{{ $message }}</p>
                @enderror

                <button type="submit" class="auth-button-primary mt-4">
                    Aktifkan Akun
                </button>
            </form>

            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50/80 p-4 dark:border-zinc-700 dark:bg-zinc-950/45">
                <p class="text-sm font-semibold leading-relaxed text-zinc-600 dark:text-zinc-300">
                    Belum menerima kode? Cek Inbox, Spam, atau Promosi, lalu kirim ulang jika masih belum ada.
                </p>
                <p class="mt-2 text-xs font-medium leading-5 text-zinc-500 dark:text-zinc-400">
                    Kode terbaru akan menggantikan kode sebelumnya.
                </p>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <button type="submit" class="auth-button-secondary">
                        Kirim Ulang Kode
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
    </div>
</x-guest-layout>
