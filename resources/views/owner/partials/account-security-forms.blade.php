@php
    /** @var \App\Models\User $user */
    $emailVerified = $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail
        ? $user->hasVerifiedEmail()
        : true;
@endphp

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<section class="owner-card">
    <header>
        <p class="owner-eyebrow">Foto Profil</p>
        <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Foto Profil Owner</h2>
        <p class="mt-2 owner-copy">Gunakan foto yang jelas agar identitas owner mudah dikenali di dashboard dan sidebar.</p>
    </header>

    <form method="post" action="{{ route('owner.profile-photo.update') }}" enctype="multipart/form-data" class="mt-5"
        x-data="{ submitting: false, avatarPreviewUrl: null, avatarPreviewActive: false, avatarObjectUrl: null, setAvatarPreview(event) { const file = event.target.files?.[0]; if (this.avatarObjectUrl) { URL.revokeObjectURL(this.avatarObjectUrl); this.avatarObjectUrl = null; } if (! file) { this.avatarPreviewUrl = null; this.avatarPreviewActive = false; return; } this.avatarObjectUrl = URL.createObjectURL(file); this.avatarPreviewUrl = this.avatarObjectUrl; this.avatarPreviewActive = true; } }"
        x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }">
        @csrf
        @method('patch')

        <div class="grid gap-5 sm:grid-cols-[8rem_minmax(0,1fr)] sm:items-center">
            <div class="mx-auto h-28 w-28 sm:mx-0 sm:h-32 sm:w-32">
                <template x-if="avatarPreviewUrl">
                    <span class="grid h-full w-full overflow-hidden rounded-full border border-gold-500/40 bg-gold-500">
                        <img x-bind:src="avatarPreviewUrl" alt="" class="h-full w-full object-cover">
                    </span>
                </template>
                <template x-if="! avatarPreviewUrl">
                    @include('owner.partials.avatar', ['owner' => $user, 'class' => 'h-full w-full text-4xl shadow-[0_18px_44px_rgba(254,172,24,0.18)]'])
                </template>
            </div>

            <div class="min-w-0">
                <label for="owner_avatar" class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Upload Foto</label>
                <input id="owner_avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                    class="owner-form-input mt-2 file:mr-3 file:rounded-md file:border-0 file:bg-gold-500 file:px-3 file:py-1.5 file:text-sm file:font-black file:text-zinc-950"
                    aria-invalid="{{ $errors->get('avatar') ? 'true' : 'false' }}"
                    @if ($errors->get('avatar')) aria-describedby="owner-avatar-helper owner-avatar-error" @else aria-describedby="owner-avatar-helper" @endif
                    x-on:change="setAvatarPreview($event)">
                <p id="owner-avatar-helper" class="mt-2 text-sm font-semibold text-zinc-500 dark:text-zinc-400">Gunakan foto JPG, PNG, atau WebP maksimal 2 MB.</p>
                @error('avatar')
                    <span id="owner-avatar-error" class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
                @enderror
                <p class="mt-2 text-xs font-black uppercase tracking-[0.12em] text-gold-700 dark:text-gold-400" x-show="avatarPreviewActive" x-cloak>Preview, belum disimpan</p>

                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button type="submit" class="owner-button-primary" x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">Simpan Foto</button>
                    @if (session('status') === 'owner-photo-updated')
                        <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">Foto profil owner berhasil diperbarui.</p>
                    @endif
                </div>
            </div>
        </div>
    </form>
</section>

<section class="owner-card mt-6">
    <header>
        <p class="owner-eyebrow">Identitas Login</p>
        <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Informasi Akun</h2>
        <p class="mt-2 owner-copy">Perbarui nama dan alamat email akun owner. Email digunakan untuk masuk dan menerima notifikasi penting.</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 grid gap-4 sm:max-w-2xl">
        @csrf
        @method('patch')

        <label class="block">
            <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Nama <span class="text-red-500" aria-hidden="true">*</span></span>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                class="owner-form-input mt-2"
                aria-invalid="{{ $errors->get('name') ? 'true' : 'false' }}"
                @if ($errors->get('name')) aria-describedby="name-error" @endif>
            @error('name')
                <span id="name-error" class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Email <span class="text-red-500" aria-hidden="true">*</span></span>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                class="owner-form-input mt-2"
                aria-invalid="{{ $errors->get('email') ? 'true' : 'false' }}"
                @if ($errors->get('email')) aria-describedby="email-error" @endif>
            @error('email')
                <span id="email-error" class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
            @enderror

            @if (! $emailVerified)
                <div class="mt-3 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-sm font-bold text-amber-800 dark:text-amber-200">
                    Email belum diverifikasi.
                    <button form="send-verification" class="ml-1 underline hover:no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/40">Kirim ulang email verifikasi.</button>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-bold text-emerald-700 dark:text-emerald-300">Link verifikasi baru sudah dikirim.</p>
                    @endif
                </div>
            @endif
        </label>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <button type="submit" class="owner-button-primary">Simpan Profil</button>
            @if (session('status') === 'profile-updated')
                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">Informasi akun tersimpan.</p>
            @endif
        </div>
    </form>
</section>

<section class="owner-card mt-6">
    <header>
        <p class="owner-eyebrow">Kata Sandi</p>
        <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Ubah Kata Sandi</h2>
        <p class="mt-2 owner-copy">Gunakan kata sandi yang kuat agar akses laporan bisnis tetap aman.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 grid gap-4 sm:max-w-2xl"
        x-data="{ show1: false, show2: false, show3: false }">
        @csrf
        @method('put')

        <label class="block">
            <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Kata Sandi Saat Ini <span class="text-red-500" aria-hidden="true">*</span></span>
            <div class="relative mt-2">
                <input id="update_password_current_password" name="current_password" x-bind:type="show1 ? 'text' : 'password'" autocomplete="current-password"
                    class="owner-form-input pr-12"
                    aria-invalid="{{ $errors->updatePassword->get('current_password') ? 'true' : 'false' }}">
                <button type="button" x-on:click="show1 = !show1" class="absolute inset-y-0 right-0 inline-flex h-full w-11 items-center justify-center text-zinc-500 hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:text-zinc-400" aria-label="Tampilkan/Sembunyikan kata sandi saat ini">
                    <span x-show="!show1">@include('admin.partials.icon', ['name' => 'eye', 'class' => 'h-5 w-5'])</span>
                    <span x-show="show1" x-cloak>@include('admin.partials.icon', ['name' => 'eye-off', 'class' => 'h-5 w-5'])</span>
                </button>
            </div>
            @error('current_password', 'updatePassword')
                <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Kata Sandi Baru <span class="text-red-500" aria-hidden="true">*</span></span>
            <div class="relative mt-2">
                <input id="update_password_password" name="password" x-bind:type="show2 ? 'text' : 'password'" autocomplete="new-password"
                    class="owner-form-input pr-12"
                    aria-invalid="{{ $errors->updatePassword->get('password') ? 'true' : 'false' }}">
                <button type="button" x-on:click="show2 = !show2" class="absolute inset-y-0 right-0 inline-flex h-full w-11 items-center justify-center text-zinc-500 hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:text-zinc-400" aria-label="Tampilkan/Sembunyikan kata sandi baru">
                    <span x-show="!show2">@include('admin.partials.icon', ['name' => 'eye', 'class' => 'h-5 w-5'])</span>
                    <span x-show="show2" x-cloak>@include('admin.partials.icon', ['name' => 'eye-off', 'class' => 'h-5 w-5'])</span>
                </button>
            </div>
            @error('password', 'updatePassword')
                <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <label class="block">
            <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Konfirmasi Kata Sandi Baru <span class="text-red-500" aria-hidden="true">*</span></span>
            <div class="relative mt-2">
                <input id="update_password_password_confirmation" name="password_confirmation" x-bind:type="show3 ? 'text' : 'password'" autocomplete="new-password"
                    class="owner-form-input pr-12"
                    aria-invalid="{{ $errors->updatePassword->get('password_confirmation') ? 'true' : 'false' }}">
                <button type="button" x-on:click="show3 = !show3" class="absolute inset-y-0 right-0 inline-flex h-full w-11 items-center justify-center text-zinc-500 hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:text-zinc-400" aria-label="Tampilkan/Sembunyikan konfirmasi kata sandi">
                    <span x-show="!show3">@include('admin.partials.icon', ['name' => 'eye', 'class' => 'h-5 w-5'])</span>
                    <span x-show="show3" x-cloak>@include('admin.partials.icon', ['name' => 'eye-off', 'class' => 'h-5 w-5'])</span>
                </button>
            </div>
            @error('password_confirmation', 'updatePassword')
                <span class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <button type="submit" class="owner-button-primary">Simpan Kata Sandi</button>
            @if (session('status') === 'password-updated')
                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">Kata sandi berhasil diperbarui.</p>
            @endif
        </div>
    </form>
</section>
