<x-guest-layout>
    <div class="w-full">
        <div class="mb-10">
            <h2 class="mb-3 text-3xl font-black tracking-tight text-slate-950 dark:text-white">
                Selamat <span class="text-gold-500">Datang</span>
            </h2>
            <p class="font-medium leading-relaxed text-slate-500 dark:text-slate-400">
                Masuk untuk mengakses dasbor Platinum Gym Anda.
            </p>
        </div>

        <x-auth-session-status class="mb-5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm font-medium text-emerald-600 dark:text-emerald-400" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="auth-label">Alamat Email</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="andi@email.com">
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Kata Sandi</label>
                    @if (Route::has('password.request'))
                        <a class="auth-link text-sm" href="{{ route('password.request') }}">Lupa Kata Sandi?</a>
                    @endif
                </div>
                <input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password" placeholder="Minimal 8 karakter">
                <x-input-error :messages="$errors->get('password')" class="auth-error" />
            </div>

            <label for="remember_me" class="flex cursor-pointer items-center gap-3 text-sm font-medium text-slate-600 dark:text-slate-400">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-gold-500 shadow-sm focus:ring-gold-500 dark:border-slate-700 dark:bg-slate-900" name="remember">
                <span>Ingat saya</span>
            </label>

            <button type="submit" class="auth-button-primary">
                Masuk
            </button>
        </form>

        <p class="mt-10 text-center font-medium text-slate-500 dark:text-slate-400">
            Belum memiliki akun?
            <a href="{{ route('register') }}" class="auth-link">Daftar Membership</a>
        </p>
    </div>
</x-guest-layout>
