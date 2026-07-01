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

<section class="mt-6 admin-card" data-admin-profile-overview>
    <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] xl:items-start">
        <div class="min-w-0">
            <p class="admin-eyebrow">Profil Admin</p>
            <div class="flex flex-col min-w-0 gap-4 mt-4 sm:flex-row sm:items-center">
                <div class="grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-xl border border-gold-500/30 bg-gold-500 text-3xl font-black text-zinc-950 shadow-[0_18px_44px_rgba(254,172,24,0.18)]" aria-hidden="true">
                    @if ($adminAvatarUrl)
                        <img src="{{ $adminAvatarUrl }}" alt="" class="object-cover w-full h-full">
                    @else
                        {{ $adminInitial }}
                    @endif
                </div>
                <div class="min-w-0">
                    <h2 class="text-2xl font-black leading-tight break-words text-zinc-950 dark:text-white">{{ $adminName }}</h2>
                    <p class="mt-2 text-sm font-semibold break-words text-zinc-600 dark:text-zinc-300">{{ $admin?->email }}</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="admin-status-pill admin-status-info">{{ $adminRoleLabel }}</span>
                        <span class="admin-status-pill {{ $emailVerified ? 'admin-status-success' : 'admin-status-warning' }}">{{ $emailVerified ? 'Email terverifikasi' : 'Email belum diverifikasi' }}</span>
                    </div>
                    <p class="mt-3 text-sm font-semibold leading-6 text-zinc-500 dark:text-zinc-400">
                        Login terakhir: <span class="font-black text-zinc-700 dark:text-zinc-200">{{ $admin?->last_login_at?->translatedFormat('d M Y H:i') ?? 'Belum tercatat' }}</span>
                    </p>
                </div>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.profile-photo.update') }}"
            enctype="multipart/form-data"
            class="w-full max-w-full overflow-hidden admin-panel"
            data-admin-profile-photo-form
            x-data="{ submitting: false, avatarPreviewUrl: null, avatarPreviewActive: false, avatarObjectUrl: null, setAvatarPreview(event) { const file = event.target.files?.[0]; if (this.avatarObjectUrl) { URL.revokeObjectURL(this.avatarObjectUrl); this.avatarObjectUrl = null; } if (! file) { this.avatarPreviewUrl = null; this.avatarPreviewActive = false; return; } this.avatarObjectUrl = URL.createObjectURL(file); this.avatarPreviewUrl = this.avatarObjectUrl; this.avatarPreviewActive = true; } }"
            x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
        >
            @csrf
            @method('PATCH')
            <div class="flex flex-col min-w-0 gap-4 sm:flex-row sm:items-start">
                <div class="grid w-20 h-20 overflow-hidden text-2xl font-black bg-white border rounded-lg shrink-0 place-items-center border-zinc-200 text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" aria-hidden="true">
                    <template x-if="avatarPreviewUrl">
                        <img x-bind:src="avatarPreviewUrl" alt="" class="object-cover w-full h-full">
                    </template>
                    <template x-if="! avatarPreviewUrl">
                        @if ($adminAvatarUrl)
                            <img src="{{ $adminAvatarUrl }}" alt="" class="object-cover w-full h-full">
                        @else
                            <span>{{ $adminInitial }}</span>
                        @endif
                    </template>
                </div>
                <div class="flex-1 min-w-0 overflow-hidden">
                    <label for="admin_avatar" class="admin-field-label">Foto profil admin</label>
                    <input
                        id="admin_avatar"
                        name="avatar"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="admin-form-input admin-upload-field mt-2 text-sm file:max-w-full file:py-1.5"
                        aria-describedby="{{ $avatarHelpId }}{{ $errors->has('avatar') ? ' '.$avatarErrorId : '' }}"
                        @error('avatar') aria-invalid="true" @enderror
                        x-on:change="setAvatarPreview($event)"
                        required
                    >
                    <p id="{{ $avatarHelpId }}" class="mt-2 admin-field-help">Gunakan JPG, PNG, atau WebP maksimal 2 MB. Foto hanya tampil di Portal Admin.</p>
                    <p class="mt-2 text-xs font-black uppercase tracking-[0.12em] text-gold-700 dark:text-gold-400" x-show="avatarPreviewActive" x-cloak>Preview, belum disimpan</p>
                    @error('avatar')
                        <span id="{{ $avatarErrorId }}" class="mt-2 admin-field-error" role="alert">{{ $message }}</span>
                    @enderror
                    @if (session('status') === 'admin-photo-updated')
                        <p class="mt-2 text-sm font-bold text-emerald-700 dark:text-emerald-300" role="status">Foto profil admin tersimpan.</p>
                    @endif
                </div>
            </div>
            <button type="submit" class="w-full mt-4 admin-button-primary sm:w-auto" x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">
                Simpan Foto Profil
            </button>
        </form>
    </div>
</section>

<section class="grid gap-4 mt-6 lg:grid-cols-2">
    <article class="admin-card">
        <p class="admin-eyebrow">Keamanan Akun</p>
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Nama, email, dan kata sandi</h3>
        <p class="mt-2 admin-copy">Kelola informasi login admin di halaman keamanan akun yang terpisah dari pengaturan website.</p>
        <a href="{{ route('profile.edit') }}" class="w-full mt-5 admin-button-primary sm:w-auto">
            @include('admin.partials.icon', ['name' => 'shield', 'class' => 'h-4 w-4'])
            Kelola Keamanan Akun
        </a>
    </article>

    <article class="admin-card">
        <p class="admin-eyebrow">Akses & Audit</p>
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Peran admin dan jejak perubahan</h3>
        <p class="mt-2 admin-copy">Perubahan penting di portal admin dicatat di Audit Log. Gunakan halaman itu untuk meninjau aktivitas sistem.</p>
        <div class="flex flex-col gap-2 mt-5 sm:flex-row">
            <a href="{{ route('admin.audit-log') }}" class="admin-button-secondary">
                @include('admin.partials.icon', ['name' => 'clipboard-list', 'class' => 'h-4 w-4'])
                Buka Audit Log
            </a>
        </div>
    </article>
</section>
