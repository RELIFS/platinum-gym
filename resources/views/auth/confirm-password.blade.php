<x-guest-layout>
    <div class="w-full">
        <div class="mb-8">
            <h2 class="mb-3 text-3xl type-title leading-tight text-zinc-950 dark:text-zinc-100">
                Konfirmasi <span class="text-gold-display">Akses</span>
            </h2>
            <p class="auth-panel-copy">
                Masukkan kata sandi untuk melanjutkan ke area aman akun Anda.
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            @include('auth.partials.password-field', [
                'id' => 'password',
                'name' => 'password',
                'label' => 'Kata Sandi',
                'autocomplete' => 'current-password',
                'placeholder' => 'Masukkan kata sandi',
            ])

            <button type="submit" class="auth-button-primary">
                Konfirmasi
            </button>
        </form>
    </div>
</x-guest-layout>
