@php
    $module = $portal['modules'][$page['moduleKey']] ?? null;
@endphp

<x-admin-layout :portal="$portal" :navigation="$navigation" :title="$page['title']">
    <section class="admin-page-header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 max-w-3xl">
                <h2 class="admin-title">{{ $page['title'] }}</h2>
                <p class="mt-3 admin-copy">{{ $page['description'] }}</p>
            </div>
            @if (! empty($page['secondaryCreateResource']) || ! empty($page['createResource']))
                <div class="flex shrink-0 flex-col-reverse gap-2 sm:flex-row">
                @if (! empty($page['secondaryCreateResource']))
                    <a href="{{ route('admin.resources.create', $page['secondaryCreateResource']) }}" class="admin-button-secondary">{{ $page['secondaryCreateLabel'] ?? 'Tambah Data Pendukung' }}</a>
                @endif
                @if (! empty($page['createResource']))
                    <a href="{{ route('admin.resources.create', $page['createResource']) }}" class="admin-button-primary">Tambah {{ $page['title'] }}</a>
                @endif
                </div>
            @endif
        </div>
    </section>

    @if ($page['key'] === 'check-in')
        <section class="admin-card mt-6" x-data="{ pasteOpen: false }">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="admin-eyebrow">Scan QR</p>
                    <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Check-in member via kamera</h2>
                    <p class="mt-2 admin-copy">Aktifkan kamera untuk scan QR member secara langsung. Gunakan input manual jika kamera tidak tersedia.</p>
                </div>
            </div>

            <div id="admin-qr-camera-secure-banner" class="mt-4 hidden rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm font-bold text-amber-800 dark:text-amber-200" role="status">
                Kamera membutuhkan koneksi aman (HTTPS) atau localhost. Domain ini belum mengaktifkan SSL, jadi gunakan input token manual atau pilih member di bawah.
            </div>
            <div id="admin-qr-camera-support-banner" class="mt-4 hidden rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm font-bold text-amber-800 dark:text-amber-200" role="status">
                Browser ini tidak mendukung akses kamera. Gunakan input token manual atau pilih member di bawah.
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,22rem)_minmax(0,1fr)] lg:items-start">
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-white/10 dark:bg-zinc-950/45">
                    <div id="admin-qr-camera-region" class="aspect-square w-full overflow-hidden rounded-md bg-zinc-950" aria-label="Pratinjau kamera scan QR"></div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" id="admin-qr-camera-start" class="admin-button-primary inline-flex items-center gap-2">
                            @include('admin.partials.icon', ['name' => 'qr', 'class' => 'h-4 w-4'])
                            Mulai Kamera
                        </button>
                        <button type="button" id="admin-qr-camera-stop" class="admin-button-secondary hidden">Matikan Kamera</button>
                    </div>
                    <p class="mt-2 text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Browser akan meminta izin kamera. Pilih kamera belakang jika tersedia.</p>
                </div>

                <div class="grid gap-4">
                    <form id="admin-qr-scan-form" method="POST" action="{{ route('admin.check-in.scan') }}" class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                        @csrf
                        <button type="button" x-on:click="pasteOpen = !pasteOpen" x-bind:aria-expanded="pasteOpen.toString()" class="text-sm font-black uppercase tracking-[0.14em] text-zinc-500 hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:text-zinc-400">
                            <span x-show="!pasteOpen">+ Input Token Manual</span>
                            <span x-show="pasteOpen" x-cloak>− Sembunyikan Input Token</span>
                        </button>
                        <div x-show="pasteOpen" x-cloak class="mt-4 grid gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                            <label>
                                <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Token QR</span>
                                <input type="text" name="token" value="{{ old('token') }}" maxlength="64" autocomplete="off" class="mt-2 min-h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 font-mono text-sm font-bold text-zinc-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white" placeholder="Tempel hasil scan QR">
                            </label>
                            <button type="submit" class="admin-button-primary">Proses Check-in</button>
                        </div>
                        <noscript>
                            <div class="mt-4 grid gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                                <label>
                                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Token QR</span>
                                    <input type="text" name="token" value="{{ old('token') }}" maxlength="64" autocomplete="off" class="mt-2 min-h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 font-mono text-sm font-bold text-zinc-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white" placeholder="Tempel hasil scan QR">
                                </label>
                                <button type="submit" class="admin-button-primary">Proses Check-in</button>
                            </div>
                        </noscript>
                    </form>

                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                        <p class="text-sm font-black text-zinc-950 dark:text-white">Check-in manual</p>
                        <p class="mt-1 text-sm font-semibold leading-6 text-zinc-500 dark:text-zinc-400">Gunakan saat scanner QR tidak tersedia. Sistem tetap mengecek membership aktif dan duplicate harian.</p>
                        <form method="POST" action="{{ route('admin.check-in.manual') }}" class="mt-4 grid gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                            @csrf
                            <label>
                                <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Member Aktif</span>
                                <select name="member_id" class="admin-form-input mt-2" required>
                                    <option value="">Pilih member</option>
                                    @foreach (collect($portal['checkInCandidates'] ?? []) as $candidate)
                                        <option value="{{ $candidate->id }}" @selected((string) old('member_id') === (string) $candidate->id)>{{ $candidate->user?->name ?? $candidate->member_code }} - {{ $candidate->member_code }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button type="submit" class="admin-button-secondary">Check-in Manual</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @include('admin.pages.operations')

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
</x-admin-layout>
