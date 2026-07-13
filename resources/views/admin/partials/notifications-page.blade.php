@props([
    'module' => [],
])

@php
    $rows = collect($module['rows'] ?? [])->values();
    $paginator = $module['paginator'] ?? null;
    $title = $module['title'] ?? 'Inbox Persetujuan Admin';
    $description = $module['description'] ?? 'Pantau persetujuan yang membutuhkan tindakan admin.';
    $empty = $module['empty'] ?? 'Tidak ada persetujuan yang perlu ditinjau.';
    $filters = collect($module['filters'] ?? []);
    $statusOptions = collect($module['statusOptions'] ?? []);
    $tableId = 'admin-table-'.str($title)->slug()->toString();
    $kindMap = [
        'success' => 'admin-status-success',
        'warning' => 'admin-status-warning',
        'danger' => 'admin-status-danger',
        'info' => 'admin-status-info',
        'neutral' => 'admin-status-neutral',
    ];
    $totalRows = $paginator ? $paginator->total() : $rows->count();
@endphp

<section class="admin-card" aria-labelledby="{{ $tableId }}-title">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <p class="admin-eyebrow">Persetujuan Admin</p>
            <h2 id="{{ $tableId }}-title" class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">{{ $title }}</h2>
            <p class="mt-2 admin-copy">{{ $description }}</p>
        </div>
        <span class="admin-status-pill admin-status-neutral shrink-0">{{ $totalRows }} persetujuan</span>
    </div>

    <form method="GET" action="{{ url()->current() }}" class="admin-panel mt-5 grid gap-4 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)_minmax(0,0.8fr)_minmax(0,0.8fr)_auto] lg:items-end">
        <label class="admin-field">
            <span class="admin-field-label">Cari persetujuan</span>
            <input type="search" name="q" value="{{ $filters->get('q') }}" class="admin-form-input" placeholder="{{ $module['searchPlaceholder'] ?? 'Cari member atau persetujuan...' }}">
        </label>
        <label class="admin-field">
            <span class="admin-field-label">Jenis persetujuan</span>
            <select name="status" class="admin-form-input">
                <option value="">Semua persetujuan</option>
                @foreach ($statusOptions as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected($filters->get('status') === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
        </label>
        <label class="admin-field">
            <span class="admin-field-label">Dari tanggal</span>
            <x-local-date-input id="{{ $tableId }}-date-from" name="date_from" :value="$filters->get('date_from')" :max="$filters->get('date_to')" class="admin-form-input min-h-11" />
        </label>
        <label class="admin-field">
            <span class="admin-field-label">Sampai tanggal</span>
            <x-local-date-input id="{{ $tableId }}-date-to" name="date_to" :value="$filters->get('date_to')" :min="$filters->get('date_from')" class="admin-form-input min-h-11" />
        </label>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            <button type="submit" class="admin-button-primary min-h-11">Terapkan</button>
            <a href="{{ url()->current() }}" class="admin-button-secondary min-h-11">Reset</a>
        </div>
    </form>

    @if ($rows->isNotEmpty())
        <div class="admin-table-wrap mt-5 hidden lg:block">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-white/10">
                <caption class="sr-only">{{ $title }}</caption>
                <thead class="admin-table-head">
                    <tr>
                        <th scope="col" class="px-4 py-3">Persetujuan</th>
                        <th scope="col" class="px-4 py-3">Member</th>
                        <th scope="col" class="px-4 py-3">Status</th>
                        <th scope="col" class="px-4 py-3">Waktu</th>
                        <th scope="col" class="px-4 py-3">Catatan</th>
                        <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    @foreach ($rows as $row)
                        @php($statusClass = $kindMap[$row['kind'] ?? 'neutral'] ?? $kindMap['neutral'])
                        <tr class="admin-table-row">
                            <td class="admin-table-cell type-control text-zinc-950 dark:text-zinc-100">{{ $row['title'] ?? '-' }}</td>
                            <td class="admin-table-cell">
                                <span class="block type-control text-zinc-950 dark:text-zinc-100">{{ $row['member'] ?? '-' }}</span>
                                <span class="mt-1 block font-mono text-xs type-control text-zinc-500 dark:text-zinc-400">{{ $row['member_code'] ?? '-' }}</span>
                            </td>
                            <td class="admin-table-cell">
                                <span class="admin-status-pill {{ $statusClass }}">{{ $row['status'] ?? '-' }}</span>
                            </td>
                            <td class="admin-table-cell whitespace-nowrap">{{ $row['time'] ?? '-' }}</td>
                            <td class="admin-table-cell max-w-[24rem]">{{ $row['note'] ?? '-' }}</td>
                            <td class="admin-table-cell text-right">
                                @if (filled($row['url'] ?? null))
                                    <a href="{{ $row['url'] }}" class="admin-button-secondary min-h-10 px-3">Review Bukti</a>
                                @else
                                    <span class="text-xs type-control text-zinc-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5 grid gap-3 lg:hidden">
            @foreach ($rows as $row)
                @php($statusClass = $kindMap[$row['kind'] ?? 'neutral'] ?? $kindMap['neutral'])
                <article class="admin-table-mobile-card">
                    <div class="flex min-w-0 flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="break-words type-control text-zinc-950 dark:text-zinc-100">{{ $row['title'] ?? '-' }}</p>
                            <p class="mt-1 break-words text-sm type-control text-zinc-500 dark:text-zinc-400">{{ $row['member'] ?? '-' }} - {{ $row['member_code'] ?? '-' }}</p>
                        </div>
                        <span class="admin-status-pill {{ $statusClass }}">{{ $row['status'] ?? '-' }}</span>
                    </div>
                    <dl class="mt-4 grid gap-3">
                        <div class="min-w-0">
                            <dt class="admin-table-label">Waktu</dt>
                            <dd class="admin-table-value">{{ $row['time'] ?? '-' }}</dd>
                        </div>
                        <div class="min-w-0">
                                <dt class="admin-table-label">Catatan</dt>
                            <dd class="admin-table-value">{{ $row['note'] ?? '-' }}</dd>
                        </div>
                    </dl>
                    @if (filled($row['url'] ?? null))
                        <a href="{{ $row['url'] }}" class="admin-button-secondary mt-4 w-full justify-center">Review Bukti</a>
                    @endif
                </article>
            @endforeach
        </div>

        @if ($paginator && $paginator->hasPages())
            <div class="mt-5">
                {{ $paginator->links() }}
            </div>
        @endif
    @else
        <div class="admin-soft-panel mt-5 text-center">
            @include('admin.partials.icon', ['name' => 'empty', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
            <p class="mt-3 type-control text-zinc-950 dark:text-zinc-100">{{ $empty }}</p>
        </div>
    @endif
</section>
