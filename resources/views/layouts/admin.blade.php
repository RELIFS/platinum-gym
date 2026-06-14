@php
    $admin = $portal['admin'] ?? auth()->user();
    $pendingPaymentCount = data_get(collect($portal['stats'] ?? [])->firstWhere('label', 'Pembayaran Pending'), 'value', '0');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#09090b">

        @include('partials.head-brand', ['title' => $title.' | Admin Platinum Gym Padang'])
        @include('partials.theme-script')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-zinc-50 font-sans text-zinc-950 antialiased selection:bg-gold-500 selection:text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100">
        <a href="#admin-main" class="public-skip-link">Lewati navigasi admin</a>

        <div x-data="{ adminMenuOpen: false, lastFocusedEl: null, openAdminMenu() { this.lastFocusedEl = document.activeElement; this.adminMenuOpen = true; this.$nextTick(() => this.$refs.adminMobilePanel?.querySelector('a[href], button:not([disabled])')?.focus()); }, closeAdminMenu() { this.adminMenuOpen = false; this.$nextTick(() => this.lastFocusedEl?.focus?.()); }, trapAdminMenu(event) { if (! this.adminMenuOpen || ! this.$refs.adminMobilePanel) return; const focusable = Array.from(this.$refs.adminMobilePanel.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex=&quot;-1&quot;])')).filter((el) => el.offsetParent !== null); if (! focusable.length) return; const first = focusable[0]; const last = focusable[focusable.length - 1]; if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); } else if (! event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); } } }" x-on:keydown.escape.window="adminMenuOpen && closeAdminMenu()" x-on:keydown.tab="trapAdminMenu($event)" class="min-h-dvh bg-zinc-50 dark:bg-zinc-950">
            <div class="fixed inset-y-0 left-0 z-40 hidden w-72 border-r border-zinc-200 bg-white/95 shadow-[18px_0_70px_rgba(24,24,27,0.06)] backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/95 lg:flex lg:flex-col">
                <div class="flex min-h-[4.5rem] items-center border-b border-zinc-200 px-5 dark:border-white/10">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex min-w-0 items-center rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-zinc-950" aria-label="Dashboard admin Platinum Gym">
                        <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="brand-logo h-10 w-auto" width="480" height="112" draggable="false">
                    </a>
                </div>

                <div class="flex-1 overflow-y-auto px-4 py-5">
                    <nav class="space-y-5" aria-label="Navigasi admin">
                        @foreach ($navigation as $group)
                            <div>
                                <p class="mb-2 px-3 text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">{{ $group['label'] }}</p>
                                <div class="space-y-1">
                                    @foreach ($group['items'] as $item)
                                        @php($isActive = request()->routeIs($item['active']))
                                        <a href="{{ route($item['route']) }}" @if ($isActive) aria-current="page" @endif class="group flex min-h-11 items-center gap-3 rounded-lg border border-transparent px-3 py-2.5 text-sm font-bold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-zinc-950 {{ $isActive ? 'border-gold-500/35 bg-gold-500/10 text-zinc-950 shadow-sm dark:bg-gold-500/10 dark:text-gold-400' : 'text-zinc-600 hover:border-zinc-200 hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-300 dark:hover:border-white/10 dark:hover:bg-white/[0.07] dark:hover:text-white' }}">
                                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-md {{ $isActive ? 'bg-gold-500/15 text-gold-600 dark:bg-gold-500/15 dark:text-gold-400' : 'bg-zinc-100 text-zinc-500 group-hover:text-gold-600 dark:bg-white/[0.06] dark:text-zinc-400 dark:group-hover:text-gold-400' }}">
                                                @include('admin.partials.icon', ['name' => $item['icon'], 'class' => 'h-4 w-4'])
                                            </span>
                                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </div>

                <div class="border-t border-zinc-200 p-4 dark:border-white/10">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="admin-button-primary w-full">Keluar</button>
                    </form>
                </div>
            </div>

            <div x-cloak x-bind:class="adminMenuOpen ? 'block' : 'hidden'" class="fixed inset-0 z-50 bg-zinc-950/70 backdrop-blur-sm lg:hidden" x-on:click="closeAdminMenu()" aria-hidden="true"></div>
            <aside id="admin-mobile-navigation" x-ref="adminMobilePanel" x-cloak x-bind:class="adminMenuOpen ? 'flex' : 'hidden'" class="fixed inset-y-0 left-0 z-[55] w-[86%] max-w-[20rem] flex-col border-r border-white/10 bg-zinc-950 text-white shadow-2xl lg:hidden" role="dialog" aria-modal="true" aria-label="Menu admin mobile">
                <div class="flex min-h-16 items-center justify-between border-b border-white/10 px-4">
                    <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="brand-logo h-9 w-auto" width="480" height="112" draggable="false">
                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-white/[0.07] text-zinc-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50" x-on:click="closeAdminMenu()" aria-label="Tutup navigasi admin">
                        @include('admin.partials.icon', ['name' => 'close', 'class' => 'h-5 w-5'])
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <nav class="space-y-5" aria-label="Navigasi admin mobile">
                        @foreach ($navigation as $group)
                            <div>
                                <p class="mb-2 px-3 text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-500">{{ $group['label'] }}</p>
                                <div class="grid gap-1">
                                    @foreach ($group['items'] as $item)
                                        @php($isActive = request()->routeIs($item['active']))
                                        <a href="{{ route($item['route']) }}" @if ($isActive) aria-current="page" @endif class="flex min-h-11 items-center gap-3 rounded-lg border border-transparent px-3 py-2.5 text-sm font-bold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 {{ $isActive ? 'border-gold-500/35 bg-gold-500/10 text-gold-400' : 'text-zinc-300 hover:border-white/10 hover:bg-white/10 hover:text-white' }}" x-on:click="closeAdminMenu()">
                                            @include('admin.partials.icon', ['name' => $item['icon'], 'class' => 'h-5 w-5 shrink-0'])
                                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </div>

                <div class="border-t border-white/10 p-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-gold-500 px-4 text-sm font-black text-zinc-950">Keluar</button>
                    </form>
                </div>
            </aside>

            <div class="lg:pl-72">
                <header class="sticky top-0 z-30 border-b border-zinc-200 bg-zinc-50/90 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/90">
                    <div class="mx-auto flex min-h-16 w-full max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:min-h-20 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <button type="button" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-zinc-950 text-white shadow-[0_12px_28px_rgba(24,24,27,0.22)] transition hover:bg-zinc-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-50 dark:bg-gold-500 dark:text-zinc-950 lg:hidden" x-on:click="openAdminMenu()" x-bind:aria-expanded="adminMenuOpen.toString()" aria-controls="admin-mobile-navigation" aria-label="Buka navigasi admin">
                                @include('admin.partials.icon', ['name' => 'menu', 'class' => 'h-5 w-5'])
                            </button>
                            <h1 class="min-w-0 max-w-[14rem] break-words text-base font-black leading-tight text-zinc-950 dark:text-white sm:max-w-none sm:text-xl">{{ $title }}</h1>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-3">
                            <a href="{{ route('admin.payments') }}" class="hidden rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs font-black text-amber-700 transition hover:border-amber-500/50 hover:bg-amber-500/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/40 dark:text-amber-300 sm:inline-flex">{{ $pendingPaymentCount }} menunggu</a>
                            <span class="hidden max-w-[12rem] truncate text-sm font-bold text-zinc-600 dark:text-zinc-300 md:inline">{{ $admin?->name }}</span>
                            <x-theme-toggle class="h-11 w-11" />
                        </div>
                    </div>
                </header>

                <main id="admin-main" tabindex="-1" class="relative min-h-[calc(100dvh-4rem)] overflow-hidden lg:min-h-[calc(100dvh-5rem)]">
                    <div class="public-surface-grid absolute inset-0 opacity-20 dark:opacity-10" aria-hidden="true"></div>
                    <div class="relative mx-auto w-full max-w-7xl px-4 pb-16 pt-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
                        @if (session('status'))
                            <div class="mb-5 rounded-lg border border-gold-500/30 bg-gold-500/10 px-4 py-3 text-sm font-bold text-zinc-800 dark:text-gold-200" role="status">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-5 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-bold text-red-700 dark:text-red-200" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
