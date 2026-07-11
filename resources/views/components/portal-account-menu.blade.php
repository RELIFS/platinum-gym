@props([
    'name' => 'Akun',
    'email' => '',
    'avatarUrl' => null,
    'avatarFallback' => null,
    'profileUrl' => '#',
    'profileLabel' => 'Profil',
    'portal' => 'portal',
])

@php
    $portalKey = preg_replace('/[^a-z0-9_-]/i', '-', (string) $portal) ?: 'portal';
    $displayName = filled($name) ? (string) $name : 'Akun';
    $displayEmail = filled($email) ? (string) $email : 'Email belum tersedia';
    $fallback = filled($avatarFallback)
        ? (string) $avatarFallback
        : mb_strtoupper(mb_substr($displayName, 0, 1));
    $menuId = 'portal-account-dropdown-'.$portalKey;
@endphp

<div
    x-data="{ open: false }"
    x-on:keydown.escape.window="open = false"
    {{ $attributes->class('portal-account-menu') }}
    data-portal-account-menu="{{ $portalKey }}"
>
    <button
        type="button"
        class="portal-account-trigger group"
        x-on:click="open = ! open"
        x-bind:aria-expanded="open.toString()"
        aria-haspopup="menu"
        aria-controls="{{ $menuId }}"
        aria-label="Buka menu akun {{ $displayName }}"
        data-portal-account-trigger="{{ $portalKey }}"
    >
        <span class="portal-account-avatar" aria-hidden="true" data-portal-account-avatar="{{ $portalKey }}">
            @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async">
            @else
                <span class="portal-account-avatar-fallback">{{ $fallback }}</span>
            @endif
        </span>
        <span class="portal-account-text">
            <span class="portal-account-name">{{ $displayName }}</span>
            <span class="portal-account-email">{{ $displayEmail }}</span>
        </span>
        <svg class="h-4 w-4 shrink-0 text-zinc-400 transition group-hover:text-gold-text dark:text-zinc-500" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M6 8L10 12L14 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <div
        id="{{ $menuId }}"
        x-cloak
        x-show="open"
        x-transition.origin.top.right
        x-on:click.outside="open = false"
        class="portal-account-dropdown"
        role="menu"
        aria-label="Menu akun {{ $displayName }}"
        data-portal-account-dropdown="{{ $portalKey }}"
    >
        <div class="portal-account-summary">
            <span class="portal-account-avatar h-12 w-12 text-base" aria-hidden="true">
                @if ($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async">
                @else
                    <span class="portal-account-avatar-fallback">{{ $fallback }}</span>
                @endif
            </span>
            <span class="portal-account-text">
                <span class="portal-account-name text-sm">{{ $displayName }}</span>
                <span class="portal-account-email">{{ $displayEmail }}</span>
            </span>
        </div>

        <div class="py-2">
            <a
                href="{{ $profileUrl }}"
                class="portal-account-item"
                role="menuitem"
                x-on:click="open = false"
                data-portal-account-profile="{{ $portalKey }}"
            >
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 12.25a4.25 4.25 0 1 0 0-8.5 4.25 4.25 0 0 0 0 8.5Z" stroke="currentColor" stroke-width="1.8" />
                    <path d="M4.75 20.25a7.25 7.25 0 0 1 14.5 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
                <span>{{ $profileLabel }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" role="none" data-portal-account-logout-form="{{ $portalKey }}">
                @csrf
                <button type="submit" class="portal-account-logout" role="menuitem" data-portal-account-logout="{{ $portalKey }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M13.75 4.75h-6A2.75 2.75 0 0 0 5 7.5v9a2.75 2.75 0 0 0 2.75 2.75h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        <path d="M10.75 12h8.5m-3.25-3.25L19.25 12 16 15.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>
