@props([
    'tone' => 'default',
])

@php
    $classes = match ($tone) {
        'success' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
        'danger' => 'bg-red-500/10 text-red-700 dark:text-red-400',
        'gold' => 'bg-gold-500/15 text-gold-700 dark:text-gold-400',
        default => 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-300',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-3 py-1 text-xs font-black uppercase tracking-[0.14em]', $classes]) }}>
    {{ $slot }}
</span>
