<x-guest-layout>
    <div class="w-full">
        <div class="mb-8">
            <h1 class="mb-3 text-3xl type-title leading-tight text-zinc-950 dark:text-zinc-100">
                Daftar <span class="text-gold-display">Membership</span>
            </h1>
            <p class="auth-panel-copy">
                Buat akun member dan mulai perjalanan latihan Anda bersama Platinum Gym Padang.
            </p>
        </div>

        @include('auth.partials.google-button', [
            'label' => 'Daftar dengan Google',
            'divider' => 'atau daftar manual',
        ])

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-500/20 bg-red-500/10 p-4 text-sm type-compact leading-relaxed text-red-700 dark:text-red-300" role="alert" aria-live="assertive">
                Periksa kembali data yang ditandai di bawah ini.
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            @php
                $phoneDescribedBy = trim('phone-feedback '.($errors->has('phone') ? 'phone-error' : ''));
            @endphp

            <div>
                <label for="name" class="auth-label">Nama Lengkap</label>
                <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama lengkap sesuai identitas" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                <x-input-error id="name-error" :messages="$errors->get('name')" class="auth-error" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-birth-date-selects />

                <div>
                    <label for="gender" class="auth-label">Jenis Kelamin</label>
                    <select id="gender" name="gender" class="auth-input" required @error('gender') aria-invalid="true" aria-describedby="gender-error" @enderror>
                        <option value="" @selected(old('gender') === null)>Pilih jenis kelamin</option>
                        <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                        <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                    </select>
                    <x-input-error id="gender-error" :messages="$errors->get('gender')" class="auth-error" />
                </div>
            </div>

            <div>
                <label for="phone" class="auth-label">No. WhatsApp</label>
                <input id="phone" class="auth-input" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="08xxxxxxxxxx" maxlength="20" inputmode="tel" data-phone-feedback-input aria-describedby="{{ $phoneDescribedBy }}" @error('phone') aria-invalid="true" @enderror>
                <p id="phone-feedback" class="mt-1.5 hidden text-xs type-compact text-red-600 dark:text-red-400" data-phone-feedback>Gunakan format No. WhatsApp 08xxxxxxxxxx.</p>
                <x-input-error id="phone-error" :messages="$errors->get('phone')" class="auth-error" />
            </div>

            <div>
                <label for="email" class="auth-label">Alamat Email</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@email.com" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                <x-input-error id="email-error" :messages="$errors->get('email')" class="auth-error" />
            </div>

            @include('auth.partials.password-field', [
                'id' => 'password',
                'name' => 'password',
                'label' => 'Kata Sandi',
                'autocomplete' => 'new-password',
                'placeholder' => 'Minimal 8 karakter',
                'strength' => true,
            ])

            @include('auth.partials.password-field', [
                'id' => 'password_confirmation',
                'name' => 'password_confirmation',
                'label' => 'Konfirmasi Kata Sandi',
                'autocomplete' => 'new-password',
                'placeholder' => 'Ulangi kata sandi',
            ])

            <x-auth.terms-checkbox />

            <button type="submit" class="auth-button-primary">
                Daftar Sekarang
            </button>
        </form>

        <p class="mt-8 text-center type-compact text-zinc-500 dark:text-zinc-400">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="auth-link">Masuk di sini</a>
        </p>
    </div>
</x-guest-layout>
