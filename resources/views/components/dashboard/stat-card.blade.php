@props([
    'label',
    'value' => '0',
    'description' => null,
])

<article {{ $attributes->class(['rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[0.04]']) }}>
    <p class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ $label }}</p>
    <p class="mt-3 text-3xl font-black text-zinc-950 dark:text-white">{{ $value }}</p>

    @if ($description)
        <p class="mt-2 text-sm font-medium leading-6 text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif
</article>
