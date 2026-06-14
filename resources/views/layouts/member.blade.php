@php
    $activeMembership = $portal['activeMembership'] ?? null;
    $unreadNotificationsCount = (int) ($portal['unreadNotificationsCount'] ?? 0);
    $navGroups = [
        [
            'label' => 'Utama',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'member.dashboard', 'active' => 'member.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Membership', 'route' => 'member.membership', 'active' => 'member.membership', 'icon' => 'card'],
                ['label' => 'QR Member', 'route' => 'member.qr', 'active' => 'member.qr', 'icon' => 'qr'],
            ],
        ],
        [
            'label' => 'Aktivitas',
            'items' => [
                ['label' => 'Booking Kelas', 'route' => 'member.booking', 'active' => 'member.booking', 'icon' => 'calendar'],
                ['label' => 'Riwayat Booking', 'route' => 'member.bookings', 'active' => 'member.bookings', 'icon' => 'calendar'],
                ['label' => 'Transaksi', 'route' => 'member.transactions', 'active' => 'member.transactions', 'icon' => 'receipt'],
                ['label' => 'Notifikasi', 'route' => 'member.notifications', 'active' => 'member.notifications', 'icon' => 'bell', 'count' => $unreadNotificationsCount],
            ],
        ],
        [
            'label' => 'Akun',
            'items' => [
                ['label' => 'Profil', 'route' => 'member.profile', 'active' => 'member.profile', 'icon' => 'user'],
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
                                        @php($isActive = request()->routeIs($item['active']))
                                        <a href="{{ route($item['route']) }}" @if ($isActive) aria-current="page" @endif class="group flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-bold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-zinc-950 {{ $isActive ? 'bg-zinc-950 text-white shadow-[inset_4px_0_0_0_#FEAC18] dark:bg-white/[0.09]' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-white/[0.07] dark:hover:text-white' }}">
                                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-md {{ $isActive ? 'bg-gold-500 text-zinc-950' : 'bg-zinc-100 text-zinc-500 group-hover:text-gold-600 dark:bg-white/[0.06] dark:text-zinc-400 dark:group-hover:text-gold-400' }}">
                                                @include('member.partials.icon', ['name' => $item['icon'], 'class' => 'h-4 w-4'])
                                            </span>
                                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                            @if (($item['count'] ?? 0) > 0)
                                                <span class="rounded-full bg-gold-500 px-2 py-0.5 text-[0.65rem] font-black text-zinc-950">{{ $item['count'] }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </div>

                <div class="border-t border-zinc-200 p-4 dark:border-white/10">
                    <div class="mb-3 grid grid-cols-2 gap-2">
                        <a href="{{ route('public.home') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-700 transition hover:border-gold-500/60 hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-white/[0.045] dark:text-zinc-200 dark:hover:text-gold-400">Website</a>
                        <a href="{{ route('profile.edit') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-700 transition hover:border-gold-500/60 hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-white/[0.045] dark:text-zinc-200 dark:hover:text-gold-400">Akun Login</a>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-zinc-950 px-4 text-sm font-black text-white transition hover:bg-zinc-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:bg-gold-500 dark:text-zinc-950 dark:hover:bg-gold-400 dark:focus-visible:ring-offset-zinc-950">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>

            <div x-cloak x-bind:class="memberMenuOpen ? 'block' : 'hidden'" class="fixed inset-0 z-50 bg-zinc-950/70 backdrop-blur-sm lg:hidden" x-on:click="closeMemberMenu()" aria-hidden="true"></div>
            <aside id="member-mobile-navigation" x-ref="memberMobilePanel" x-cloak x-bind:class="memberMenuOpen ? 'flex' : 'hidden'" class="fixed inset-y-0 left-0 z-[55] w-[86%] max-w-[20rem] flex-col border-r border-white/10 bg-zinc-950 text-white shadow-2xl lg:hidden" role="dialog" aria-modal="true" aria-label="Menu member mobile">
                <div class="flex min-h-16 items-center justify-between border-b border-white/10 px-4">
                    <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="brand-logo h-9 w-auto" width="480" height="112" draggable="false">
                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-white/[0.07] text-zinc-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50" x-on:click="closeMemberMenu()" aria-label="Tutup navigasi member">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <nav class="space-y-5" aria-label="Navigasi member mobile">
                        @foreach ($navGroups as $group)
                            <div>
                                <p class="mb-2 px-3 text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-500">{{ $group['label'] }}</p>
                                <div class="grid gap-1">
                                    @foreach ($group['items'] as $item)
                                        @php($isActive = request()->routeIs($item['active']))
                                        <a href="{{ route($item['route']) }}" @if ($isActive) aria-current="page" @endif class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-bold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 {{ $isActive ? 'bg-gold-500 text-zinc-950' : 'text-zinc-300 hover:bg-white/10 hover:text-white' }}" x-on:click="closeMemberMenu()">
                                            @include('member.partials.icon', ['name' => $item['icon'], 'class' => 'h-5 w-5 shrink-0'])
                                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                            @if (($item['count'] ?? 0) > 0)
                                                <span class="rounded-full bg-white/15 px-2 py-0.5 text-[0.65rem] font-black">{{ $item['count'] }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </div>

                <div class="border-t border-white/10 p-4">
                    <div class="grid gap-2">
                        <a href="{{ route('public.home') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-white/10 px-4 text-sm font-bold text-zinc-200">Website</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-gold-500 px-4 text-sm font-black text-zinc-950">Keluar</button>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="lg:pl-72">
                <header class="sticky top-0 z-30 border-b border-zinc-200 bg-zinc-50/90 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/90">
                    <div class="mx-auto flex min-h-16 w-full max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:min-h-20 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <button type="button" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-zinc-950 text-white shadow-[0_12px_28px_rgba(24,24,27,0.22)] transition hover:bg-zinc-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-50 dark:bg-gold-500 dark:text-zinc-950 lg:hidden" x-on:click="openMemberMenu()" x-bind:aria-expanded="memberMenuOpen.toString()" aria-controls="member-mobile-navigation" aria-label="Buka navigasi member">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M3 5.5H17M3 10H17M3 14.5H17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>
                            </button>
                            <div class="min-w-0">
                                <p class="text-[0.72rem] font-black uppercase tracking-[0.14em] text-gold-600 dark:text-gold-400">Member Area</p>
                                <h1 class="max-w-[9.5rem] break-words text-base font-black leading-tight text-zinc-950 dark:text-white sm:max-w-none sm:text-xl">{{ $title }}</h1>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-3">
                            @if ($activeMembership)
                                <span class="member-status-pill member-status-success hidden sm:inline-flex">Membership Aktif</span>
                            @else
                                <span class="member-status-pill member-status-warning hidden sm:inline-flex">Paket Belum Aktif</span>
                            @endif
                            <a href="{{ route('member.notifications') }}" class="relative inline-flex h-11 w-11 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-sm transition hover:border-gold-500/60 hover:text-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-300 dark:hover:text-gold-400" aria-label="Buka notifikasi member">
                                @include('member.partials.icon', ['name' => 'bell', 'class' => 'h-5 w-5'])
                                @if ($unreadNotificationsCount > 0)
                                    <span class="absolute right-1.5 top-1.5 h-2.5 w-2.5 rounded-full bg-gold-500 ring-2 ring-white dark:ring-zinc-950"></span>
                                @endif
                            </a>
                            <x-theme-toggle class="h-11 w-11" />
                        </div>
                    </div>
                </header>

                <main id="member-main" tabindex="-1" class="relative min-h-[calc(100dvh-4rem)] overflow-hidden lg:min-h-[calc(100dvh-5rem)]">
                    <div class="public-surface-grid absolute inset-0 opacity-20 dark:opacity-10" aria-hidden="true"></div>
                    <div class="relative mx-auto w-full max-w-7xl px-4 pb-28 pt-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
                        @if (session('status'))
                            <div class="mb-5 rounded-lg border border-gold-500/30 bg-gold-500/10 px-4 py-3 text-sm font-bold text-zinc-800 dark:text-gold-200" role="status" aria-live="polite">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-5 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-bold text-red-700 dark:text-red-200" role="alert" aria-live="assertive">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @include('member.partials.chatbot', ['portal' => $portal])
    </body>
</html>
