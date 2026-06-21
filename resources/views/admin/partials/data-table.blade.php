@props([
    'module' => [],
])

@php
    $columns = collect($module['columns'] ?? [])->values();
    $rows = collect($module['rows'] ?? [])->values()->map(fn ($row) => is_array($row) && array_key_exists('cells', $row) ? $row : ['cells' => $row, 'actions' => []]);
    $paginator = $module['paginator'] ?? null;
    $hasPaginator = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $filters = collect($module['filters'] ?? []);
    $statusOptions = collect($module['statusOptions'] ?? []);
    $hasDateFilters = (bool) ($module['dateFilters'] ?? false);
    $hasActions = $rows->contains(fn ($row) => ! empty($row['actions'] ?? []));
    $pillColumns = collect($module['pillColumns'] ?? [])->map(fn ($column) => str((string) $column)->lower()->toString());
    $pillColumnIndexes = $columns->keys()->filter(fn ($index) => str((string) $columns->get($index))->lower()->toString() === 'status' || $pillColumns->contains(str((string) $columns->get($index))->lower()->toString()))->values();
    $tableId = 'admin-table-'.str($module['title'] ?? 'data-admin')->slug()->toString();
    $hasActiveFilter = filled($filters->get('q')) || filled($filters->get('status'));
    $preservedFilterKeys = $hasDateFilters ? ['event', 'causer_id'] : ['date_from', 'date_to', 'event', 'causer_id'];
    $preservedFilters = collect($preservedFilterKeys)
        ->mapWithKeys(fn ($key) => [$key => $filters->get($key)])
        ->filter(fn ($value) => filled($value));
    $formGridClass = $hasDateFilters
        ? 'mt-5 grid gap-3 lg:grid-cols-[minmax(0,1fr)_12rem_12rem_auto_auto] lg:items-end'
        : 'mt-5 grid gap-3 lg:grid-cols-[minmax(0,1fr)_14rem_auto_auto] lg:items-end';
    $countText = $hasPaginator
        ? match (true) {
            $paginator->total() === 0 => '0 data',
            filled($paginator->firstItem()) => 'Menampilkan '.$paginator->firstItem().'-'.$paginator->lastItem().' dari '.$paginator->total().' data',
            default => 'Tidak ada data pada halaman ini dari '.$paginator->total().' data',
        }
        : $rows->count().' data';
@endphp

<section class="admin-card" aria-labelledby="{{ $tableId }}-title">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <h2 id="{{ $tableId }}-title" class="mt-2 text-xl font-black text-zinc-950 dark:text-white">{{ $module['title'] ?? 'Data Admin' }}</h2>
            <p class="mt-2 admin-copy">{{ $module['description'] ?? 'Ringkasan data Platinum Gym.' }}</p>
        </div>
        <span class="admin-status-pill admin-status-neutral shrink-0">{{ $countText }}</span>
    </div>

    @if ($hasPaginator || $statusOptions->isNotEmpty() || $hasActiveFilter || $hasDateFilters)
        <form method="GET" action="{{ url()->current() }}" class="admin-toolbar {{ $formGridClass }}">
            @foreach ($preservedFilters as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach

            <label class="min-w-0">
                <span class="sr-only">Cari {{ $module['title'] ?? 'data admin' }}</span>
                <span class="relative block">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-zinc-400 dark:text-zinc-500" aria-hidden="true">
                        @include('admin.partials.icon', ['name' => 'search', 'class' => 'h-4 w-4'])
                    </span>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters->get('q') }}"
                        class="admin-table-search pl-9"
                        placeholder="{{ $module['searchPlaceholder'] ?? 'Cari nama, kode, status...' }}"
                        autocomplete="off"
                        spellcheck="false"
                        data-admin-table-search
                    >
                </span>
            </label>

            @if ($statusOptions->isNotEmpty())
                <label class="min-w-0">
                    <span class="sr-only">Filter status</span>
                    <select name="status" class="admin-form-input min-h-11" aria-label="Filter status {{ $module['title'] ?? 'data admin' }}">
                        <option value="">Semua status</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected((string) $filters->get('status') === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @elseif (! $hasDateFilters)
                <span class="hidden lg:block" aria-hidden="true"></span>
            @endif

            @if ($hasDateFilters)
                <label class="min-w-0">
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Dari tanggal</span>
                    <input type="date" name="date_from" value="{{ $filters->get('date_from') }}" max="{{ $filters->get('date_to') }}" class="admin-form-input mt-2 min-h-11">
                </label>
                <label class="min-w-0">
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Sampai tanggal</span>
                    <input type="date" name="date_to" value="{{ $filters->get('date_to') }}" min="{{ $filters->get('date_from') }}" class="admin-form-input mt-2 min-h-11">
                </label>
            @endif

            <button type="submit" class="admin-button-primary min-h-11">Terapkan</button>
            <a href="{{ url()->current().($preservedFilters->isNotEmpty() ? '?'.http_build_query($preservedFilters->all()) : '') }}" class="admin-button-secondary min-h-11">Bersihkan Pencarian</a>
        </form>
    @endif

    @if ($rows->isNotEmpty())
        <div class="admin-table-wrap mt-5 hidden md:block">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-white/10">
                <caption class="sr-only">{{ $module['title'] ?? 'Data Admin' }}</caption>
                <thead class="admin-table-head">
                    <tr>
                        @foreach ($columns as $column)
                            <th scope="col" class="px-4 py-3">{{ $column }}</th>
                        @endforeach
                        @if ($hasActions)
                            <th scope="col" class="px-4 py-3">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    @foreach ($rows as $row)
                        @php
                            $rowCells = collect($row['cells'] ?? [])->values();
                            $rowActions = collect($row['actions'] ?? []);
                        @endphp
                        <tr class="admin-table-row">
                            @foreach ($rowCells as $cell)
                                <td class="admin-table-cell">
                                    @if ($pillColumnIndexes->contains($loop->index))
                                        <span class="admin-status-pill admin-status-neutral">{{ $cell }}</span>
                                    @else
                                        {{ $cell }}
                                    @endif
                                </td>
                            @endforeach
                            @if ($hasActions)
                                <td class="px-4 py-3">
                                    @include('admin.partials.table-actions', ['actions' => $rowActions])
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5 grid gap-3 md:hidden">
            @foreach ($rows as $row)
                @php
                    $rowCells = collect($row['cells'] ?? [])->values();
                    $rowActions = collect($row['actions'] ?? []);
                @endphp
                <article class="admin-table-mobile-card">
                    <dl class="grid gap-3">
                        @foreach ($columns as $column)
                            @php
                                $cell = $rowCells->get($loop->index, '-');
                            @endphp
                            <div class="min-w-0">
                                <dt class="admin-table-label">{{ $column }}</dt>
                                <dd class="admin-table-value">
                                    @if ($pillColumnIndexes->contains($loop->index))
                                        <span class="admin-status-pill admin-status-neutral">{{ $cell }}</span>
                                    @else
                                        {{ $cell }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                    @if ($rowActions->isNotEmpty())
                        <div class="mt-4">
                            @include('admin.partials.table-actions', ['actions' => $rowActions])
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        @if ($hasPaginator && $paginator->hasPages())
            @php
                $lastPage = max(1, $paginator->lastPage());
                $currentPage = min(max(1, $paginator->currentPage()), $lastPage);
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
            @endphp
            <nav class="mt-5 flex flex-col gap-3 border-t border-zinc-200 pt-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between" aria-label="Navigasi halaman {{ $module['title'] ?? 'data admin' }}">
                <p class="text-sm font-semibold text-zinc-500 dark:text-zinc-400">Halaman {{ $currentPage }} dari {{ $lastPage }}</p>
                <div class="flex flex-wrap gap-2">
                    @if ($paginator->onFirstPage())
                        <span class="admin-pagination-disabled" aria-disabled="true">Sebelumnya</span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="admin-button-secondary min-h-11" aria-label="Halaman sebelumnya">Sebelumnya</a>
                    @endif

                    @foreach (range($startPage, $endPage) as $pageNumber)
                        @if ($pageNumber === $currentPage)
                            <span class="admin-pagination-active" aria-current="page">{{ $pageNumber }}</span>
                        @else
                            <a href="{{ $paginator->url($pageNumber) }}" class="admin-pagination-link" aria-label="Halaman {{ $pageNumber }}">{{ $pageNumber }}</a>
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="admin-button-secondary min-h-11" aria-label="Halaman berikutnya">Berikutnya</a>
                    @else
                        <span class="admin-pagination-disabled" aria-disabled="true">Berikutnya</span>
                    @endif
                </div>
            </nav>
        @endif
    @else
        <div class="admin-soft-panel mt-5 text-center">
            @include('admin.partials.icon', ['name' => 'empty', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
            <p class="mt-3 font-black text-zinc-950 dark:text-white">{{ $hasActiveFilter ? 'Tidak ada data yang cocok dengan filter ini.' : ($module['empty'] ?? 'Belum ada data.') }}</p>
            @if ($hasActiveFilter)
                <p class="mt-1 text-sm font-semibold text-zinc-500 dark:text-zinc-400">Ubah kata kunci atau filter.</p>
            @endif
        </div>
    @endif
</section>
