@props([
    'label',
    'value' => '0',
    'description' => null,
])

<article {{ $attributes->class(['rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[0.04]']) }}>
    <p class="text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-300">{{ $label }}</p>
    <p class="mt-3 text-3xl type-emphasis text-zinc-950 dark:text-zinc-100">{{ $value }}</p>

    @if ($description)
        <p class="mt-2 text-sm type-compact leading-6 text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif
</article>
