@php
    $stats = collect($portal['stats']);
    $moduleSummaries = collect($portal['moduleSummaries']);
    $module = $portal['modules'][$page['moduleKey']] ?? null;
    $siblingPages = collect($moduleSummaries)->take(6);
@endphp

<x-admin-layout :portal="$portal" :navigation="$navigation" :title="$page['title']">
    <section class="admin-page-header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 max-w-3xl">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="admin-eyebrow">{{ $page['eyebrow'] }}</p>
                    <span class="admin-readonly-pill">Read-only v1</span>
                </div>
                <h2 class="admin-title mt-3">{{ $page['title'] }}</h2>
                <p class="mt-3 admin-copy">{{ $page['description'] }}</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="admin-button-secondary shrink-0">Dashboard</a>
        </div>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan admin">
        @foreach ($stats as $stat)
            <article class="admin-stat">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</p>
                <p class="mt-3 break-words text-2xl font-black tabular-nums text-zinc-950 dark:text-white">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm font-medium leading-6 text-zinc-500 dark:text-zinc-400">{{ $stat['description'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
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

        <aside class="space-y-6">
            <section class="admin-card">
                <p class="admin-eyebrow">Modul</p>
                <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Akses terkait</h2>
                <div class="mt-5 grid gap-3">
                    @foreach ($siblingPages as $summary)
                        <a href="{{ route($summary['route']) }}" class="group flex min-h-16 items-center gap-3 rounded-lg border border-zinc-200 bg-white p-3 transition hover:border-gold-500/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-zinc-950/45">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-gold-500/10 text-sm font-black tabular-nums text-gold-600 dark:text-gold-400">{{ $summary['value'] }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-black text-zinc-950 dark:text-white">{{ $summary['label'] }}</span>
                                <span class="mt-0.5 block text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">{{ $summary['description'] }}</span>
                            </span>
                            @include('admin.partials.icon', ['name' => 'arrow', 'class' => 'h-4 w-4 shrink-0 text-zinc-400 group-hover:text-gold-500'])
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="admin-card">
                <p class="admin-eyebrow">Website</p>
                <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Preview publik</h2>
                <div class="mt-5 grid gap-3">
                    <a href="{{ route('public.home') }}" class="admin-button-secondary w-full">Beranda</a>
                    <a href="{{ route('public.services') }}" class="admin-button-secondary w-full">Layanan</a>
                    <a href="{{ route('public.gallery') }}" class="admin-button-secondary w-full">Galeri</a>
                </div>
            </section>
        </aside>
    </div>
</x-admin-layout>