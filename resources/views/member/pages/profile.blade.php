@php
    $genderLabel = match ((string) $member->gender) {
        'male' => 'Laki-laki',
        'female' => 'Perempuan',
        default => filled($member->gender) ? str((string) $member->gender)->headline()->toString() : '-',
    };
    $membershipLabel = $activeMembership?->package?->name ?? 'Belum aktif';
    $profileFields = [
        $user->name,
        $user->email,
        $user->phone,
        $user->avatar,
        $member->gender,
        $member->birth_date,
        $member->address,
        $member->emergency_contact,
    ];
    if ($member->is_student) {
        $profileFields[] = $member->student_proof_path;
    }
    $filledFields = collect($profileFields)->filter(fn ($value) => filled($value))->count();
    $completionPercent = (int) round(($filledFields / count($profileFields)) * 100);
    $identityRows = [
        ['label' => 'Nama Lengkap', 'value' => $user->name],
        ['label' => 'Alamat Email', 'value' => $user->email],
        ['label' => 'No. WhatsApp', 'value' => $user->phone ?? '-'],
        ['label' => 'Kode Member', 'value' => $member->member_code, 'mono' => true],
    ];
    $profileRows = [
        ['label' => 'Gender', 'value' => $genderLabel],
        ['label' => 'Tanggal Lahir', 'value' => $member->birth_date?->translatedFormat('d M Y') ?? '-'],
        ['label' => 'Alamat', 'value' => $member->address ?? '-'],
        ['label' => 'Kontak Darurat', 'value' => $member->emergency_contact ?? '-'],
    ];
    $supportRows = [
        ['label' => 'Kategori Member', 'value' => $member->is_student ? 'Mahasiswa' : 'Umum'],
        [
            'label' => 'Bukti Mahasiswa',
            'value' => $member->is_student ? (filled($member->student_proof_path) ? 'Sudah diunggah' : 'Belum diunggah') : '-',
            'url' => filled($member->student_proof_path) ? route('member.profile.student-proof') : null,
        ],
    ];
@endphp

<div class="mt-6 grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
    <section class="member-card-strong relative isolate overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/70 to-transparent" aria-hidden="true"></div>
        <div class="relative flex min-w-0 flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start">
                <x-member-avatar :user="$user" class="h-20 w-20 rounded-lg text-3xl shadow-[0_18px_44px_rgba(254,172,24,0.18)]" aria-hidden="true" />
                <div class="min-w-0">
                    <p class="member-eyebrow">Ringkasan Akun</p>
                    <h3 class="mt-2 break-words text-2xl type-title leading-tight text-zinc-950 dark:text-zinc-100 sm:text-3xl">{{ $user->name }}</h3>
                    <p class="mt-3 break-words text-sm type-control text-zinc-600 dark:text-zinc-300">{{ $user->email }}</p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="member-status-pill member-status-success">{{ $statusLabel }}</span>
                    </div>
                </div>
            </div>

            <div class="grid min-w-0 gap-3 sm:w-full sm:grid-cols-3 xl:w-[26rem]">
                <div class="member-soft-panel min-w-0">
                    <p class="text-[0.72rem] type-control uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">Membership</p>
                    <p class="mt-2 break-words text-sm type-control text-zinc-950 dark:text-zinc-100">{{ $membershipLabel }}</p>
                </div>
                <div class="member-soft-panel min-w-0">
                    <p class="text-[0.72rem] type-control uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">Bergabung</p>
                    <p class="mt-2 text-sm type-control text-zinc-950 dark:text-zinc-100">{{ $member->joined_at?->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div class="member-soft-panel min-w-0">
                    <p class="text-[0.72rem] type-control uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">Kelengkapan</p>
                    <p class="mt-2 text-sm type-control text-zinc-700 dark:text-gold-400">{{ $completionPercent }}%</p>
                </div>
            </div>
        </div>
    </section>

    <aside class="member-card">
        <p class="member-eyebrow">Aksi Profil</p>
        <h3 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Kelola data akun</h3>
        <p class="mt-3 member-copy">Ubah data hanya saat perlu agar informasi layanan tetap akurat.</p>
        <a href="{{ route('member.profile.edit') }}" class="member-button-primary mt-5 w-full">Edit Profil</a>
        <a href="{{ route('profile.edit') }}" class="member-button-secondary mt-3 w-full">Keamanan Akun</a>
    </aside>
</div>

<div class="mt-6 grid min-w-0 gap-6 lg:grid-cols-2">
    <section class="member-card min-w-0">
        <p class="member-eyebrow">Identitas</p>
        <h3 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Data utama</h3>
        <dl class="mt-5">
            @foreach ($identityRows as $row)
                <div class="member-data-row">
                    <dt class="min-w-0 text-sm type-control text-zinc-500 dark:text-zinc-400">{{ $row['label'] }}</dt>
                    <dd class="min-w-0 max-w-full sm:max-w-[62%] break-words text-right text-sm type-control text-zinc-950 dark:text-zinc-100 {{ ($row['mono'] ?? false) ? 'font-mono' : '' }}">{{ $row['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    <section class="member-card min-w-0">
        <p class="member-eyebrow">Data Pribadi</p>
        <h3 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Kontak dan biodata</h3>
        <dl class="mt-5">
            @foreach ($profileRows as $row)
                <div class="member-data-row">
                    <dt class="min-w-0 text-sm type-control text-zinc-500 dark:text-zinc-400">{{ $row['label'] }}</dt>
                    <dd class="min-w-0 max-w-full sm:max-w-[62%] break-words text-right text-sm type-control text-zinc-950 dark:text-zinc-100">{{ $row['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
</div>

<section class="member-card mt-6 min-w-0">
    <div class="member-section-header">
        <div class="min-w-0">
            <p class="member-eyebrow">Data Pendukung</p>
            <h3 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Informasi layanan member</h3>
        </div>
        <a href="{{ route('member.membership') }}" class="member-button-secondary w-full sm:w-auto">Lihat Membership</a>
    </div>

    <div class="mt-5 grid min-w-0 gap-3 md:grid-cols-2">
        @foreach ($supportRows as $row)
            <article class="member-soft-panel min-w-0">
                <p class="text-xs type-control uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">{{ $row['label'] }}</p>
                <p class="mt-2 break-words text-lg type-control text-zinc-950 dark:text-zinc-100">{{ $row['value'] }}</p>
                @if ($row['url'] ?? null)
                    <a href="{{ $row['url'] }}" class="mt-3 inline-flex min-h-10 items-center rounded-md border border-gold-500/30 px-3 text-xs type-control text-gold-text underline-offset-2 hover:bg-gold-500/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-700 dark:focus-visible:outline-gold-400" target="_blank" rel="noopener">Lihat bukti</a>
                @endif
            </article>
        @endforeach
    </div>
</section>
