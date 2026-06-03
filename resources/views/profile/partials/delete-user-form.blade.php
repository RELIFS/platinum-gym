<section class="space-y-6">
    <header>
        <h2 class="text-lg font-black text-zinc-950 dark:text-white">
            Hapus Akun
        </h2>

        <p class="mt-1 text-sm font-medium leading-6 text-zinc-500 dark:text-zinc-400">
            Akun yang dihapus tidak dapat dipulihkan. Pastikan keputusan ini sudah benar sebelum melanjutkan.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Hapus Akun</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-black text-zinc-950 dark:text-white">
                Hapus akun ini?
            </h2>

            <p class="mt-1 text-sm font-medium leading-6 text-zinc-500 dark:text-zinc-400">
                Masukkan password untuk mengonfirmasi penghapusan akun permanen.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Password"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Hapus Akun
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
