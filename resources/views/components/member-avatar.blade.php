@props(['user' => null])

@php
    $displayName = (string) ($user?->name ?? 'Member');
    $initial = mb_strtoupper(mb_substr($displayName, 0, 1));
    $avatar = (string) ($user?->avatar ?? '');
    $avatarUrl = filled($avatar) ? (str_starts_with($avatar, 'storage/') ? asset($avatar) : $avatar) : null;
@endphp

<span {{ $attributes->class('grid shrink-0 place-items-center overflow-hidden rounded-full border border-gold-500/30 bg-gold-500 font-black text-zinc-950') }}>
    @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async">
    @else
        <span aria-hidden="true">{{ $initial }}</span>
    @endif
</span>
