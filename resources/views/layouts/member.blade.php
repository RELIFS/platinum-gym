@php
    $activeMembership = $portal['activeMembership'] ?? null;
    $unreadNotificationsCount = (int) ($portal['unreadNotificationsCount'] ?? 0);
    $portalUser = $portal['user'] ?? auth()->user();
    $portalMember = $portal['member'] ?? null;
    $memberDisplayName = (string) ($portalUser->name ?? 'Member');
    $memberCode = (string) ($portalMember->member_code ?? '-');
    $unreadBadgeText = $unreadNotificationsCount > 99 ? '99+' : (string) $unreadNotificationsCount;
    $headerStatusLabel = $activeMembership ? 'Membership Aktif' : 'Paket Belum Aktif';
    $headerStatusShort = $activeMembership ? 'Aktif' : 'Belum Aktif';
    $headerStatusClass = $activeMembership ? 'member-status-success' : 'member-status-warning';
    $navGroups = [
        [
            'label' => 'Utama',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'member.dashboard', 'active' => 'member.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Membership', 'route' => 'member.membership', 'active' => 'member.membership', 'icon' => 'membership-card'],
                ['label' => 'QR Member', 'route' => 'member.qr', 'active' => 'member.qr', 'icon' => 'qr-scan'],
            ],
        ],
        [
            'label' => 'Aktivitas',
            'items' => [
                ['label' => 'Booking Kelas', 'route' => 'member.booking', 'active' => 'member.booking', 'icon' => 'calendar-check'],
                ['label' => 'Riwayat Booking', 'route' => 'member.bookings', 'active' => 'member.bookings', 'icon' => 'history'],
                ['label' => 'Transaksi', 'route' => 'member.transactions', 'active' => 'member.transactions', 'icon' => 'receipt'],
                ['label' => 'Notifikasi', 'route' => 'member.notifications', 'active' => 'member.notifications', 'icon' => 'bell', 'count' => $unreadNotificationsCount],
            ],
        ],
        [
            'label' => 'Akun',
            'items' => [
                ['label' => 'Profil', 'route' => 'member.profile', 'active' => 'member.profile*', 'icon' => 'user'],
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#09090b">

        @include('partials.head-brand', ['title' => $title.' | Platinum Gym Padang'])

        @include('partials.theme-script')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-zinc-50 font-sans text-zinc-950 antialiased selection:bg-gold-500 selection:text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100">
        <a href="#member-main" class="public-skip-link">Lewati navigasi member</a>

        <div x-data="{ memberMenuOpen: false, lastFocusedEl: null, openMemberMenu() { this.lastFocusedEl = document.activeElement; this.memberMenuOpen = true; this.$nextTick(() => this.$refs.memberMobilePanel?.querySelector('a[href], button:not([disabled])')?.focus()); }, closeMemberMenu() { this.memberMenuOpen = false; this.$nextTick(() => this.lastFocusedEl?.focus?.()); }, trapMemberMenu(event) { if (! this.memberMenuOpen || ! this.$refs.memberMobilePanel) return; const focusable = Array.from(this.$refs.memberMobilePanel.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex=&quot;-1&quot;])')).filter((el) => el.offsetParent !== null); if (! focusable.length) return; const first = focusable[0]; const last = focusable[focusable.length - 1]; if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); } else if (! event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); } } }" x-on:keydown.escape.window="memberMenuOpen && closeMemberMenu()" x-on:keydown.tab="trapMemberMenu($event)" class="min-h-dvh bg-zinc-50 dark:bg-zinc-950">
            <div class="fixed inset-y-0 left-0 z-40 hidden w-72 border-r border-zinc-200 bg-white/95 shadow-[18px_0_70px_rgba(24,24,27,0.06)] backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/95 lg:flex lg:flex-col">
                <div class="flex min-h-[4.5rem] items-center border-b border-zinc-200 px-5 dark:border-white/10">
                    <a href="{{ route('member.dashboard') }}" class="inline-flex min-w-0 items-center rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-zinc-950" aria-label="Dashboard member Platinum Gym">
                        <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="brand-logo h-10 w-auto" width="480" height="112" draggable="false">
                    </a>
                </div>

                <div class="flex-1 overflow-y-auto px-4 py-5">
                    <nav class="space-y-5" aria-label="Navigasi member">
                        @foreach ($navGroups as $group)
                            <div>
                                <p class="mb-2 px-3 text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">{{ $group['label'] }}</p>
                                <div class="space-y-1">
                                    @foreach ($group['items'] as $item)
                                        @include('member.partials.sidebar-navigation-item', ['item' => $item])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="border-t border-zinc-200 pt-5 dark:border-white/10">
                            @include('member.partials.sidebar-navigation-item', [
                                'item' => [],
                                'href' => route('public.home'),
                                'label' => 'Website Utama',
                                'icon' => 'globe',
                                'websiteLink' => 'desktop',
                            ])
                        </div>
                    </nav>
                </div>

                <div class="border-t border-zinc-200 p-4 dark:border-white/10">
                    <div class="mb-3 flex items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/[0.04]" aria-label="Identitas member">
                        <x-member-avatar :user="$portalUser" class="h-10 w-10 text-sm" aria-hidden="true" />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-zinc-950 dark:text-white">{{ $memberDisplayName }}</p>
                            <p class="mt-0.5 truncate font-mono text-[0.7rem] font-bold uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">{{ $memberCode }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="member-button-secondary w-full">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>

            <div x-cloak x-bind:class="memberMenuOpen ? 'block' : 'hidden'" class="fixed inset-0 z-50 bg-zinc-900/25 backdrop-blur-sm dark:bg-zinc-950/70 lg:hidden" x-on:click="closeMemberMenu()" aria-hidden="true"></div>
            <aside id="member-mobile-navigation" x-ref="memberMobilePanel" x-cloak x-bind:class="memberMenuOpen ? 'flex' : 'hidden'" class="fixed inset-y-0 left-0 z-[55] w-[88%] max-w-[20rem] flex-col border-r border-zinc-200 bg-white text-zinc-950 shadow-[18px_0_60px_rgba(24,24,27,0.10)] dark:border-white/10 dark:bg-zinc-950 dark:text-white lg:hidden" role="dialog" aria-modal="true" aria-label="Menu member mobile">
                <div class="flex min-h-16 items-center justify-between border-b border-zinc-200 px-4 dark:border-white/10">
                    <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="brand-logo h-9 w-auto" width="480" height="112" draggable="false">
                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 transition hover:bg-zinc-200 hover:text-zinc-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 dark:bg-white/[0.07] dark:text-zinc-300 dark:hover:bg-white/10 dark:hover:text-white" x-on:click="closeMemberMenu()" aria-label="Tutup navigasi member">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <nav class="space-y-5" aria-label="Navigasi member mobile">
                        @foreach ($navGroups as $group)
                            <div>
                                <p class="mb-2 px-3 text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-500">{{ $group['label'] }}</p>
                                <div class="grid gap-1">
                                    @foreach ($group['items'] as $item)
                                        @include('member.partials.sidebar-navigation-item', ['item' => $item, 'mobile' => true])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="border-t border-zinc-200 pt-5 dark:border-white/10">
                            @include('member.partials.sidebar-navigation-item', [
                                'item' => [],
                                'mobile' => true,
                                'href' => route('public.home'),
                                'label' => 'Website Utama',
                                'icon' => 'globe',
                                'websiteLink' => 'mobile',
                            ])
                        </div>
                    </nav>
                </div>

                <div class="border-t border-zinc-200 p-4 dark:border-white/10">
                    <div class="mb-3 flex items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/[0.04]" aria-label="Identitas member">
                        <x-member-avatar :user="$portalUser" class="h-10 w-10 text-sm" aria-hidden="true" />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-zinc-950 dark:text-white">{{ $memberDisplayName }}</p>
                            <p class="mt-0.5 truncate font-mono text-[0.7rem] font-bold uppercase tracking-[0.1em] text-zinc-500 dark:text-zinc-400">{{ $memberCode }}</p>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="member-button-secondary w-full">Keluar</button>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="lg:pl-72">
                <header class="sticky top-0 z-30 border-b border-zinc-200 bg-zinc-50/90 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/90">
                    <div class="mx-auto flex min-h-16 w-full max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:min-h-20 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <button type="button" class="member-mobile-menu-button lg:hidden" x-on:click="openMemberMenu()" x-bind:aria-expanded="memberMenuOpen.toString()" aria-controls="member-mobile-navigation" aria-label="Buka navigasi member">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M3 5.5H17M3 10H17M3 14.5H17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>
                            </button>
                            <div class="min-w-0">

                                <h1 class="max-w-[7rem] break-words text-base font-black leading-tight text-zinc-950 dark:text-white min-[360px]:max-w-[9.5rem] sm:max-w-none sm:text-xl">{{ $title }}</h1>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-3">
                            <span class="member-status-pill {{ $headerStatusClass }} hidden sm:inline-flex" aria-label="Status membership: {{ $headerStatusLabel }}">{{ $headerStatusLabel }}</span>
                            <span class="member-status-pill {{ $headerStatusClass }} hidden min-[360px]:inline-flex sm:hidden" aria-label="Status membership: {{ $headerStatusLabel }}" title="{{ $headerStatusLabel }}">{{ $headerStatusShort }}</span>
                            <a href="{{ route('member.notifications') }}" class="relative inline-flex h-11 min-w-11 items-center justify-center rounded-full border border-zinc-200 bg-white px-2 text-zinc-700 shadow-sm transition hover:border-gold-500/60 hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-300 dark:hover:text-gold-400" aria-label="Buka notifikasi member, {{ $unreadNotificationsCount }} belum dibaca">
                                @include('member.partials.icon', ['name' => 'bell', 'class' => 'h-5 w-5'])
                                @if ($unreadNotificationsCount > 0)
                                    <span class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-gold-500 px-1 text-[0.65rem] font-black leading-none text-zinc-950 ring-2 ring-white dark:ring-zinc-950" aria-hidden="true">{{ $unreadBadgeText }}</span>
                                    <span class="sr-only">{{ $unreadNotificationsCount }} notifikasi belum dibaca</span>
                                @endif
                            </a>
                            <x-theme-toggle class="h-11 w-11" />
                        </div>
                    </div>
                </header>

                <main id="member-main" tabindex="-1" class="relative min-h-[calc(100dvh-4rem)] overflow-hidden lg:min-h-[calc(100dvh-5rem)]">
                    <div class="public-surface-grid absolute inset-0 opacity-20 dark:opacity-10" aria-hidden="true"></div>
                    <div class="relative mx-auto w-full max-w-7xl px-4 pb-28 pt-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
                        @include('partials.flash-banner', [
                            'message' => session('status'),
                            'errorMessage' => $errors->any() ? $errors->first() : '',
                            'kind' => session('status_kind', 'success'),
                        ])

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @include('member.partials.chatbot', ['portal' => $portal])
    </body>
</html>
