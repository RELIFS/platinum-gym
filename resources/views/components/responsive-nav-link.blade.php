@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg bg-gold-500 px-4 py-3 text-start text-sm font-bold text-zinc-950 transition focus:outline-none focus:ring-2 focus:ring-gold-500/50'
            : 'block w-full rounded-lg px-4 py-3 text-start text-sm font-bold text-zinc-700 transition hover:bg-zinc-100 hover:text-zinc-950 focus:outline-none focus:ring-2 focus:ring-gold-500/40 dark:text-zinc-200 dark:hover:bg-white/10 dark:hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
