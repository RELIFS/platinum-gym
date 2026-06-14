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
    $hasActions = $rows->contains(fn ($row) => ! empty($row['actions'] ?? []));
    $statusIndex = $columns->search(fn ($column) => str((string) $column)->lower()->toString() === 'status');
    $hasStatusColumn = $statusIndex !== false;
    $tableId = 'admin-table-'.str($module['title'] ?? 'data-admin')->slug()->toString();
    $hasActiveFilter = filled($filters->get('q')) || filled($filters->get('status'));
    $preservedFilters = collect(['date_from', 'date_to', 'event', 'causer_id'])
        ->mapWithKeys(fn ($key) => [$key => $filters->get($key)])
        ->filter(fn ($value) => filled($value));
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
        <span class="admin-status-pill shrink-0 bg-zinc-100 text-zinc-600 dark:bg-white/[0.07] dark:text-zinc-300">{{ $countText }}</span>
    </div>

    @if ($hasPaginator || $statusOptions->isNotEmpty() || $hasActiveFilter)
        <form method="GET" action="{{ url()->current() }}" class="mt-5 grid gap-3 lg:grid-cols-[minmax(0,1fr)_14rem_auto_auto] lg:items-end">
            @foreach ($preservedFilters as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach

            <label class="min-w-0">
                <span class="sr-only">Cari {{ $module['title'] ?? 'data admin' }}</span>
                <input
                    type="search"
                    name="q"
                    value="{{ $filters->get('q') }}"
                    class="admin-table-search"
                    placeholder="Cari nama, kode, status..."
                    autocomplete="off"
                    spellcheck="false"
                    data-admin-table-search
                >
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
            @else
                <span class="hidden lg:block" aria-hidden="true"></span>
            @endif

            <button type="submit" class="admin-button-primary min-h-11">Terapkan</button>
            <a href="{{ url()->current().($preservedFilters->isNotEmpty() ? '?'.http_build_query($preservedFilters->all()) : '') }}" class="admin-button-secondary min-h-11">Reset</a>
        </form>
    @endif

    @if ($rows->isNotEmpty())
        <div class="admin-table-wrap mt-5 hidden md:block">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-white/10">
                <caption class="sr-only">{{ $module['title'] ?? 'Data Admin' }}</caption>
                <thead class="bg-zinc-50 text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:bg-white/[0.03] dark:text-zinc-400">
                    <tr>
                        @foreach ($columns as $column)
                            <th scope="col" class="px-4 py-3">{{ $column }}</th>
                        @endforeach
                        @if ($hasActions)
                            <th scope="col" class="px-4 py-3">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-white/10 dark:bg-transparent">
                    @foreach ($rows as $row)
                        @php
                            $rowCells = collect($row['cells'] ?? [])->values();
                            $rowActions = collect($row['actions'] ?? []);
                        @endphp
                        <tr class="align-top transition hover:bg-zinc-50 dark:hover:bg-white/[0.035]">
                            @foreach ($rowCells as $cell)
                                <td class="max-w-[18rem] break-words px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-200">
                                    @if ($hasStatusColumn && $loop->index === $statusIndex)
                                        <span class="admin-status-pill bg-zinc-100 text-zinc-700 dark:bg-white/[0.07] dark:text-zinc-300">{{ $cell }}</span>
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
                        <span class="admin-button-secondary min-h-11 opacity-45" aria-disabled="true">Sebelumnya</span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="admin-button-secondary min-h-11" aria-label="Halaman sebelumnya">Sebelumnya</a>
                    @endif

                    @foreach (range($startPage, $endPage) as $pageNumber)
                        @if ($pageNumber === $currentPage)
                            <span class="grid min-h-11 min-w-11 place-items-center rounded-lg bg-gold-500 px-3 text-sm font-black text-zinc-950" aria-current="page">{{ $pageNumber }}</span>
                        @else
                            <a href="{{ $paginator->url($pageNumber) }}" class="grid min-h-11 min-w-11 place-items-center rounded-lg border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 transition hover:border-gold-500/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-zinc-950/45 dark:text-zinc-200" aria-label="Halaman {{ $pageNumber }}">{{ $pageNumber }}</a>
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="admin-button-secondary min-h-11" aria-label="Halaman berikutnya">Berikutnya</a>
                    @else
                        <span class="admin-button-secondary min-h-11 opacity-45" aria-disabled="true">Berikutnya</span>
                    @endif
                </div>
            </nav>
        @endif
    @else
        <div class="admin-soft-panel mt-5 text-center">
            @include('admin.partials.icon', ['name' => 'empty', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
            <p class="mt-3 font-black text-zinc-950 dark:text-white">{{ $hasActiveFilter ? 'Tidak ada data yang cocok dengan filter ini.' : ($module['empty'] ?? 'Belum ada data.') }}</p>
            @if ($hasActiveFilter)
                <p class="mt-1 text-sm font-semibold text-zinc-500 dark:text-zinc-400">Ubah kata kunci atau filter status.</p>
            @endif
        </div>
    @endif
</section>
