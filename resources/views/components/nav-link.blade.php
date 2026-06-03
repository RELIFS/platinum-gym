@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-full bg-gold-500 px-3 py-2 text-sm font-bold text-zinc-950 transition focus:outline-none focus:ring-2 focus:ring-gold-500/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-zinc-950'
            : 'inline-flex items-center rounded-full px-3 py-2 text-sm font-bold text-zinc-600 transition hover:bg-zinc-200/70 hover:text-zinc-950 focus:outline-none focus:ring-2 focus:ring-gold-500/40 dark:text-zinc-300 dark:hover:bg-white/10 dark:hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
