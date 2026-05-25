<x-guest-layout>
    <div class="w-full">
        <div class="mb-8">
            <h2 class="mb-3 text-3xl font-black tracking-tight text-slate-950 dark:text-white">
                Daftar <span class="text-gold-500">Membership</span>
            </h2>
            <p class="font-medium leading-relaxed text-slate-500 dark:text-slate-400">
                Mulai perjalanan fitness Anda bersama Platinum Gym hari ini.
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="auth-label">Nama Lengkap</label>
                <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama lengkap sesuai identitas">
                <x-input-error :messages="$errors->get('name')" class="auth-error" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="birth_date" class="auth-label">Tanggal Lahir</label>
                    <input id="birth_date" class="auth-input" type="date" name="birth_date" value="{{ old('birth_date') }}" required max="{{ now()->subDay()->toDateString() }}">
                    <x-input-error :messages="$errors->get('birth_date')" class="auth-error" />
                </div>

                <div>
                    <label for="gender" class="auth-label">Jenis Kelamin</label>
                    <select id="gender" name="gender" class="auth-input" required>
                        <option value="" @selected(old('gender') === null)>Pilih jenis kelamin</option>
                        <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                        <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="auth-error" />
                </div>
            </div>

            <div>
                <label for="phone" class="auth-label">No. WhatsApp</label>
                <input id="phone" class="auth-input" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="08xxxxxxxxxx" maxlength="20">
                <x-input-error :messages="$errors->get('phone')" class="auth-error" />
            </div>

            <div>
                <label for="email" class="auth-label">Alamat Email</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@email.com">
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div>
                <label for="password" class="auth-label">Kata Sandi</label>
                <input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter">
                <x-input-error :messages="$errors->get('password')" class="auth-error" />
            </div>

            <div>
                <label for="password_confirmation" class="auth-label">Konfirmasi Kata Sandi</label>
                <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi">
                <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
            </div>

            <div>
                <label for="terms" class="flex cursor-pointer items-start gap-3 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                    <input id="terms" type="checkbox" name="terms" value="1" @checked(old('terms')) class="mt-0.5 rounded border-slate-300 text-gold-500 shadow-sm focus:ring-gold-500 dark:border-slate-700 dark:bg-slate-900" required>
                    <span>
                        Saya menyetujui <a href="#" class="auth-link">Syarat &amp; Ketentuan</a> dan <a href="#" class="auth-link">Kebijakan Privasi</a> Platinum Gym Padang.
                    </span>
                </label>
                <x-input-error :messages="$errors->get('terms')" class="auth-error" />
            </div>

            <button type="submit" class="auth-button-primary">
                Daftar Sekarang
            </button>
        </form>

        <p class="mt-8 text-center font-medium text-slate-500 dark:text-slate-400">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="auth-link">Masuk di sini</a>
        </p>
    </div>
</x-guest-layout>
