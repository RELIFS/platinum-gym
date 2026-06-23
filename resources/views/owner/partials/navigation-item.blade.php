@php
    $activePatterns = (array) $item['active'];
    $isActive = request()->routeIs(...$activePatterns);
    $iconSize = $mobile ?? false ? 'h-5 w-5 shrink-0' : 'h-4 w-4';
@endphp

<a
    href="{{ route($item['route']) }}"
    @if ($isActive) aria-current="page" @endif
    data-owner-nav-route="{{ $item['route'] }}"
    data-owner-nav-active="{{ $isActive ? 'true' : 'false' }}"
    class="group admin-nav-link {{ $isActive ? 'admin-nav-link-active' : '' }}"
    @if ($mobile ?? false) x-on:click="closeOwnerMenu()" @endif
>
    @if ($mobile ?? false)
        @include('admin.partials.icon', ['name' => $item['icon'], 'class' => $iconSize])
    @else
        <span class="admin-nav-icon {{ $isActive ? 'admin-nav-icon-active' : '' }}">
            @include('admin.partials.icon', ['name' => $item['icon'], 'class' => $iconSize])
        </span>
    @endif
    <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
</a>
