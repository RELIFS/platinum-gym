<x-owner-layout :portal="$portal" :navigation="$navigation" title="Keamanan Akun Owner">
    <section class="owner-page-header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="owner-eyebrow">Akun Owner</p>
                <h2 class="owner-title mt-2">Keamanan Akun Owner</h2>
                <p class="mt-3 owner-copy">Kelola nama, email, dan kata sandi akun owner.</p>
            </div>
            <a href="{{ route('owner.dashboard') }}" class="owner-button-secondary">
                @include('admin.partials.icon', ['name' => 'arrow', 'class' => 'h-4 w-4 rotate-180'])
                Kembali ke Dashboard
            </a>
        </div>
    </section>

    <div class="mt-6">
        @include('owner.partials.account-security-forms')
    </div>
</x-owner-layout>

