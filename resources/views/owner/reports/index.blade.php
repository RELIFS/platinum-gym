@php
    /** @var \App\Features\Reports\Data\ReportFilters $filters */
    $filters = $portal['filters'];
    $report = $portal['report'];
    $rows = $report['rows'];
    $options = $report['options'];
    $headings = collect($report['headings']);
@endphp

<x-owner-layout :portal="$portal" :navigation="$navigation" :title="$title">
    <section class="owner-page-header">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 max-w-3xl">
                <p class="owner-eyebrow">Laporan</p>
                <h2 class="owner-title mt-3">{{ $title }}</h2>
                <p class="mt-3 owner-copy">{{ $report['description'] }}</p>
            </div>

            <a href="{{ route('owner.reports.export', $filters->query()) }}" class="owner-button-primary">
                @include('admin.partials.icon', ['name' => 'download', 'class' => 'h-4 w-4'])
                Unduh CSV
            </a>
        </div>

        <form method="GET" action="{{ route('owner.reports.index') }}" class="owner-panel mt-5">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label for="report_type" class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Jenis laporan</label>
                    <select id="report_type" name="report_type" class="owner-form-input mt-2">
                        @foreach ($options['reportTypes'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters->reportType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Dari tanggal</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $filters->from->toDateString() }}" class="owner-form-input mt-2">
                </div>
                <div>
                    <label for="date_to" class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Sampai tanggal</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $filters->to->toDateString() }}" class="owner-form-input mt-2">
                </div>
                <div>
                    <label for="method" class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Metode</label>
                    <select id="method" name="method" class="owner-form-input mt-2">
                        <option value="">Semua metode</option>
                        @foreach ($options['paymentMethods'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters->method === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="q" class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Cari</label>
                    <input id="q" name="q" type="search" value="{{ $filters->search }}" placeholder="Cari kode, member, invoice..." class="owner-form-input mt-2">
                </div>
            </div>
            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <a href="{{ route('owner.reports.index') }}" class="owner-button-secondary">Reset</a>
                <button type="submit" class="owner-button-primary">Terapkan filter</button>
            </div>
        </form>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan laporan owner">
        @foreach ($report['summary'] as $item)
            <article class="owner-card p-4 sm:p-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">{{ $item['label'] }}</p>
                <p class="mt-3 break-words text-2xl font-black tabular-nums text-zinc-950 dark:text-white">{{ $item['value'] }}</p>
                <p class="mt-2 text-sm font-medium leading-6 text-zinc-500 dark:text-zinc-400">{{ $item['description'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="owner-card mt-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="owner-eyebrow">Preview</p>
                <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">{{ $report['title'] }}</h2>
                <p class="mt-2 owner-copy">Periode {{ $filters->periodLabel() }}. Export CSV memakai filter yang sama.</p>
            </div>
        </div>

        @if ($rows->count() > 0)
            <div class="mt-5 space-y-3 md:hidden">
                @foreach ($rows as $row)
                    <article class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        @foreach ($headings as $index => $heading)
                            <div class="flex flex-col gap-1 border-b border-zinc-200 py-2 last:border-b-0 dark:border-white/10">
                                <span class="text-[0.68rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ $heading }}</span>
                                <span class="break-words text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $row[$index] ?? '-' }}</span>
                            </div>
                        @endforeach
                        @if (($row['invoice_url'] ?? null) && $filters->reportType === 'finance')
                            <a href="{{ $row['invoice_url'] }}" class="owner-button-secondary mt-3 w-full">Lihat invoice</a>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="admin-table-wrap mt-5 hidden md:block">
                <table class="min-w-full text-left text-sm">
                    <caption class="sr-only">{{ $report['title'] }}</caption>
                    <thead class="admin-table-head">
                        <tr>
                            @foreach ($headings as $heading)
                                <th scope="col" class="px-4 py-3">{{ $heading }}</th>
                            @endforeach
                            @if ($filters->reportType === 'finance')
                                <th scope="col" class="px-4 py-3">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @foreach ($rows as $row)
                            <tr class="admin-table-row">
                                @foreach ($headings as $index => $heading)
                                    <td class="admin-table-cell">{{ $row[$index] ?? '-' }}</td>
                                @endforeach
                                @if ($filters->reportType === 'finance')
                                    <td class="admin-table-cell">
                                        @if ($row['invoice_url'] ?? null)
                                            <a href="{{ $row['invoice_url'] }}" class="owner-button-secondary">Invoice</a>
                                        @else
                                            <span class="owner-status-pill">Belum ada</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="owner-panel mt-5 text-center">Belum ada data laporan pada filter ini.</div>
        @endif

        @if ($rows->hasPages())
            <nav class="mt-5 flex flex-wrap items-center justify-between gap-3" aria-label="Pagination laporan owner">
                <p class="text-sm font-semibold text-zinc-500 dark:text-zinc-400">
                    Menampilkan {{ $rows->firstItem() }}-{{ $rows->lastItem() }} dari {{ $rows->total() }} data
                </p>
                <div class="flex flex-wrap gap-2">
                    @if ($rows->onFirstPage())
                        <span class="admin-pagination-disabled">Sebelumnya</span>
                    @else
                        <a href="{{ $rows->previousPageUrl() }}" class="admin-pagination-link">Sebelumnya</a>
                    @endif
                    @if ($rows->hasMorePages())
                        <a href="{{ $rows->nextPageUrl() }}" class="admin-pagination-link">Berikutnya</a>
                    @else
                        <span class="admin-pagination-disabled">Berikutnya</span>
                    @endif
                </div>
            </nav>
        @endif
    </section>
</x-owner-layout>
