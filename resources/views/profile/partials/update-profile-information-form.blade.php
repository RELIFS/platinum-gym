<section>
    <header>
        <h2 class="text-lg type-control text-zinc-950 dark:text-zinc-100">
            Informasi Profil
        </h2>

        <p class="mt-1 text-sm type-compact leading-6 text-zinc-500 dark:text-zinc-400">
            Perbarui nama dan email akun Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm type-compact text-zinc-600 dark:text-zinc-300">
                        Email Anda belum diverifikasi.

                        <button form="send-verification" class="rounded-md text-sm type-control text-gold-text underline hover:text-gold-text-strong focus:outline-none focus:ring-2 focus:ring-gold-700/40 dark:focus:ring-gold-400/40">
                            Kirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-code-sent')
                        <p class="mt-2 text-sm type-control text-emerald-600 dark:text-emerald-400">
                            Kode verifikasi baru sudah dikirim ke email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Simpan</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm type-control text-emerald-600 dark:text-emerald-400"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
