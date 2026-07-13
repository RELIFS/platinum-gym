@php
    $user = $member->user;
    $verificationLabel = match ((string) $member->student_verification_status) {
        'pending_review' => 'Menunggu review',
        'verified' => 'Terverifikasi',
        'failed' => 'Ditolak',
        'unverified', '' => 'Belum diverifikasi',
        default => str((string) $member->student_verification_status)->replace(['_', '-'], ' ')->headline()->toString(),
    };
    $verificationClass = match ((string) $member->student_verification_status) {
        'verified' => 'admin-status-success',
        'pending_review' => 'admin-status-warning',
        'failed' => 'admin-status-danger',
        default => 'admin-status-neutral',
    };
    $hasProof = $member->is_student && filled($member->student_proof_path);
@endphp

<x-admin-layout :portal="$portal" :navigation="$navigation" :title="$title">
    <x-admin.page-header title="Review Bukti Mahasiswa" description="Tinjau bukti KTM atau akun portal mahasiswa sebelum status mahasiswa dipakai di alur membership.">
        <x-slot:actions>
            <a href="{{ route('admin.members', ['q' => $member->member_code]) }}" class="admin-button-secondary">Kembali ke Anggota</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)] xl:items-start">
        <section class="admin-card">
            <p class="admin-eyebrow">Data Member</p>
            <div class="mt-4 flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start">
                <span class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-full bg-gold-500 text-xl type-control text-zinc-950" aria-hidden="true">
                    {{ str($user?->name ?? 'M')->substr(0, 1)->upper() }}
                </span>
                <div class="min-w-0">
                    <h2 class="break-words text-xl type-title text-zinc-950 dark:text-zinc-100">{{ $user?->name ?? '-' }}</h2>
                    <p class="mt-1 break-words text-sm type-control text-zinc-500 dark:text-zinc-400">{{ $user?->email ?? '-' }}</p>
                    <p class="mt-2 font-mono text-xs type-control uppercase tracking-[0.12em] text-zinc-700 dark:text-gold-400">{{ $member->member_code }}</p>
                </div>
            </div>

            <dl class="mt-6 grid gap-3 text-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <dt class="type-control text-zinc-500 dark:text-zinc-400">WhatsApp</dt>
                    <dd class="break-words text-right type-control text-zinc-950 dark:text-zinc-100">{{ $user?->phone ?: '-' }}</dd>
                </div>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <dt class="type-control text-zinc-500 dark:text-zinc-400">Kategori</dt>
                    <dd><span class="admin-status-pill admin-status-neutral">{{ $member->is_student ? 'Mahasiswa' : 'Umum' }}</span></dd>
                </div>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <dt class="type-control text-zinc-500 dark:text-zinc-400">Status verifikasi</dt>
                    <dd><span class="admin-status-pill {{ $verificationClass }}">{{ $verificationLabel }}</span></dd>
                </div>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <dt class="type-control text-zinc-500 dark:text-zinc-400">Waktu upload</dt>
                    <dd class="text-right type-control text-zinc-950 dark:text-zinc-100">{{ $member->student_proof_uploaded_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd>
                </div>
                <div class="min-w-0">
                    <dt class="type-control text-zinc-500 dark:text-zinc-400">Catatan terakhir</dt>
                    <dd class="mt-1 break-words type-control text-zinc-700 dark:text-zinc-200">{{ $member->student_verification_note ?: '-' }}</dd>
                </div>
            </dl>
        </section>

        <section class="admin-card">
            <p class="admin-eyebrow">Bukti Mahasiswa</p>
            <h2 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">KTM atau akun portal mahasiswa</h2>
            <p class="mt-2 admin-copy">Pastikan bukti sesuai dengan identitas member sebelum memilih setujui atau tolak.</p>

            @if ($hasProof)
                <div class="mx-auto mt-5 max-w-lg overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-white/10 dark:bg-zinc-900">
                    <img src="{{ route('admin.members.student-proof.show', $member) }}" alt="Bukti mahasiswa {{ $user?->name ?? $member->member_code }}" class="max-h-[20rem] w-full object-contain" loading="lazy">
                </div>
            @else
                <div class="admin-soft-panel mt-5 text-center">
                    @include('admin.partials.icon', ['name' => 'empty', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
                    <p class="mt-3 type-control text-zinc-950 dark:text-zinc-100">Bukti mahasiswa belum tersedia.</p>
                </div>
            @endif
        </section>
    </div>

    <section class="admin-card mt-6">
        <div class="grid min-w-0 gap-4 lg:grid-cols-2">
            <form method="POST" action="{{ route('admin.members.student-proof.approve', $member) }}" class="admin-panel lg:order-2" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }">
                @csrf
                <label class="admin-field">
                    <span class="admin-field-label">Catatan setujui</span>
                    <textarea name="note" rows="3" maxlength="500" class="admin-form-input" placeholder="Opsional, contoh: Bukti KTM sesuai.">{{ old('note') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('note')" />
                </label>
                <button type="submit" class="admin-button-primary mt-4 w-full justify-center" @disabled(! $hasProof)>Setujui</button>
            </form>

            <form method="POST" action="{{ route('admin.members.student-proof.reject', $member) }}" class="admin-panel lg:order-1" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }">
                @csrf
                <label class="admin-field">
                    <span class="admin-field-label">Catatan tolak</span>
                    <textarea name="note" rows="3" maxlength="500" class="admin-form-input" placeholder="Opsional, contoh: Foto tidak terbaca atau bukan bukti mahasiswa.">{{ old('note') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('note')" />
                </label>
                <button type="submit" class="admin-button-danger mt-4 w-full justify-center" @disabled(! $hasProof)>Tolak</button>
            </form>
        </div>
    </section>
</x-admin-layout>
