@php
    $module = $portal['modules'][$page['moduleKey']] ?? null;
    $checkInPreview = session('check_in_preview');
    $todayCheckIns = collect($portal['todayCheckIns'] ?? []);
@endphp

<x-admin-layout :portal="$portal" :navigation="$navigation" :title="$page['title']">
    @if ($page['key'] !== 'profile')
        <x-admin.page-header :title="$page['title']" :description="$page['description']">
            @if (! empty($page['secondaryCreateResource']) || ! empty($page['createResource']))
                <x-slot:actions>
                    @if (! empty($page['secondaryCreateResource']))
                        <a href="{{ route('admin.resources.create', $page['secondaryCreateResource']) }}" class="admin-button-secondary">{{ $page['secondaryCreateLabel'] ?? 'Tambah Data Pendukung' }}</a>
                    @endif
                    @if (! empty($page['createResource']))
                        <a href="{{ route('admin.resources.create', $page['createResource']) }}" class="admin-button-primary">Tambah {{ $page['title'] }}</a>
                    @endif
                </x-slot:actions>
            @endif
        </x-admin.page-header>
    @endif

    @if ($page['key'] === 'check-in')
        <section class="admin-card mt-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="admin-eyebrow">Pindai QR</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Check-in member dengan kamera</h2>
                    <p class="mt-2 admin-copy">Aktifkan kamera untuk memindai QR member secara langsung. Sistem akan menampilkan pratinjau sebelum admin mengonfirmasi tindakan.</p>
                </div>
            </div>

            <div id="admin-qr-camera-secure-banner" class="mt-4 hidden rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm font-bold text-amber-800 dark:text-amber-200" role="status">
                Kamera membutuhkan koneksi aman (HTTPS) atau localhost. Aktifkan SSL atau buka lewat localhost agar scanner QR dapat digunakan.
            </div>
            <div id="admin-qr-camera-support-banner" class="mt-4 hidden rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm font-bold text-amber-800 dark:text-amber-200" role="status">
                Browser ini tidak mendukung akses kamera. Gunakan browser lain yang mendukung kamera untuk memindai QR member.
            </div>

            <div class="mt-5 grid min-w-0 gap-4 xl:grid-cols-[minmax(0,26rem)_minmax(0,1fr)] xl:items-stretch">
                <div class="admin-panel p-3">
                    <div id="admin-qr-camera-region" class="aspect-square w-full overflow-hidden rounded-md bg-zinc-950" aria-label="Pratinjau kamera pindai QR"></div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" id="admin-qr-camera-start" class="admin-button-primary inline-flex items-center gap-2">
                            @include('admin.partials.icon', ['name' => 'qr', 'class' => 'h-4 w-4'])
                            Mulai Kamera
                        </button>
                        <button type="button" id="admin-qr-camera-stop" class="admin-button-secondary hidden">Matikan Kamera</button>
                    </div>
                    <p class="mt-2 text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Browser akan meminta izin kamera. Pilih kamera belakang jika tersedia.</p>
                </div>

                <aside class="admin-soft-panel flex min-w-0 flex-col justify-between" aria-labelledby="admin-check-in-today-title">
                    <div class="min-w-0">
                        <p class="admin-eyebrow">Status Operasional</p>
                        <div class="mt-3 flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <h3 id="admin-check-in-today-title" class="text-xl font-black text-zinc-950 dark:text-white">Status Check-in Hari Ini</h3>
                                <p class="mt-2 admin-copy">Pantau hasil pindai QR dan lanjutkan konfirmasi dari pratinjau member.</p>
                            </div>
                            <div class="shrink-0 rounded-lg border border-gold-500/25 bg-gold-500/10 px-4 py-3 text-center text-zinc-950 dark:text-white">
                                <p class="text-2xl font-black leading-none">{{ $todayCheckIns->count() }}</p>
                                <p class="mt-1 text-xs font-black uppercase tracking-[0.12em] text-zinc-600 dark:text-zinc-300">Check-in</p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 lg:grid-cols-3">
                            @foreach (['Pindai QR', 'Cek Pratinjau', 'Konfirmasi Aksi'] as $step)
                                <div class="admin-panel px-3 py-2 text-sm font-black text-zinc-700 dark:text-zinc-200">{{ $step }}</div>
                            @endforeach
                        </div>

                        <div class="admin-panel mt-5 px-4 py-3 text-sm font-bold leading-6 text-zinc-600 dark:text-zinc-300">
                            Riwayat lengkap tersedia di tabel bawah dengan filter tanggal dan pencarian member.
                        </div>
                    </div>
                </aside>

                <form id="admin-qr-scan-form" method="POST" action="{{ route('admin.check-in.preview') }}" class="hidden" aria-hidden="true">
                    @csrf
                    <input type="hidden" name="token" value="">
                </form>
            </div>
        </section>

        @if (is_array($checkInPreview))
            @php
                $previewAvatar = $checkInPreview['avatar'] ?? null;
                $previewAvatarUrl = filled($previewAvatar)
                    ? (str_starts_with((string) $previewAvatar, 'storage/') ? asset($previewAvatar) : (string) $previewAvatar)
                    : null;
                $previewSessions = collect($checkInPreview['sessions'] ?? []);
                $alreadyCheckedIn = filled($checkInPreview['today_check_in'] ?? null);
                $hasPreviewMembership = filled($checkInPreview['membership'] ?? null);
                $membershipActionDisabled = ! $hasPreviewMembership || $alreadyCheckedIn;
                $combinedActionDisabled = $membershipActionDisabled || $previewSessions->isEmpty();
            @endphp
            <section class="admin-card mt-6" data-admin-check-in-preview>
                <div class="grid min-w-0 gap-5 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] lg:items-start">
                    <div class="admin-panel">
                        <p class="admin-eyebrow">Pratinjau Member</p>
                        <div class="mt-4 flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start">
                            <div class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-lg border border-gold-500/25 bg-gold-500 text-2xl font-black text-zinc-950" aria-hidden="true">
                                @if ($previewAvatarUrl)
                                    <img src="{{ $previewAvatarUrl }}" alt="" class="h-full w-full object-cover">
                                @else
                                    {{ str($checkInPreview['name'] ?? 'M')->substr(0, 1)->upper() }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h3 class="break-words text-xl font-black text-zinc-950 dark:text-white">{{ $checkInPreview['name'] ?? '-' }}</h3>
                                <p class="mt-2 break-words font-mono text-sm font-bold text-zinc-600 dark:text-zinc-300">{{ $checkInPreview['member_code'] ?? '-' }}</p>
                                <p class="mt-2 break-words text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $checkInPreview['phone'] ?? '-' }}</p>
                            </div>
                        </div>

                        <dl class="mt-5 space-y-3 text-sm">
                            <div class="flex flex-wrap items-start justify-between gap-3"><dt class="font-semibold text-zinc-500 dark:text-zinc-400">Membership</dt><dd class="max-w-full break-words text-right font-black text-zinc-950 dark:text-white">{{ $checkInPreview['membership']['name'] ?? 'Tidak ada membership aktif' }}</dd></div>
                            <div class="flex flex-wrap items-start justify-between gap-3"><dt class="font-semibold text-zinc-500 dark:text-zinc-400">Berlaku sampai</dt><dd class="font-black text-zinc-950 dark:text-white">{{ $checkInPreview['membership']['end_date'] ?? '-' }}</dd></div>
                            <div class="flex flex-wrap items-start justify-between gap-3"><dt class="font-semibold text-zinc-500 dark:text-zinc-400">QR</dt><dd><span class="admin-status-pill admin-status-success">{{ $checkInPreview['qr']['status'] ?? 'Aktif' }}</span></dd></div>
                            <div class="flex flex-wrap items-start justify-between gap-3"><dt class="font-semibold text-zinc-500 dark:text-zinc-400">Hari ini</dt><dd class="font-black {{ $alreadyCheckedIn ? 'text-amber-700 dark:text-amber-300' : 'text-emerald-700 dark:text-emerald-300' }}">{{ $hasPreviewMembership ? ($alreadyCheckedIn ? 'Sudah check-in '.$checkInPreview['today_check_in']['time'] : 'Belum check-in') : 'Tidak tersedia untuk QR sesi' }}</dd></div>
                        </dl>
                    </div>

                    <div class="admin-panel bg-white dark:bg-zinc-950/45">
                        <p class="admin-eyebrow">Konfirmasi Tindakan</p>
                        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Pilih aksi admin</h3>
                        <p class="mt-2 admin-copy">{{ $hasPreviewMembership ? 'Pemindaian QR hanya menampilkan pratinjau. Check-in dan penggunaan sesi terjadi setelah tombol di bawah dikonfirmasi.' : 'QR ini aktif dari paket sesi. Gunakan tombol Gunakan Sesi; check-in membership tersedia setelah member memiliki membership aktif.' }}</p>

                        <form method="POST" action="{{ route('admin.check-in.confirm') }}" class="mt-5 grid gap-4" x-data="{ submitting: false, selectedAction: '', selectedSessionId: '', selectedEnrollmentId: '', sessionError: '', requiresSession(action) { return ['use_package_session', 'check_in_and_use_session'].includes(action) }, syncSelectedSession(event) { const option = event.target.selectedOptions[0]; this.selectedEnrollmentId = option?.dataset.classEnrollmentId || ''; this.sessionError = ''; } }" x-on:submit="if (requiresSession(selectedAction) && ! selectedSessionId) { $event.preventDefault(); sessionError = 'Pilih paket sesi terlebih dahulu sebelum menggunakan sesi.'; return; } if (submitting) { $event.preventDefault() } else { submitting = true }">
                            @csrf
                            <input type="hidden" name="preview_key" value="{{ $checkInPreview['preview_key'] ?? '' }}">
                            <input type="hidden" name="action" x-bind:value="selectedAction">
                            <input type="hidden" name="class_enrollment_id" x-bind:value="selectedEnrollmentId">

                            @if ($previewSessions->isNotEmpty())
                                <label class="admin-field">
                                    <span class="admin-field-label">Paket sesi aktif</span>
                                    <select name="member_package_session_id" class="admin-form-input" x-model="selectedSessionId" x-on:change="syncSelectedSession($event)">
                                        <option value="">Pilih paket sesi yang ingin digunakan</option>
                                        @foreach ($previewSessions as $session)
                                            <option value="{{ $session['id'] }}" data-class-enrollment-id="{{ $session['class_enrollment_id'] ?? '' }}">{{ $session['usage_label'] ?? ($session['name'].' - '.$session['remaining'].'/'.$session['total'].' sesi') }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @else
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm font-bold text-zinc-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-400">{{ $checkInPreview['session_notice'] ?? 'Tidak ada paket sesi aktif yang bisa digunakan.' }}</div>
                            @endif

                            @if (filled($checkInPreview['session_notice'] ?? null) && $previewSessions->isNotEmpty())
                                <div class="rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-sm font-bold text-amber-800 dark:text-amber-200">{{ $checkInPreview['session_notice'] }}</div>
                            @endif

                            <div x-cloak x-show="sessionError" class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2 text-sm font-bold text-red-700 dark:text-red-200" role="alert" aria-live="assertive" x-text="sessionError"></div>

                            <div class="grid min-w-0 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                <button type="submit" name="action" value="check_in_membership" class="admin-button-primary w-full" x-on:click="selectedAction = 'check_in_membership'" x-bind:disabled="submitting || @js($membershipActionDisabled)" @disabled($membershipActionDisabled)>Check-in Member</button>
                                <button type="submit" name="action" value="use_package_session" class="admin-button-secondary w-full" x-on:click="selectedAction = 'use_package_session'" x-bind:disabled="submitting || @js($previewSessions->isEmpty())" @disabled($previewSessions->isEmpty())>Gunakan Sesi</button>
                                <button type="submit" name="action" value="check_in_and_use_session" class="admin-button-secondary w-full" x-on:click="selectedAction = 'check_in_and_use_session'" x-bind:disabled="submitting || @js($combinedActionDisabled)" @disabled($combinedActionDisabled)>Check-in + Gunakan Sesi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        @endif
    @endif

    @include('admin.pages.operations')

    @if (! in_array($page['key'], ['settings', 'profile'], true))
        <div class="mt-6">
            @if ($module)
            @include($module['view'] ?? 'admin.partials.data-table', ['module' => $module])
            @else
            <section class="admin-card">
                <div class="admin-soft-panel text-center">
                    @include('admin.partials.icon', ['name' => 'empty', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
                    <p class="mt-3 font-black text-zinc-950 dark:text-white">Data belum tersedia.</p>
                </div>
            </section>
            @endif
        </div>
    @endif
</x-admin-layout>
