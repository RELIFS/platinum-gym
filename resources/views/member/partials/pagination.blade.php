@php
    $hasPaginator = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $label = $label ?? 'data member';
    $countText = $hasPaginator
        ? match (true) {
            $paginator->total() === 0 => '0 data',
            filled($paginator->firstItem()) => 'Menampilkan '.$paginator->firstItem().'-'.$paginator->lastItem().' dari '.$paginator->total().' data',
            default => 'Tidak ada data pada halaman ini dari '.$paginator->total().' data',
        }
        : null;
@endphp

@if ($hasPaginator)
    <div class="mt-5 flex flex-col gap-3 border-t border-zinc-200 pt-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm font-bold text-zinc-500 dark:text-zinc-400">{{ $countText }}</p>

        @if ($paginator->hasPages())
            @php
                $lastPage = max(1, $paginator->lastPage());
                $currentPage = min(max(1, $paginator->currentPage()), $lastPage);
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
            @endphp
            <nav class="flex flex-wrap gap-2" aria-label="Navigasi halaman {{ $label }}">
                @if ($paginator->onFirstPage())
                    <span class="member-button-secondary min-h-11 opacity-45" aria-disabled="true">Sebelumnya</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="member-button-secondary min-h-11" aria-label="Halaman sebelumnya">Sebelumnya</a>
                @endif

                @foreach (range($startPage, $endPage) as $pageNumber)
                    @if ($pageNumber === $currentPage)
                        <span class="grid min-h-11 min-w-11 place-items-center rounded-lg bg-gold-500 px-3 text-sm font-black text-zinc-950" aria-current="page">{{ $pageNumber }}</span>
                    @else
                        <a href="{{ $paginator->url($pageNumber) }}" class="grid min-h-11 min-w-11 place-items-center rounded-lg border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-700 transition hover:border-gold-500/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-zinc-950/45 dark:text-zinc-200" aria-label="Halaman {{ $pageNumber }}">{{ $pageNumber }}</a>
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="member-button-secondary min-h-11" aria-label="Halaman berikutnya">Berikutnya</a>
                @else
                    <span class="member-button-secondary min-h-11 opacity-45" aria-disabled="true">Berikutnya</span>
                @endif
            </nav>
        @endif
    </div>
@endif
