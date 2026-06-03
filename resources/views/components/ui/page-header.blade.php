@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<header {{ $attributes->class(['max-w-3xl']) }}>
    @if ($eyebrow)
        <p class="mb-2 text-xs font-black uppercase tracking-[0.22em] text-gold-600 dark:text-gold-400">{{ $eyebrow }}</p>
    @endif

    <h1 class="text-3xl font-black text-zinc-950 dark:text-white sm:text-4xl">{{ $title }}</h1>

    @if ($description)
        <p class="mt-3 text-sm font-medium leading-7 text-zinc-600 dark:text-zinc-400 sm:text-base">{{ $description }}</p>
    @endif
</header>
