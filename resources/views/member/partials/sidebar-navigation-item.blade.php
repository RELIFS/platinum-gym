@props([
    'item' => [],
    'mobile' => false,
    'href' => null,
    'label' => null,
    'icon' => null,
    'active' => null,
    'websiteLink' => null,
])

@php
    $activePattern = $active ?? ($item['active'] ?? null);
    $iconName = $icon ?? ($item['icon'] ?? 'circle');
    $isActive = $activePattern ? request()->routeIs($activePattern) : false;
    $linkHref = $href ?? (isset($item['route']) ? route($item['route']) : '#');
    $linkLabel = $label ?? ($item['label'] ?? '');
    $count = (int) ($item['count'] ?? 0);
    $badgeText = $count > 99 ? '99+' : (string) $count;
@endphp

<a
    href="{{ $linkHref }}"
    @if ($isActive) aria-current="page" @endif
    @if ($mobile) x-on:click="closeMemberMenu()" @endif
    @if ($websiteLink) data-member-website-link="{{ $websiteLink }}" data-member-website-placement="menu" @endif
    class="group member-sidebar-nav-link {{ $isActive ? 'member-sidebar-nav-link-active' : '' }}"
>
    <span class="member-sidebar-icon-frame {{ $isActive ? 'member-sidebar-icon-frame-active' : '' }}" data-member-sidebar-icon="{{ $iconName }}" aria-hidden="true">
        @include('member.partials.icon', ['name' => $iconName, 'class' => 'member-sidebar-icon-svg'])
    </span>
    <span class="min-w-0 flex-1 truncate">{{ $linkLabel }}</span>
    @if ($count > 0)
        <span class="rounded-full bg-gold-500 px-2 py-0.5 text-[0.65rem] type-control text-zinc-950">{{ $badgeText }}</span>
    @endif
</a>
