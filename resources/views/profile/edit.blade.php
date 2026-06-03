<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Akun</p>
            <h1 class="mt-1 text-2xl font-black text-zinc-950 dark:text-white">Profil</h1>
        </div>
    </x-slot>

    <x-dashboard.page description="Kelola informasi akun dan keamanan login Platinum Gym Padang.">
        <div class="space-y-6">
            <x-dashboard.card>
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </x-dashboard.card>

            <x-dashboard.card>
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </x-dashboard.card>

            <x-dashboard.card>
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </x-dashboard.card>
        </div>
    </x-dashboard.page>
</x-app-layout>
