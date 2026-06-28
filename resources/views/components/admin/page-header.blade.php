@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'admin-page-header']) }}>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 max-w-3xl">
            @if (filled($eyebrow))
                <p class="admin-eyebrow">{{ $eyebrow }}</p>
            @endif
            <h2 class="admin-title {{ filled($eyebrow) ? 'mt-3' : '' }}">{{ $title }}</h2>
            @if (filled($description))
                <p class="mt-3 admin-copy">{{ $description }}</p>
            @endif
        </div>

        @if (isset($actions) && filled(trim((string) $actions)))
            <div class="flex shrink-0 flex-col-reverse gap-2 sm:flex-row">
                {{ $actions }}
            </div>
        @endif
    </div>
</section>
