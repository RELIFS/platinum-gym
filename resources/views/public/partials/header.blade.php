@php
    $siteName = $settings['site_name'] ?? 'Platinum Gym Padang';
    $navItems = [
        ['label' => 'Beranda', 'route' => 'public.home'],
        ['label' => 'Tentang', 'route' => 'public.about'],
        ['label' => 'Layanan', 'route' => 'public.services'],
        ['label' => 'Kelas', 'route' => 'public.classes'],
        ['label' => 'Produk', 'route' => 'public.products'],
        ['label' => 'Galeri', 'route' => 'public.gallery'],
        ['label' => 'Lokasi', 'route' => 'public.location'],
        ['label' => 'BMI', 'route' => 'public.bmi'],
    ];
@endphp

<header class="sticky top-0 z-50 border-b border-zinc-200/80 bg-zinc-50/88 shadow-[0_10px_32px_rgba(24,24,27,0.06)] backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/88 dark:shadow-[0_10px_32px_rgba(0,0,0,0.28)]" x-data="{ open: false }">
    <div class="public-container">
        <div class="flex min-h-20 items-center justify-between gap-4">
            <a href="{{ route('public.home') }}" class="inline-flex min-h-11 touch-manipulation items-center rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-50 dark:focus-visible:ring-offset-zinc-950" aria-label="{{ $siteName }}">
                <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="{{ $siteName }}" class="brand-logo h-10 w-auto sm:h-11" draggable="false" width="480" height="112">
            </a>

            <nav class="hidden items-center gap-1 rounded-full border border-zinc-200/80 bg-white/55 p-1 shadow-sm backdrop-blur xl:flex dark:border-white/10 dark:bg-white/[0.035]" aria-label="Navigasi utama">
                @foreach ($navItems as $item)
                    @php($isActive = request()->routeIs($item['route']))
                    <a href="{{ route($item['route']) }}" @if ($isActive) aria-current="page" @endif class="inline-flex min-h-11 touch-manipulation items-center rounded-full px-3 py-2 text-sm font-bold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-50 dark:focus-visible:ring-offset-zinc-950 {{ $isActive ? 'bg-gold-500 text-zinc-950 shadow-[0_10px_24px_rgba(254,172,24,0.22)]' : 'text-zinc-600 hover:bg-zinc-200/70 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-white/10 dark:hover:text-white' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-3 xl:flex">
                <x-theme-toggle />
                @auth
                    <a href="{{ route('dashboard') }}" class="public-header-link">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="public-header-link">Masuk</a>
                    <a href="{{ route('register') }}" class="public-header-cta" aria-label="Daftar sebagai member Platinum Gym">
                        <span>Daftar Member</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M4 10H15M11 5L16 10L11 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                @endauth
            </div>

            <div class="flex items-center gap-2 xl:hidden">
                <x-theme-toggle />
                <button type="button" class="public-mobile-menu-button" x-on:click="open = !open" x-bind:aria-expanded="open.toString()" aria-controls="mobile-navigation" x-bind:aria-label="open ? 'Tutup navigasi' : 'Buka navigasi'">
                    <svg x-show="!open" class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M3 5.5H17M3 10H17M3 14.5H17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                    <svg x-cloak x-show="open" class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
        </div>

        <div id="mobile-navigation" class="max-h-[calc(100dvh-6rem)] overflow-y-auto overscroll-contain pb-5 xl:hidden" x-cloak x-show="open" x-transition>
            <nav class="grid gap-2 rounded-2xl border border-zinc-200 bg-white p-3 shadow-2xl dark:border-white/10 dark:bg-zinc-900" aria-label="Navigasi mobile">
                @foreach ($navItems as $item)
                    @php($isActive = request()->routeIs($item['route']))
                    <a href="{{ route($item['route']) }}" @if ($isActive) aria-current="page" @endif class="touch-manipulation rounded-xl px-4 py-3 text-sm font-bold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-zinc-900 {{ $isActive ? 'bg-gold-500 text-zinc-950' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-white/10' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <div class="grid gap-2 pt-2 sm:grid-cols-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="public-button-primary">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="public-button-secondary">Masuk</a>
                        <a href="{{ route('register') }}" class="public-button-primary" aria-label="Daftar sebagai member Platinum Gym">Daftar Member</a>
                    @endauth
                    <a href="{{ route('public.location') }}" class="public-button-secondary sm:col-span-2">Lokasi &amp; Kontak</a>
                </div>
            </nav>
        </div>
    </div>
</header>
