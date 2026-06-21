<x-guest-layout>
    <div class="w-full">
        <div class="mb-8">
            <h1 class="mb-3 text-3xl font-extrabold leading-tight text-zinc-950 dark:text-white">
                Daftar <span class="text-gold-500">Membership</span>
            </h1>
            <p class="auth-panel-copy">
                Buat akun member dan mulai perjalanan latihan Anda bersama Platinum Gym Padang.
            </p>
        </div>

        @include('auth.partials.google-button', [
            'label' => 'Daftar dengan Google',
            'divider' => 'atau daftar manual',
        ])

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="auth-label">Nama Lengkap</label>
                <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama lengkap sesuai identitas" @error('name') aria-invalid="true" @enderror>
                <x-input-error :messages="$errors->get('name')" class="auth-error" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-birth-date-selects />

                <div>
                    <label for="gender" class="auth-label">Jenis Kelamin</label>
                    <select id="gender" name="gender" class="auth-input" required @error('gender') aria-invalid="true" @enderror>
                        <option value="" @selected(old('gender') === null)>Pilih jenis kelamin</option>
                        <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                        <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="auth-error" />
                </div>
            </div>

            <div>
                <label for="phone" class="auth-label">No. WhatsApp</label>
                <input id="phone" class="auth-input" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="08xxxxxxxxxx" maxlength="20" inputmode="tel" data-phone-feedback-input aria-describedby="phone-feedback" @error('phone') aria-invalid="true" @enderror>
                <p id="phone-feedback" class="mt-1.5 hidden text-xs font-medium text-red-600 dark:text-red-400" data-phone-feedback>Gunakan format nomor 08xxxxxxxxxx.</p>
                <x-input-error :messages="$errors->get('phone')" class="auth-error" />
            </div>

            <div>
                <label for="email" class="auth-label">Alamat Email</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@email.com" @error('email') aria-invalid="true" @enderror>
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
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

            <div>
                <label for="terms" class="flex cursor-pointer items-start gap-3 text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">
                    <input id="terms" type="checkbox" name="terms" value="1" @checked(old('terms')) class="mt-0.5 rounded border-zinc-300 bg-white text-gold-500 shadow-sm focus:ring-gold-500 dark:border-zinc-700 dark:bg-zinc-950" required @error('terms') aria-invalid="true" @enderror>
                    <span>
                        Saya menyetujui <a href="{{ route('legal.terms') }}" class="auth-link">Syarat &amp; Ketentuan</a> dan <a href="{{ route('legal.privacy') }}" class="auth-link">Kebijakan Privasi</a> Platinum Gym Padang.
                    </span>
                </label>
                <x-input-error :messages="$errors->get('terms')" class="auth-error" />
            </div>

            <button type="submit" class="auth-button-primary">
                Daftar Sekarang
            </button>
        </form>

        <p class="mt-8 text-center font-medium text-zinc-500 dark:text-zinc-400">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="auth-link">Masuk di sini</a>
        </p>
    </div>
</x-guest-layout>
