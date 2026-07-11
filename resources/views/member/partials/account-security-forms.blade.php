@php
    /** @var \App\Models\User $user */
    $emailVerified = $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail
        ? $user->hasVerifiedEmail()
        : true;
    $memberInputClasses = 'member-form-input';
@endphp

<form id="send-verification" method="post" action="{{ route('verification.send') }}" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }">
    @csrf
</form>

<section class="member-card">
    <header>
        <p class="member-eyebrow">Identitas Login</p>
        <h2 class="member-section-title">Informasi Akun</h2>
        <p class="mt-2 member-copy">Perbarui nama dan email akun Anda. Email digunakan untuk login dan menerima notifikasi pembayaran/booking.</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 grid gap-4 sm:max-w-2xl" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }">
        @csrf
        @method('patch')

        <label class="block">
            <span class="text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Nama <span class="text-red-500" aria-hidden="true">*</span></span>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                class="mt-2 {{ $memberInputClasses }}"
                aria-invalid="{{ $errors->get('name') ? 'true' : 'false' }}"
                @if ($errors->get('name')) aria-describedby="name-error" @endif>
            @error('name')
                <span id="name-error" class="mt-2 block text-sm type-control text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Email <span class="text-red-500" aria-hidden="true">*</span></span>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                class="mt-2 {{ $memberInputClasses }}"
                aria-invalid="{{ $errors->get('email') ? 'true' : 'false' }}"
                @if ($errors->get('email')) aria-describedby="email-error" @endif>
            @error('email')
                <span id="email-error" class="mt-2 block text-sm type-control text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
            @enderror

            @if (! $emailVerified)
                <div class="mt-3 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-sm type-control text-amber-800 dark:text-amber-200">
                    Email belum diverifikasi.
                    <button form="send-verification" class="ml-1 underline hover:no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/40">Kirim ulang email verifikasi.</button>
                    @if (session('status') === 'verification-code-sent')
                        <p class="mt-2 type-control text-emerald-700 dark:text-emerald-300">Kode verifikasi baru sudah dikirim.</p>
                    @endif
                </div>
            @endif
        </label>

        <div class="flex items-center gap-3">
            <button type="submit" class="member-button-primary" x-bind:disabled="submitting"><span x-show="! submitting">Simpan</span><span x-show="submitting">Menyimpan...</span></button>
            @if (session('status') === 'profile-updated')
                <p class="text-sm type-control text-emerald-700 dark:text-emerald-300">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>

<section class="member-card mt-6">
    <header>
        <p class="member-eyebrow">Kata Sandi</p>
        <h2 class="member-section-title">Ubah Password</h2>
        <p class="mt-2 member-copy">Gunakan kombinasi huruf besar, kecil, angka, dan simbol untuk menjaga keamanan akun Anda.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 grid gap-4 sm:max-w-2xl"
        x-data="{ show1: false, show2: false, show3: false, submitting: false }" x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }">
        @csrf
        @method('put')

        <label class="block">
            <span class="text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Password Saat Ini <span class="text-red-500" aria-hidden="true">*</span></span>
            <div class="relative mt-2">
                <input id="update_password_current_password" name="current_password" x-bind:type="show1 ? 'text' : 'password'" autocomplete="current-password"
                    class="{{ $memberInputClasses }} pr-12"
                    aria-invalid="{{ $errors->updatePassword->get('current_password') ? 'true' : 'false' }}">
                <button type="button" x-on:click="show1 = !show1" class="absolute inset-y-0 right-0 inline-flex h-full w-11 items-center justify-center text-zinc-500 hover:text-gold-text focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:text-zinc-400 dark:focus-visible:ring-gold-400/40" aria-label="Tampilkan/Sembunyikan kata sandi saat ini">
                    <svg x-show="!show1" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.75 12C4.85 7.85 8.05 5.75 12 5.75c3.95 0 7.15 2.1 9.25 6.25-2.1 4.15-5.3 6.25-9.25 6.25-3.95 0-7.15-2.1-9.25-6.25Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M12 14.75a2.75 2.75 0 1 0 0-5.5 2.75 2.75 0 0 0 0 5.5Z" stroke="currentColor" stroke-width="1.8" /></svg>
                    <svg x-show="show1" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3.5 3.5 20.5 20.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M9.4 6.18C10.23 6 11.1 5.9 12 5.9c3.95 0 7.15 2.1 9.25 6.25a13 13 0 0 1-2.32 3.2M6.6 7.7C4.98 8.74 3.66 10.2 2.75 12c2.1 4.15 5.3 6.25 9.25 6.25 1.05 0 2.05-.15 3-.45" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M9.9 9.9a2.75 2.75 0 0 0 3.86 3.86" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
                </button>
            </div>
            @error('current_password', 'updatePassword')
                <span class="mt-2 block text-sm type-control text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Password Baru <span class="text-red-500" aria-hidden="true">*</span></span>
            <div class="relative mt-2">
                <input id="update_password_password" name="password" x-bind:type="show2 ? 'text' : 'password'" autocomplete="new-password"
                    class="{{ $memberInputClasses }} pr-12"
                    aria-invalid="{{ $errors->updatePassword->get('password') ? 'true' : 'false' }}">
                <button type="button" x-on:click="show2 = !show2" class="absolute inset-y-0 right-0 inline-flex h-full w-11 items-center justify-center text-zinc-500 hover:text-gold-text focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:text-zinc-400 dark:focus-visible:ring-gold-400/40" aria-label="Tampilkan/Sembunyikan kata sandi baru">
                    <svg x-show="!show2" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.75 12C4.85 7.85 8.05 5.75 12 5.75c3.95 0 7.15 2.1 9.25 6.25-2.1 4.15-5.3 6.25-9.25 6.25-3.95 0-7.15-2.1-9.25-6.25Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M12 14.75a2.75 2.75 0 1 0 0-5.5 2.75 2.75 0 0 0 0 5.5Z" stroke="currentColor" stroke-width="1.8" /></svg>
                    <svg x-show="show2" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3.5 3.5 20.5 20.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M9.4 6.18C10.23 6 11.1 5.9 12 5.9c3.95 0 7.15 2.1 9.25 6.25a13 13 0 0 1-2.32 3.2M6.6 7.7C4.98 8.74 3.66 10.2 2.75 12c2.1 4.15 5.3 6.25 9.25 6.25 1.05 0 2.05-.15 3-.45" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M9.9 9.9a2.75 2.75 0 0 0 3.86 3.86" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
                </button>
            </div>
            @error('password', 'updatePassword')
                <span class="mt-2 block text-sm type-control text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">Konfirmasi Password Baru <span class="text-red-500" aria-hidden="true">*</span></span>
            <div class="relative mt-2">
                <input id="update_password_password_confirmation" name="password_confirmation" x-bind:type="show3 ? 'text' : 'password'" autocomplete="new-password"
                    class="{{ $memberInputClasses }} pr-12"
                    aria-invalid="{{ $errors->updatePassword->get('password_confirmation') ? 'true' : 'false' }}">
                <button type="button" x-on:click="show3 = !show3" class="absolute inset-y-0 right-0 inline-flex h-full w-11 items-center justify-center text-zinc-500 hover:text-gold-text focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:text-zinc-400 dark:focus-visible:ring-gold-400/40" aria-label="Tampilkan/Sembunyikan konfirmasi kata sandi">
                    <svg x-show="!show3" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.75 12C4.85 7.85 8.05 5.75 12 5.75c3.95 0 7.15 2.1 9.25 6.25-2.1 4.15-5.3 6.25-9.25 6.25-3.95 0-7.15-2.1-9.25-6.25Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M12 14.75a2.75 2.75 0 1 0 0-5.5 2.75 2.75 0 0 0 0 5.5Z" stroke="currentColor" stroke-width="1.8" /></svg>
                    <svg x-show="show3" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3.5 3.5 20.5 20.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M9.4 6.18C10.23 6 11.1 5.9 12 5.9c3.95 0 7.15 2.1 9.25 6.25a13 13 0 0 1-2.32 3.2M6.6 7.7C4.98 8.74 3.66 10.2 2.75 12c2.1 4.15 5.3 6.25 9.25 6.25 1.05 0 2.05-.15 3-.45" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M9.9 9.9a2.75 2.75 0 0 0 3.86 3.86" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
                </button>
            </div>
            @error('password_confirmation', 'updatePassword')
                <span class="mt-2 block text-sm type-control text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <div class="flex items-center gap-3">
            <button type="submit" class="member-button-primary" x-bind:disabled="submitting"><span x-show="! submitting">Simpan</span><span x-show="submitting">Menyimpan...</span></button>
            @if (session('status') === 'password-updated')
                <p class="text-sm type-control text-emerald-700 dark:text-emerald-300">Password berhasil diperbarui.</p>
            @endif
        </div>
    </form>
</section>
