@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
])

<section {{ $attributes->class(['py-8 sm:py-10']) }}>
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        @if ($title || $description || $eyebrow)
            <div class="mb-8 max-w-3xl">
                @if ($eyebrow)
                    <p class="mb-2 text-xs font-black uppercase tracking-[0.22em] text-gold-600 dark:text-gold-400">{{ $eyebrow }}</p>
                @endif

                @if ($title)
                    <h1 class="text-3xl font-black text-zinc-950 dark:text-white sm:text-4xl">{{ $title }}</h1>
                @endif

                @if ($description)
                    <p class="mt-3 text-sm font-medium leading-7 text-zinc-600 dark:text-zinc-400 sm:text-base">{{ $description }}</p>
                @endif
            </div>
        @endif

        {{ $slot }}
    </div>
</section>
