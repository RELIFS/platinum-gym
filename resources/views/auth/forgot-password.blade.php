<x-guest-layout>
    <div class="w-full">
        <div class="mb-8">
            <h2 class="mb-3 text-3xl font-extrabold leading-tight text-zinc-950 dark:text-white">
                Lupa <span class="text-gold-500">Kata Sandi</span>
            </h2>
            <p class="auth-panel-copy">
                Masukkan email akun Anda. Kami akan mengirim link untuk membuat kata sandi baru.
            </p>
        </div>

        <x-auth-session-status class="mb-5 rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm font-medium text-emerald-700 dark:text-emerald-400" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="auth-label">Alamat Email</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@email.com">
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <button type="submit" class="auth-button-primary">
                Kirim Link Reset
            </button>
        </form>

        <p class="mt-8 text-center font-medium text-zinc-500 dark:text-zinc-400">
            Ingat kata sandi?
            <a href="{{ route('login') }}" class="auth-link">Masuk di sini</a>
        </p>
    </div>
</x-guest-layout>
