@props([
    'variant' => 'primary',
])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-zinc-300 bg-white text-zinc-800 shadow-sm hover:border-gold-500/60 hover:text-gold-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-200 dark:hover:text-gold-400',
        'danger' => 'bg-red-600 text-white hover:bg-red-500',
        default => 'bg-gold-500 text-zinc-950 shadow-[0_10px_28px_rgba(254,172,24,0.22)] hover:bg-gold-400',
    };
@endphp

<button {{ $attributes->merge(['type' => 'button'])->class(['inline-flex min-h-11 items-center justify-center rounded-lg px-5 py-2.5 text-sm font-black transition focus:outline-none focus:ring-2 focus:ring-gold-500/50 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-zinc-950', $classes]) }}>
    {{ $slot }}
</button>
