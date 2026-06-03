@props([
    'title',
    'description' => null,
    'actionHref' => null,
    'actionLabel' => null,
])

<div {{ $attributes->class(['rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-6 text-center dark:border-white/15 dark:bg-white/[0.03]']) }}>
    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gold-500/15 text-gold-600 dark:text-gold-400">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 7.5H18M6 12H18M6 16.5H13" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
    </div>
    <h3 class="text-base font-black text-zinc-950 dark:text-white">{{ $title }}</h3>

    @if ($description)
        <p class="mx-auto mt-2 max-w-xl text-sm font-medium leading-6 text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif

    @if ($actionHref && $actionLabel)
        <a href="{{ $actionHref }}" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-lg bg-gold-500 px-5 py-2.5 text-sm font-black text-zinc-950 transition hover:bg-gold-400 focus:outline-none focus:ring-2 focus:ring-gold-500/50 focus:ring-offset-2 focus:ring-offset-zinc-50 dark:focus:ring-offset-zinc-950">
            {{ $actionLabel }}
        </a>
    @endif
</div>
