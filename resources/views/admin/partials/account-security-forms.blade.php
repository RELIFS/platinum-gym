@php
    /** @var \App\Models\User $user */
    $emailVerified = $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail
        ? $user->hasVerifiedEmail()
        : true;
    $adminName = (string) ($user->name ?? 'Admin');
    $adminInitial = mb_strtoupper(mb_substr($adminName, 0, 1));
    $adminRoleLabel = $user->getRoleNames()->implode(', ') ?: 'Admin';
    $adminAvatar = (string) ($user->avatar ?? '');
    $adminAvatarUrl = filled($adminAvatar)
        ? (str_starts_with($adminAvatar, 'storage/') ? asset($adminAvatar) : $adminAvatar)
        : null;
    $currentPasswordErrorId = 'update-password-current-error';
    $newPasswordErrorId = 'update-password-new-error';
    $confirmPasswordErrorId = 'update-password-confirm-error';
@endphp

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<section class="admin-card mb-6">
    <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-center gap-4">
            <div class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-xl border border-gold-500/25 bg-gold-500 text-2xl font-black text-zinc-950" aria-hidden="true">
                @if ($adminAvatarUrl)
                    <img src="{{ $adminAvatarUrl }}" alt="" class="h-full w-full object-cover">
                @else
                    {{ $adminInitial }}
                @endif
            </div>
            <div class="min-w-0">
                <p class="admin-eyebrow">Akun yang sedang dikelola</p>
                <h2 class="mt-1 break-words text-xl font-black text-zinc-950 dark:text-white">{{ $adminName }}</h2>
                <p class="mt-1 break-words text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="admin-status-pill admin-status-info">{{ $adminRoleLabel }}</span>
            <span class="admin-status-pill {{ $emailVerified ? 'admin-status-success' : 'admin-status-warning' }}">{{ $emailVerified ? 'Email terverifikasi' : 'Email belum diverifikasi' }}</span>
        </div>
    </div>
</section>

<section class="admin-card">
    <header>
        <p class="admin-eyebrow">Identitas Login</p>
        <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Informasi Akun</h2>
        <p class="mt-2 admin-copy">Perbarui nama dan alamat email akun admin. Email digunakan untuk masuk dan menerima notifikasi sistem.</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 grid gap-4 sm:max-w-2xl">
        @csrf
        @method('patch')

        <label class="admin-field">
            <span class="admin-field-label">Nama <span class="admin-required" aria-hidden="true">*</span></span>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                class="admin-form-input"
                aria-invalid="{{ $errors->get('name') ? 'true' : 'false' }}"
                @if ($errors->get('name')) aria-describedby="name-error" @endif>
            @error('name')
                <span id="name-error" class="admin-field-error" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <label class="admin-field">
            <span class="admin-field-label">Email <span class="admin-required" aria-hidden="true">*</span></span>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                class="admin-form-input"
                aria-invalid="{{ $errors->get('email') ? 'true' : 'false' }}"
                @if ($errors->get('email')) aria-describedby="email-error" @endif>
            @error('email')
                <span id="email-error" class="admin-field-error" role="alert">{{ $message }}</span>
            @enderror

            @if (! $emailVerified)
                <div class="mt-3 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-sm font-bold text-amber-800 dark:text-amber-200">
                    Email belum diverifikasi.
                    <button form="send-verification" class="ml-1 underline hover:no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/40">Kirim ulang email verifikasi.</button>
                    @if (session('status') === 'verification-code-sent')
                        <p class="mt-2 font-bold text-emerald-700 dark:text-emerald-300">Kode verifikasi baru sudah dikirim.</p>
                    @endif
                </div>
            @endif
        </label>

        <div class="admin-inline-action-field">
            <button type="submit" class="admin-button-primary">Simpan Profil</button>
            @if (session('status') === 'profile-updated')
                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">Informasi akun tersimpan.</p>
            @endif
        </div>
    </form>
</section>

<section class="admin-card mt-6">
    <header>
        <p class="admin-eyebrow">Kata Sandi</p>
        <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Ubah Kata Sandi</h2>
        <p class="mt-2 admin-copy">Gunakan kombinasi huruf besar, kecil, angka, dan simbol untuk menjaga keamanan akun admin.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 grid gap-4 sm:max-w-2xl"
        x-data="{ show1: false, show2: false, show3: false }">
        @csrf
        @method('put')

        <label class="admin-field">
            <span class="admin-field-label">Kata sandi saat ini <span class="admin-required" aria-hidden="true">*</span></span>
            <div class="relative">
                <input id="update_password_current_password" name="current_password" x-bind:type="show1 ? 'text' : 'password'" autocomplete="current-password"
                    class="admin-form-input pr-12"
                    aria-invalid="{{ $errors->updatePassword->get('current_password') ? 'true' : 'false' }}"
                    @if ($errors->updatePassword->get('current_password')) aria-describedby="{{ $currentPasswordErrorId }}" @endif>
                <button type="button" x-on:click="show1 = !show1" class="absolute inset-y-0 right-0 inline-flex h-full w-11 items-center justify-center text-zinc-500 hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:text-zinc-400" x-bind:aria-label="show1 ? 'Sembunyikan kata sandi saat ini' : 'Tampilkan kata sandi saat ini'" x-bind:aria-pressed="show1.toString()">
                    <span x-show="!show1">@include('admin.partials.icon', ['name' => 'eye', 'class' => 'h-5 w-5'])</span>
                    <span x-show="show1" x-cloak>@include('admin.partials.icon', ['name' => 'eye-off', 'class' => 'h-5 w-5'])</span>
                </button>
            </div>
            @error('current_password', 'updatePassword')
                <span id="{{ $currentPasswordErrorId }}" class="admin-field-error" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <label class="admin-field">
            <span class="admin-field-label">Kata sandi baru <span class="admin-required" aria-hidden="true">*</span></span>
            <div class="relative">
                <input id="update_password_password" name="password" x-bind:type="show2 ? 'text' : 'password'" autocomplete="new-password"
                    class="admin-form-input pr-12"
                    aria-invalid="{{ $errors->updatePassword->get('password') ? 'true' : 'false' }}"
                    @if ($errors->updatePassword->get('password')) aria-describedby="{{ $newPasswordErrorId }}" @endif>
                <button type="button" x-on:click="show2 = !show2" class="absolute inset-y-0 right-0 inline-flex h-full w-11 items-center justify-center text-zinc-500 hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:text-zinc-400" x-bind:aria-label="show2 ? 'Sembunyikan kata sandi baru' : 'Tampilkan kata sandi baru'" x-bind:aria-pressed="show2.toString()">
                    <span x-show="!show2">@include('admin.partials.icon', ['name' => 'eye', 'class' => 'h-5 w-5'])</span>
                    <span x-show="show2" x-cloak>@include('admin.partials.icon', ['name' => 'eye-off', 'class' => 'h-5 w-5'])</span>
                </button>
            </div>
            @error('password', 'updatePassword')
                <span id="{{ $newPasswordErrorId }}" class="admin-field-error" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <label class="admin-field">
            <span class="admin-field-label">Konfirmasi kata sandi baru <span class="admin-required" aria-hidden="true">*</span></span>
            <div class="relative">
                <input id="update_password_password_confirmation" name="password_confirmation" x-bind:type="show3 ? 'text' : 'password'" autocomplete="new-password"
                    class="admin-form-input pr-12"
                    aria-invalid="{{ $errors->updatePassword->get('password_confirmation') ? 'true' : 'false' }}"
                    @if ($errors->updatePassword->get('password_confirmation')) aria-describedby="{{ $confirmPasswordErrorId }}" @endif>
                <button type="button" x-on:click="show3 = !show3" class="absolute inset-y-0 right-0 inline-flex h-full w-11 items-center justify-center text-zinc-500 hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:text-zinc-400" x-bind:aria-label="show3 ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'" x-bind:aria-pressed="show3.toString()">
                    <span x-show="!show3">@include('admin.partials.icon', ['name' => 'eye', 'class' => 'h-5 w-5'])</span>
                    <span x-show="show3" x-cloak>@include('admin.partials.icon', ['name' => 'eye-off', 'class' => 'h-5 w-5'])</span>
                </button>
            </div>
            @error('password_confirmation', 'updatePassword')
                <span id="{{ $confirmPasswordErrorId }}" class="admin-field-error" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <div class="admin-inline-action-field">
            <button type="submit" class="admin-button-primary">Simpan Kata Sandi</button>
            @if (session('status') === 'password-updated')
                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">Kata sandi berhasil diperbarui.</p>
            @endif
        </div>
    </form>
</section>
