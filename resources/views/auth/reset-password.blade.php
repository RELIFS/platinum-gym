<x-guest-layout>
    <div class="w-full">
        <div class="mb-8">
            <h2 class="mb-3 text-3xl font-extrabold leading-tight text-zinc-950 dark:text-white">
                Reset <span class="text-gold-500">Kata Sandi</span>
            </h2>
            <p class="auth-panel-copy">
                Buat kata sandi baru untuk akun Platinum Gym Padang Anda.
            </p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="auth-label">Alamat Email</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="nama@email.com">
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            @include('auth.partials.password-field', [
                'id' => 'password',
                'name' => 'password',
                'label' => 'Kata Sandi Baru',
                'autocomplete' => 'new-password',
                'placeholder' => 'Minimal 8 karakter',
                'strength' => true,
            ])

            @include('auth.partials.password-field', [
                'id' => 'password_confirmation',
                'name' => 'password_confirmation',
                'label' => 'Konfirmasi Kata Sandi',
                'autocomplete' => 'new-password',
                'placeholder' => 'Ulangi kata sandi baru',
            ])

            <button type="submit" class="auth-button-primary">
                Simpan Kata Sandi Baru
            </button>
        </form>
    </div>
</x-guest-layout>
