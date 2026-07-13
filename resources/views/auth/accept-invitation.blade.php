<x-guest-layout>
    <div class="w-full">
        <a href="{{ route('login') }}" class="mb-8 inline-flex items-center gap-2 rounded-lg px-1 py-2 text-sm type-control text-zinc-500 transition hover:text-gold-text dark:text-zinc-400">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Kembali ke login
        </a>

        <div class="mb-8">
            <div class="mb-5 inline-flex h-14 w-14 items-center justify-center rounded-lg border border-gold-600/30 bg-gold-500/10 text-gold-text dark:border-gold-400/25">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3.75 19.25 7.75V12.75C19.25 16.7 16.26 20.07 12 21.25C7.74 20.07 4.75 16.7 4.75 12.75V7.75L12 3.75Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                    <path d="M9 12.5 11 14.5 15.5 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <h2 class="mb-3 text-3xl type-title leading-tight text-zinc-950 dark:text-zinc-100">
                Aktivasi <span class="text-gold-display">Akun</span>
            </h2>
            <p class="auth-panel-copy">
                Undangan untuk {{ $invitation->user?->email }}. Buat kata sandi baru agar akun member Platinum Gym Padang aktif dan bisa digunakan.
            </p>
        </div>

        @error('token')
            <div class="mb-5 rounded-lg border border-red-500/30 bg-red-500/10 p-4 text-sm type-control text-red-700 dark:text-red-300" role="alert">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('account-invitations.store', $token) }}" class="space-y-5">
            @csrf

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

            <button type="submit" class="auth-button-primary">Aktifkan Akun</button>
        </form>
    </div>
</x-guest-layout>
