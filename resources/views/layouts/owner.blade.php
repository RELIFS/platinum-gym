@php
    $owner = $portal['owner'] ?? auth()->user();
    $ownerName = (string) ($owner?->name ?? 'Owner');
    $ownerEmail = (string) ($owner?->email ?? '');
    $ownerInitial = mb_strtoupper(mb_substr($ownerName, 0, 1));
    $ownerAvatar = (string) ($owner?->avatar ?? '');
    $ownerAvatarUrl = filled($ownerAvatar)
        ? (str_starts_with($ownerAvatar, 'storage/') ? asset($ownerAvatar) : $ownerAvatar)
        : asset('images/owner/owner-avatar-default.webp');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#09090b">

        @include('partials.head-brand', ['title' => $title.' | Owner Platinum Gym Padang'])
        @include('partials.theme-script')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#f8fafc] font-sans text-zinc-950 antialiased selection:bg-gold-500 selection:text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100">
        <a href="#owner-main" class="public-skip-link">Lewati navigasi owner</a>

        <div x-data="{ ownerMenuOpen: false, lastFocusedEl: null, syncOwnerMenuState() { window.platinumGymUi?.setMobileMenuOpen('owner-navigation', this.ownerMenuOpen); }, openOwnerMenu() { this.lastFocusedEl = document.activeElement; this.ownerMenuOpen = true; this.syncOwnerMenuState(); this.$nextTick(() => this.$refs.ownerMobilePanel?.querySelector('a[href], button:not([disabled])')?.focus()); }, closeOwnerMenu() { this.ownerMenuOpen = false; this.syncOwnerMenuState(); this.$nextTick(() => this.lastFocusedEl?.focus?.()); }, trapOwnerMenu(event) { if (! this.ownerMenuOpen || ! this.$refs.ownerMobilePanel) return; const focusable = Array.from(this.$refs.ownerMobilePanel.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex=&quot;-1&quot;])')).filter((el) => el.offsetParent !== null); if (! focusable.length) return; const first = focusable[0]; const last = focusable[focusable.length - 1]; if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); } else if (! event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); } } }" x-init="syncOwnerMenuState()" x-on:keydown.escape.window="ownerMenuOpen && closeOwnerMenu()" x-on:keydown.tab="trapOwnerMenu($event)" x-on:resize.window="if (window.innerWidth >= 1024 && ownerMenuOpen) closeOwnerMenu()" class="min-h-dvh bg-[#f8fafc] dark:bg-zinc-950">
            <div class="fixed inset-y-0 left-0 z-40 hidden w-72 border-r border-zinc-200 bg-white/95 shadow-[14px_0_48px_rgba(15,23,42,0.055)] backdrop-blur-xl print:hidden dark:border-white/10 dark:bg-zinc-950/95 lg:flex lg:flex-col">
                <div class="flex min-h-[4.5rem] items-center border-b border-zinc-200 px-5 dark:border-white/10">
                    <a href="{{ route('owner.dashboard') }}" class="inline-flex min-h-11 min-w-0 touch-manipulation items-center rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-gold-400/40 dark:focus-visible:ring-offset-zinc-950" aria-label="Dashboard owner Platinum Gym">
                        <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="brand-logo h-10 w-auto" width="480" height="112" draggable="false">
                    </a>
                </div>

                <div class="flex-1 overflow-y-auto px-4 py-5">
                    <nav class="space-y-5" aria-label="Navigasi owner">
                        @foreach ($navigation as $group)
                            <div>
                                <p class="mb-2 px-3 text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-500 dark:text-zinc-400">{{ $group['label'] }}</p>
                                <div class="space-y-1">
                                    @foreach ($group['items'] as $item)
                                        @include('owner.partials.navigation-item', ['item' => $item])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </div>
            </div>

            <div x-cloak x-bind:class="ownerMenuOpen ? 'block' : 'hidden'" class="fixed inset-0 z-50 bg-zinc-950/70 backdrop-blur-sm print:hidden lg:hidden" x-on:click="closeOwnerMenu()" aria-hidden="true"></div>
            <aside id="owner-mobile-navigation" x-ref="ownerMobilePanel" x-cloak x-bind:class="ownerMenuOpen ? 'flex' : 'hidden'" class="fixed inset-y-0 left-0 z-[55] w-[86%] max-w-[20rem] flex-col border-r border-zinc-200 bg-white text-zinc-950 shadow-2xl print:hidden dark:border-white/10 dark:bg-zinc-950 dark:text-zinc-100 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu owner mobile">
                <div class="flex min-h-16 items-center justify-between border-b border-zinc-200 px-4 dark:border-white/10">
                    <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="brand-logo h-9 w-auto" width="480" height="112" draggable="false">
                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 transition hover:bg-zinc-200 hover:text-zinc-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/50 dark:bg-white/[0.07] dark:text-zinc-300 dark:hover:bg-white/10 dark:hover:text-zinc-100 dark:focus-visible:ring-gold-400/45" x-on:click="closeOwnerMenu()" aria-label="Tutup navigasi owner">
                        @include('admin.partials.icon', ['name' => 'close', 'class' => 'h-5 w-5'])
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <nav class="space-y-5" aria-label="Navigasi owner mobile">
                        @foreach ($navigation as $group)
                            <div>
                                <p class="mb-2 px-3 text-[0.72rem] type-control uppercase tracking-[0.11em] text-zinc-500 dark:text-zinc-400">{{ $group['label'] }}</p>
                                <div class="grid gap-1">
                                    @foreach ($group['items'] as $item)
                                        @include('owner.partials.navigation-item', ['item' => $item, 'mobile' => true])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </div>

                @include('owner.partials.sidebar-footer', ['owner' => $owner, 'ownerName' => $ownerName, 'mobile' => true])
            </aside>

            <div class="lg:pl-72">
                <header data-auto-hide-topbar data-auto-hide-scope="below-lg" class="sticky top-0 z-30 border-b border-zinc-200 bg-[#f8fafc]/90 backdrop-blur-xl print:hidden dark:border-white/10 dark:bg-zinc-950/90">
                    <div class="mx-auto flex min-h-16 w-full max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:min-h-20 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <button type="button" class="owner-mobile-menu-button lg:hidden" x-on:click="openOwnerMenu()" x-bind:aria-expanded="ownerMenuOpen.toString()" aria-controls="owner-mobile-navigation" aria-label="Buka navigasi owner">
                                @include('admin.partials.icon', ['name' => 'menu', 'class' => 'h-5 w-5'])
                            </button>
                            <h1 class="min-w-0 max-w-[14rem] break-words text-base type-title leading-tight text-zinc-950 dark:text-zinc-100 sm:max-w-none sm:text-xl">{{ $title }}</h1>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-3">
                            <x-theme-toggle class="h-11 w-11" />
                            <x-portal-account-menu
                                portal="owner"
                                :name="$ownerName"
                                :email="$ownerEmail"
                                :avatar-url="$ownerAvatarUrl"
                                :avatar-fallback="$ownerInitial"
                                :profile-url="route('profile.edit')"
                                profile-label="Profil"
                            />
                        </div>
                    </div>
                </header>

                <main id="owner-main" tabindex="-1" class="relative min-h-[calc(100dvh-4rem)] overflow-x-clip print:min-h-0 print:overflow-visible lg:min-h-[calc(100dvh-5rem)]">
                    <div class="public-surface-grid absolute inset-0 opacity-20 print:hidden dark:opacity-10" aria-hidden="true"></div>
                    <div class="relative mx-auto w-full max-w-7xl px-4 pb-16 pt-6 print:max-w-none print:px-0 print:py-0 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
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
    </body>
</html>
