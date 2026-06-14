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
                <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
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
        <section class="admin-card mt-6">
            <p class="admin-eyebrow">Scan QR</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Check-in member</h2>
            <form method="POST" action="{{ route('admin.check-in.scan') }}" class="mt-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                @csrf
                <label>
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Token QR</span>
                    <input type="text" name="token" value="{{ old('token') }}" maxlength="64" autocomplete="off" class="mt-2 min-h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 font-mono text-sm font-bold text-zinc-900 shadow-sm focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white" placeholder="Tempel hasil scan QR">
                </label>
                <button type="submit" class="admin-button-primary">Proses Check-in</button>
            </form>

            <div class="mt-6 border-t border-zinc-200 pt-5 dark:border-white/10">
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
        </section>
    @endif

    @include('admin.pages.operations')

    <div class="mt-6">
        @if ($module)
            @include('admin.partials.data-table', ['module' => $module])
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
