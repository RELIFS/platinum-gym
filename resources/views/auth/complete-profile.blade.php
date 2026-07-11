<x-guest-layout>
    <div class="w-full">
        <div class="mb-8">
            <h1 class="mb-3 text-3xl type-title leading-tight text-zinc-950 dark:text-zinc-100">
                Lengkapi <span class="text-gold-display">Profil</span>
            </h1>
            <p class="auth-panel-copy">
                Tambahkan data wajib member agar akun Google Anda bisa memakai portal Platinum Gym Padang.
            </p>
        </div>

        <form method="POST" action="{{ route('member.profile.complete.store') }}" class="space-y-5">
            @csrf

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
                <input id="phone" class="auth-input" type="tel" name="phone" value="{{ old('phone', Auth::user()->phone) }}" required autocomplete="tel" placeholder="08xxxxxxxxxx" maxlength="20" inputmode="tel" data-phone-feedback-input aria-describedby="phone-feedback" @error('phone') aria-invalid="true" @enderror>
                <p id="phone-feedback" class="mt-1.5 hidden text-xs type-compact text-red-600 dark:text-red-400" data-phone-feedback>Gunakan format nomor 08xxxxxxxxxx.</p>
                <x-input-error :messages="$errors->get('phone')" class="auth-error" />
            </div>

            <x-auth.terms-checkbox />

            <button type="submit" class="auth-button-primary">
                Simpan Profil Member
            </button>
        </form>
    </div>
</x-guest-layout>
