<x-guest-layout>
    <div class="w-full">
        <div class="mb-8">
            <h1 class="mb-3 text-3xl font-extrabold leading-tight text-zinc-950 dark:text-white">
                Selamat <span class="text-gold-500">Datang</span>
            </h1>
            <p class="auth-panel-copy">
                Masuk ke akun Platinum Gym Padang untuk melanjutkan ke area Anda.
            </p>
        </div>

        <x-auth-session-status class="mb-5 rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm font-medium text-emerald-700 dark:text-emerald-400" :status="session('status')" />

        @include('auth.partials.google-button', [
            'label' => 'Masuk dengan Google',
            'divider' => 'atau masuk dengan email',
        ])

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="auth-label">Alamat Email</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@email.com" @error('email') aria-invalid="true" @enderror>
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label for="password" class="auth-label mb-0">Kata Sandi</label>
                    @if (Route::has('password.request'))
                        <a class="auth-link text-sm" href="{{ route('password.request') }}">Lupa Kata Sandi?</a>
                    @endif
                </div>
                <div class="relative">
                    <input id="password" class="auth-input pr-12" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi" @error('password') aria-invalid="true" @enderror>
                    <button type="button" class="auth-password-toggle" data-password-toggle="password" aria-label="Tampilkan kata sandi" aria-pressed="false">
                        <svg data-eye-open class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M2.75 12C4.85 7.85 8.05 5.75 12 5.75C15.95 5.75 19.15 7.85 21.25 12C19.15 16.15 15.95 18.25 12 18.25C8.05 18.25 4.85 16.15 2.75 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                            <path d="M12 14.75C13.52 14.75 14.75 13.52 14.75 12C14.75 10.48 13.52 9.25 12 9.25C10.48 9.25 9.25 10.48 9.25 12C9.25 13.52 10.48 14.75 12 14.75Z" stroke="currentColor" stroke-width="1.8" />
                        </svg>
                        <svg data-eye-closed class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M3.25 3.25L20.75 20.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            <path d="M9.2 5.98C10.08 5.83 11.01 5.75 12 5.75C15.95 5.75 19.15 7.85 21.25 12C20.52 13.44 19.66 14.63 18.68 15.56" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M14.12 14.12C13.58 14.62 12.84 14.9 12 14.9C10.4 14.9 9.1 13.6 9.1 12C9.1 11.16 9.38 10.42 9.88 9.88" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            <path d="M6.52 7.55C5.08 8.57 3.83 10.05 2.75 12C4.85 16.15 8.05 18.25 12 18.25C13.32 18.25 14.55 18.01 15.68 17.52" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="auth-error" />
            </div>

            <label for="remember_me" class="flex cursor-pointer items-center gap-3 text-sm font-medium text-zinc-600 dark:text-zinc-400">
                <input id="remember_me" type="checkbox" class="rounded border-zinc-300 bg-white text-gold-500 shadow-sm focus:ring-gold-500 dark:border-zinc-700 dark:bg-zinc-950" name="remember">
                <span>Ingat saya</span>
            </label>

            <button type="submit" class="auth-button-primary">
                Masuk
            </button>
        </form>

        <p class="mt-8 text-center font-medium text-zinc-500 dark:text-zinc-400">
            Belum memiliki akun?
            <a href="{{ route('register') }}" class="auth-link">Daftar Membership</a>
        </p>
    </div>
</x-guest-layout>
