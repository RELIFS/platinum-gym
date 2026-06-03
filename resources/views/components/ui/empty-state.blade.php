@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class(['rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-6 text-center dark:border-white/15 dark:bg-white/[0.03]']) }}>
    <h3 class="text-base font-black text-zinc-950 dark:text-white">{{ $title }}</h3>

    @if ($description)
        <p class="mx-auto mt-2 max-w-xl text-sm font-medium leading-6 text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif

    {{ $slot }}
</div>
