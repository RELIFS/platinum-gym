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

<section class="admin-card mt-6" data-admin-profile-overview>
    <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] xl:items-start">
        <div class="min-w-0">
            <p class="admin-eyebrow">Profil Admin</p>
            <div class="mt-4 flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center">
                <div class="grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-xl border border-gold-500/30 bg-gold-500 text-3xl font-black text-zinc-950 shadow-[0_18px_44px_rgba(254,172,24,0.18)]" aria-hidden="true">
                    @if ($adminAvatarUrl)
                        <img src="{{ $adminAvatarUrl }}" alt="" class="h-full w-full object-cover">
                    @else
                        {{ $adminInitial }}
                    @endif
                </div>
                <div class="min-w-0">
                    <h2 class="break-words text-2xl font-black leading-tight text-zinc-950 dark:text-white">{{ $adminName }}</h2>
                    <p class="mt-2 break-words text-sm font-semibold text-zinc-600 dark:text-zinc-300">{{ $admin?->email }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
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
            class="admin-panel w-full max-w-full overflow-hidden"
            data-admin-profile-photo-form
            x-data="{ submitting: false, avatarPreviewUrl: null, avatarPreviewActive: false, avatarObjectUrl: null, setAvatarPreview(event) { const file = event.target.files?.[0]; if (this.avatarObjectUrl) { URL.revokeObjectURL(this.avatarObjectUrl); this.avatarObjectUrl = null; } if (! file) { this.avatarPreviewUrl = null; this.avatarPreviewActive = false; return; } this.avatarObjectUrl = URL.createObjectURL(file); this.avatarPreviewUrl = this.avatarObjectUrl; this.avatarPreviewActive = true; } }"
            x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
        >
            @csrf
            @method('PATCH')
            <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start">
                <div class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-lg border border-zinc-200 bg-white text-2xl font-black text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" aria-hidden="true">
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
                <div class="min-w-0 flex-1 overflow-hidden">
                    <label for="admin_avatar" class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Foto profil admin</label>
                    <input
                        id="admin_avatar"
                        name="avatar"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="admin-form-input mt-2 max-w-full overflow-hidden text-sm file:mr-3 file:max-w-full file:rounded-md file:border-0 file:bg-gold-500 file:px-3 file:py-1.5 file:text-sm file:font-black file:text-zinc-950"
                        aria-describedby="{{ $avatarHelpId }}{{ $errors->has('avatar') ? ' '.$avatarErrorId : '' }}"
                        @error('avatar') aria-invalid="true" @enderror
                        x-on:change="setAvatarPreview($event)"
                        required
                    >
                    <p id="{{ $avatarHelpId }}" class="mt-2 text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Gunakan JPG, PNG, atau WebP maksimal 2 MB. Foto hanya tampil di Portal Admin.</p>
                    <p class="mt-2 text-xs font-black uppercase tracking-[0.12em] text-gold-700 dark:text-gold-400" x-show="avatarPreviewActive" x-cloak>Preview, belum disimpan</p>
                    @error('avatar')
                        <span id="{{ $avatarErrorId }}" class="mt-2 block text-sm font-bold text-red-600 dark:text-red-300" role="alert">{{ $message }}</span>
                    @enderror
                    @if (session('status') === 'admin-photo-updated')
                        <p class="mt-2 text-sm font-bold text-emerald-700 dark:text-emerald-300" role="status">Foto profil admin tersimpan.</p>
                    @endif
                </div>
            </div>
            <button type="submit" class="admin-button-primary mt-4 w-full sm:w-auto" x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">
                Simpan Foto Profil
            </button>
        </form>
    </div>
</section>

<section class="mt-6 grid gap-4 lg:grid-cols-2">
    <article class="admin-card">
        <p class="admin-eyebrow">Keamanan Akun</p>
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Nama, email, dan kata sandi</h3>
        <p class="mt-2 admin-copy">Kelola informasi login admin di halaman keamanan akun yang terpisah dari pengaturan website.</p>
        <a href="{{ route('profile.edit') }}" class="admin-button-primary mt-5 w-full sm:w-auto">
            @include('admin.partials.icon', ['name' => 'shield', 'class' => 'h-4 w-4'])
            Kelola Keamanan Akun
        </a>
    </article>

    <article class="admin-card">
        <p class="admin-eyebrow">Akses & Audit</p>
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Peran admin dan jejak perubahan</h3>
        <p class="mt-2 admin-copy">Perubahan penting di portal admin dicatat di Audit Log. Gunakan halaman itu untuk meninjau aktivitas sistem.</p>
        <div class="mt-5 flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('admin.audit-log') }}" class="admin-button-secondary">
                @include('admin.partials.icon', ['name' => 'clipboard-list', 'class' => 'h-4 w-4'])
                Buka Audit Log
            </a>
            <span class="admin-status-pill admin-status-neutral justify-center">{{ $adminRoleLabel }}</span>
        </div>
    </article>
</section>
