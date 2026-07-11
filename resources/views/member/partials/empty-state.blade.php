<div class="member-soft-panel text-center {{ $class ?? '' }}">
    @include('member.partials.icon', ['name' => $icon ?? 'empty', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
    <p class="mt-3 type-control text-zinc-950 dark:text-zinc-100">{{ $title ?? 'Belum ada data' }}</p>
    @if (filled($body ?? null))
        <p class="mt-1 member-copy">{{ $body }}</p>
    @endif
</div>
