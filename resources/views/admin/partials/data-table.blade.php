@props([
    'module' => [],
])

@php
    $columns = collect($module['columns'] ?? [])->values();
    $rows = collect($module['rows'] ?? [])->values();
    $statusIndex = $columns->search(fn ($column) => str((string) $column)->lower()->toString() === 'status');
    $hasStatusColumn = $statusIndex !== false;
    $statusKeyFor = fn ($value) => str((string) $value)->lower()->replace([' ', '_'], '-')->slug()->toString();
    $statusOptions = $hasStatusColumn
        ? $rows->map(fn ($row) => (string) data_get($row, $statusIndex, '-'))->filter()->unique()->values()
        : collect();
@endphp

<section
    class="admin-card"
    x-data="{ search: '', status: 'all', visibleCount: {{ $rows->count() }}, update() { const query = this.search.trim().toLowerCase(); const desktopRows = Array.from(this.$root.querySelectorAll('[data-admin-table-desktop-row]')); this.visibleCount = 0; desktopRows.forEach((row) => { const visible = (! query || row.dataset.search.includes(query)) && (this.status === 'all' || row.dataset.status === this.status); row.hidden = ! visible; const mobileRow = this.$root.querySelector(`[data-admin-table-mobile-row='${row.dataset.rowIndex}']`); if (mobileRow) { mobileRow.hidden = ! visible; } if (visible) { this.visibleCount += 1; } }); } }"
    x-init="update()"
    x-effect="search; status; update()"
>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <p class="admin-eyebrow">Data</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">{{ $module['title'] ?? 'Data Admin' }}</h2>
            <p class="mt-2 admin-copy">{{ $module['description'] ?? 'Ringkasan data Platinum Gym.' }}</p>
        </div>
        <span class="admin-status-pill shrink-0 bg-zinc-100 text-zinc-600 dark:bg-white/[0.07] dark:text-zinc-300">
            <span x-text="visibleCount">{{ $rows->count() }}</span>
            <span class="ml-1">dari {{ $rows->count() }} item</span>
        </span>
    </div>

    @if ($rows->isNotEmpty())
        <div class="mt-5 grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
            <label class="min-w-0">
                <span class="sr-only">Cari {{ $module['title'] ?? 'data admin' }}</span>
                <input
                    type="search"
                    class="admin-table-search"
                    placeholder="Cari data..."
                    autocomplete="off"
                    spellcheck="false"
                    data-admin-table-search
                    x-model.debounce.150ms="search"
                >
            </label>

            @if ($statusOptions->isNotEmpty())
                <div class="flex min-w-0 flex-wrap gap-2" aria-label="Filter status">
                    <button type="button" class="admin-status-pill border transition focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40" x-bind:aria-pressed="status === 'all'" x-bind:class="status === 'all' ? 'border-gold-500/40 bg-gold-500/10 text-gold-600 dark:text-gold-400' : 'border-zinc-200 bg-white text-zinc-600 hover:border-gold-500/50 dark:border-white/10 dark:bg-zinc-950/45 dark:text-zinc-300'" x-on:click="status = 'all'; update()">Semua</button>
                    @foreach ($statusOptions as $option)
                        @php
                            $statusKey = $statusKeyFor($option);
                        @endphp
                        <button type="button" class="admin-status-pill border transition focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40" x-bind:aria-pressed="status === '{{ $statusKey }}'" x-bind:class="status === '{{ $statusKey }}' ? 'border-gold-500/40 bg-gold-500/10 text-gold-600 dark:text-gold-400' : 'border-zinc-200 bg-white text-zinc-600 hover:border-gold-500/50 dark:border-white/10 dark:bg-zinc-950/45 dark:text-zinc-300'" x-on:click="status = '{{ $statusKey }}'; update()">{{ $option }}</button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="admin-table-wrap mt-5 hidden md:block">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-white/10">
                <thead class="bg-zinc-50 text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:bg-white/[0.03] dark:text-zinc-400">
                    <tr>
                        @foreach ($columns as $column)
                            <th scope="col" class="px-4 py-3">{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-white/10 dark:bg-transparent">
                    @foreach ($rows as $row)
                        @php
                            $rowCells = collect($row)->values();
                            $searchText = str($rowCells->implode(' '))->lower()->squish()->toString();
                            $statusKey = $hasStatusColumn ? $statusKeyFor($rowCells->get($statusIndex, '-')) : '';
                        @endphp
                        <tr class="align-top transition hover:bg-zinc-50 dark:hover:bg-white/[0.035]" data-admin-table-desktop-row data-row-index="{{ $loop->index }}" data-search="{{ $searchText }}" data-status="{{ $statusKey }}">
                            @foreach ($rowCells as $cell)
                                <td class="max-w-[18rem] break-words px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-200">
                                    @if ($hasStatusColumn && $loop->index === $statusIndex)
                                        <span class="admin-status-pill bg-zinc-100 text-zinc-700 dark:bg-white/[0.07] dark:text-zinc-300">{{ $cell }}</span>
                                    @else
                                        {{ $cell }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5 grid gap-3 md:hidden">
            @foreach ($rows as $row)
                @php
                    $rowCells = collect($row)->values();
                @endphp
                <article class="admin-table-mobile-card" data-admin-table-mobile-row="{{ $loop->index }}">
                    <dl class="grid gap-3">
                        @foreach ($columns as $column)
                            @php
                                $cell = $rowCells->get($loop->index, '-');
                            @endphp
                            <div class="min-w-0">
                                <dt class="text-[0.68rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ $column }}</dt>
                                <dd class="mt-1 break-words text-sm font-bold text-zinc-800 dark:text-zinc-100">
                                    @if ($hasStatusColumn && $loop->index === $statusIndex)
                                        <span class="admin-status-pill bg-zinc-100 text-zinc-700 dark:bg-white/[0.07] dark:text-zinc-300">{{ $cell }}</span>
                                    @else
                                        {{ $cell }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </article>
            @endforeach
        </div>

        <div x-cloak x-show="visibleCount === 0" class="admin-soft-panel mt-5 text-center">
            @include('admin.partials.icon', ['name' => 'empty', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
            <p class="mt-3 font-black text-zinc-950 dark:text-white">Tidak ada data yang cocok.</p>
            <p class="mt-1 text-sm font-semibold text-zinc-500 dark:text-zinc-400">Ubah kata kunci atau filter status.</p>
        </div>
    @else
        <div class="admin-soft-panel mt-5 text-center">
            @include('admin.partials.icon', ['name' => 'empty', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
            <p class="mt-3 font-black text-zinc-950 dark:text-white">{{ $module['empty'] ?? 'Belum ada data.' }}</p>
        </div>
    @endif
</section>