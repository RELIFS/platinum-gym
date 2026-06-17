@props([
    'module' => [],
])

@php
    $rows = collect($module['rows'] ?? [])->values();
    $title = $module['title'] ?? 'Kesiapan Notifikasi';
    $description = $module['description'] ?? 'Area pengingat member, booking, dan pembayaran.';
    $empty = $module['empty'] ?? 'Belum ada daftar notifikasi operasional.';
    $tableId = 'admin-table-'.str($title)->slug()->toString();
    $kindMap = [
        'success' => 'admin-status-success',
        'warning' => 'admin-status-warning',
        'danger' => 'admin-status-danger',
        'info' => 'admin-status-info',
        'neutral' => 'admin-status-neutral',
    ];
@endphp

<section class="admin-card" aria-labelledby="{{ $tableId }}-title">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <h2 id="{{ $tableId }}-title" class="mt-2 text-xl font-black text-zinc-950 dark:text-white">{{ $title }}</h2>
            <p class="mt-2 admin-copy">{{ $description }}</p>
        </div>
        <span class="admin-status-pill shrink-0 bg-zinc-100 text-zinc-600 dark:bg-white/[0.07] dark:text-zinc-300">{{ $rows->count() }} area</span>
    </div>

    @if ($rows->isNotEmpty())
        <div class="admin-table-wrap mt-5 hidden md:block">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-white/10">
                <caption class="sr-only">{{ $title }}</caption>
                <thead class="bg-zinc-50 text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:bg-white/[0.03] dark:text-zinc-400">
                    <tr>
                        <th scope="col" class="px-4 py-3">Area</th>
                        <th scope="col" class="px-4 py-3">Status</th>
                        <th scope="col" class="px-4 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-white/10 dark:bg-transparent">
                    @foreach ($rows as $row)
                        @php
                            $kind = $row['kind'] ?? 'neutral';
                            $statusClass = $kindMap[$kind] ?? $kindMap['neutral'];
                        @endphp
                        <tr class="align-top transition hover:bg-zinc-50 dark:hover:bg-white/[0.035]">
                            <td class="max-w-[18rem] break-words px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-200">{{ $row['area'] ?? '-' }}</td>
                            <td class="max-w-[18rem] break-words px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-200">
                                <span class="admin-status-pill {{ $statusClass }}">{{ $row['status'] ?? '-' }}</span>
                            </td>
                            <td class="max-w-[28rem] break-words px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-200">{{ $row['note'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5 grid gap-3 md:hidden">
            @foreach ($rows as $row)
                @php
                    $kind = $row['kind'] ?? 'neutral';
                    $statusClass = $kindMap[$kind] ?? $kindMap['neutral'];
                @endphp
                <article class="admin-table-mobile-card">
                    <dl class="grid gap-3">
                        <div class="min-w-0">
                            <dt class="text-[0.68rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Area</dt>
                            <dd class="mt-1 break-words text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ $row['area'] ?? '-' }}</dd>
                        </div>
                        <div class="min-w-0">
                            <dt class="text-[0.68rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Status</dt>
                            <dd class="mt-1 break-words text-sm font-bold text-zinc-800 dark:text-zinc-100">
                                <span class="admin-status-pill {{ $statusClass }}">{{ $row['status'] ?? '-' }}</span>
                            </dd>
                        </div>
                        <div class="min-w-0">
                            <dt class="text-[0.68rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Catatan</dt>
                            <dd class="mt-1 break-words text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ $row['note'] ?? '-' }}</dd>
                        </div>
                    </dl>
                </article>
            @endforeach
        </div>
    @else
        <div class="admin-soft-panel mt-5 text-center">
            @include('admin.partials.icon', ['name' => 'empty', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
            <p class="mt-3 font-black text-zinc-950 dark:text-white">{{ $empty }}</p>
        </div>
    @endif
</section>
