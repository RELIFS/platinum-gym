<x-admin-layout :portal="$portal" :navigation="$navigation" title="Keamanan Akun">
    <x-admin.page-header eyebrow="Akun Admin" title="Keamanan Akun Admin" description="Kelola informasi masuk admin: nama, email, dan kata sandi. Halaman ini terpisah dari pengaturan website.">
        <x-slot:actions>
            <a href="{{ route('admin.profile') }}" class="admin-button-secondary shrink-0">
                @include('admin.partials.icon', ['name' => 'arrow', 'class' => 'h-4 w-4 rotate-180'])
                Kembali ke Profil Admin
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6">
        @include('admin.partials.account-security-forms')
    </div>
</x-admin-layout>
