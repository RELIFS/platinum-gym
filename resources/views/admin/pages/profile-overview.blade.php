@php
    $admin = $portal['admin'] ?? auth()->user();
    $adminName = (string) ($admin?->name ?? 'Admin');
    $adminInitial = mb_strtoupper(mb_substr($adminName, 0, 1));
    $adminRoleLabel = $admin?->getRoleNames()->implode(', ') ?: 'Admin';
    $adminAvatar = (string) ($admin?->avatar ?? '');
    $adminAvatarUrl = filled($adminAvatar)
        ? (str_starts_with($adminAvatar, 'storage/') ? asset($adminAvatar) : $adminAvatar)
        : null;
    $emailVerified = $admin instanceof \Illuminate\Contracts\Auth\MustVerifyEmail
        ? $admin->hasVerifiedEmail()
        : true;
    $avatarHelpId = 'admin-avatar-help';
    $avatarErrorId = 'admin-avatar-error';
@endphp

<section
    class="mt-6 admin-card"
    data-admin-profile-overview
    x-data="{ submitting: false, avatarPreviewUrl: null, avatarPreviewActive: false, avatarObjectUrl: null, fileName: '', setAvatarPreview(event) { const file = event.target.files?.[0]; this.fileName = file ? file.name : ''; if (this.avatarObjectUrl) { URL.revokeObjectURL(this.avatarObjectUrl); this.avatarObjectUrl = null; } if (! file) { this.avatarPreviewUrl = null; this.avatarPreviewActive = false; return; } this.avatarObjectUrl = URL.createObjectURL(file); this.avatarPreviewUrl = this.avatarObjectUrl; this.avatarPreviewActive = true; } }"
>
    <div class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.72fr)] lg:items-start">
        <div class="flex min-w-0 flex-col gap-5 sm:flex-row sm:items-center">
            <div class="grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-xl border border-gold-500/30 bg-gold-500 text-3xl type-emphasis text-zinc-950 shadow-[0_18px_44px_rgba(254,172,24,0.18)]" aria-hidden="true">
                <template x-if="avatarPreviewUrl">
                    <img x-bind:src="avatarPreviewUrl" alt="" class="h-full w-full object-cover">
                </template>
                <template x-if="! avatarPreviewUrl">
                    @if ($adminAvatarUrl)
                        <img src="{{ $adminAvatarUrl }}" alt="" class="h-full w-full object-cover">
                    @else
                        <span>{{ $adminInitial }}</span>
                    @endif
                </template>
            </div>
            <div class="min-w-0">
                <p class="admin-eyebrow">Profil Admin</p>
                <h2 class="mt-2 break-words text-2xl type-title leading-tight text-zinc-950 dark:text-zinc-100">{{ $adminName }}</h2>
                <p class="mt-2 break-words text-sm type-control text-zinc-600 dark:text-zinc-300">{{ $admin?->email }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="admin-status-pill admin-status-info">{{ $adminRoleLabel }}</span>
                    <span class="admin-status-pill {{ $emailVerified ? 'admin-status-success' : 'admin-status-warning' }}">{{ $emailVerified ? 'Email terverifikasi' : 'Email belum diverifikasi' }}</span>
                </div>
                <p class="mt-3 text-sm type-control leading-6 text-zinc-500 dark:text-zinc-400">
                    Login terakhir: <span class="type-control text-zinc-700 dark:text-zinc-200">{{ $admin?->last_login_at?->translatedFormat('d M Y H:i') ?? 'Belum tercatat' }}</span>
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.profile-photo.update') }}"
            enctype="multipart/form-data"
            class="admin-panel w-full max-w-full overflow-hidden"
            data-admin-profile-photo-form
            x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
        >
            @csrf
            @method('PATCH')
            <label for="admin_avatar" class="admin-field-label">Foto profil admin</label>
            <input
                id="admin_avatar"
                name="avatar"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="peer sr-only"
                aria-describedby="{{ $avatarHelpId }}{{ $errors->has('avatar') ? ' '.$avatarErrorId : '' }}"
                @error('avatar') aria-invalid="true" @enderror
                x-on:change="setAvatarPreview($event)"
                required
            >
            <div class="mt-3 flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center">
                <label for="admin_avatar" class="admin-button-secondary cursor-pointer peer-focus-visible:ring-2 peer-focus-visible:ring-gold-700/35 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-white dark:peer-focus-visible:ring-gold-400/35 dark:peer-focus-visible:ring-offset-zinc-950">
                    @include('admin.partials.icon', ['name' => 'user', 'class' => 'h-4 w-4'])
                    Pilih Foto
                </label>
                <span class="min-w-0 break-words text-sm type-control text-zinc-600 dark:text-zinc-300" x-text="fileName || 'Belum ada foto dipilih'">Belum ada foto dipilih</span>
            </div>
            <p id="{{ $avatarHelpId }}" class="mt-3 admin-field-help">Gunakan JPG, PNG, atau WebP maksimal 2 MB. Foto hanya tampil di Portal Admin.</p>
            <p class="mt-2 text-xs type-control uppercase tracking-[0.12em] text-zinc-700 dark:text-gold-400" x-show="avatarPreviewActive" x-cloak>Preview tampil di avatar utama, belum disimpan</p>
            @error('avatar')
                <span id="{{ $avatarErrorId }}" class="mt-2 admin-field-error" role="alert">{{ $message }}</span>
            @enderror
            @if (session('status') === 'admin-photo-updated')
                <p class="mt-2 text-sm type-control text-emerald-700 dark:text-emerald-300" role="status">Foto profil admin tersimpan.</p>
            @endif
            <button type="submit" class="mt-4 w-full admin-button-primary sm:w-auto" x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">
                Simpan Foto Profil
            </button>
        </form>
    </div>
</section>

<article class="mt-4 admin-card" data-admin-profile-security-card>
    <div class="flex min-w-0 flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
            <p class="admin-eyebrow">Keamanan Akun</p>
            <h3 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Nama, email, dan kata sandi</h3>
            <p class="mt-2 admin-copy">Kelola informasi login admin di halaman keamanan akun.</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="w-full admin-button-secondary md:w-auto">
            @include('admin.partials.icon', ['name' => 'shield', 'class' => 'h-4 w-4'])
            Kelola Keamanan Akun
        </a>
    </div>
</article>
