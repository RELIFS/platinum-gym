@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->class(['rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-6']) }}>
    @if ($title || $description)
        <div class="mb-5">
            @if ($title)
                <h2 class="text-lg type-title text-zinc-950 dark:text-zinc-100">{{ $title }}</h2>
            @endif

            @if ($description)
                <p class="mt-1 text-sm type-compact leading-6 text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</section>
