@php
    $displayName = (string) ($owner?->name ?? 'Owner');
    $initial = mb_strtoupper(mb_substr($displayName, 0, 1));
    $avatar = (string) ($owner?->avatar ?? '');
    $avatarUrl = asset('images/owner/owner-avatar-default.webp');
    $avatarClass = trim('grid shrink-0 place-items-center overflow-hidden rounded-full border border-gold-500/40 bg-gold-500 font-black text-zinc-950 '.($class ?? 'h-10 w-10 text-sm'));

    if (filled($avatar)) {
        $avatarUrl = str_starts_with($avatar, 'storage/')
            ? asset($avatar)
            : $avatar;
    }
@endphp

<span class="{{ $avatarClass }}" @if ($ariaHidden ?? false) aria-hidden="true" @endif>
    @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async">
    @else
        <span aria-hidden="true">{{ $initial }}</span>
    @endif
</span>
