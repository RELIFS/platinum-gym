<x-admin-layout :portal="$portal" :navigation="$navigation" title="Keamanan Akun">
    <section class="admin-page-header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 max-w-3xl">
                <p class="admin-eyebrow">Akun Login</p>
                <h2 class="admin-title">Keamanan Akun Admin</h2>
                <p class="mt-3 admin-copy">Kelola informasi login admin: nama, email, dan kata sandi. Halaman ini terpisah dari pengaturan website.</p>
            </div>
            <a href="{{ route('admin.profile') }}" class="admin-button-secondary shrink-0">
                @include('admin.partials.icon', ['name' => 'arrow', 'class' => 'h-4 w-4 rotate-180'])
                Kembali ke Profil Admin
            </a>
        </div>
    </section>

    <div class="mt-6">
        @include('admin.partials.account-security-forms')
    </div>
</x-admin-layout>
