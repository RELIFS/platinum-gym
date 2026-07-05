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
    @if ($mobile) x-on:click="closeAdminMenu()" @endif
    @if ($websiteLink) data-admin-website-link="{{ $websiteLink }}" @endif
    class="group admin-sidebar-nav-link {{ $isActive ? 'admin-sidebar-nav-link-active' : '' }}"
>
    <span class="admin-sidebar-icon-frame {{ $isActive ? 'admin-sidebar-icon-frame-active' : '' }}" data-admin-sidebar-icon="{{ $iconName }}" aria-hidden="true">
        @include('admin.partials.icon', ['name' => $iconName, 'class' => 'admin-sidebar-icon-svg'])
    </span>
    <span class="min-w-0 flex-1 truncate">{{ $linkLabel }}</span>
    @if ($count > 0)
        <span class="rounded-full bg-gold-500 px-2 py-0.5 text-[0.65rem] font-black text-zinc-950" aria-label="{{ $count }} persetujuan menunggu review">{{ $badgeText }}</span>
    @endif
</a>
