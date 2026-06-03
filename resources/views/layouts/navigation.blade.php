@php
    $user = Auth::user();
    $roleLabels = $user?->roles->pluck('name')->map(fn ($role) => str($role)->headline()->toString())->all() ?? [];
    $navItems = array_values(array_filter([
        $user?->hasRole('member') ? ['label' => 'Member', 'route' => 'member.dashboard', 'active' => 'member.*'] : null,
        $user?->hasRole('admin') ? ['label' => 'Admin', 'route' => 'admin.dashboard', 'active' => 'admin.*'] : null,
        $user?->hasRole('owner') ? ['label' => 'Owner', 'route' => 'owner.dashboard', 'active' => 'owner.*'] : null,
    ]));
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-50 border-b border-zinc-200 bg-zinc-50/90 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/90">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-16 items-center justify-between gap-4 py-3">
            <div class="flex min-w-0 items-center gap-5">
                <a href="{{ route('dashboard') }}" class="inline-flex min-w-0 items-center" aria-label="Dashboard Platinum Gym">
                    <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="brand-logo h-9 w-auto sm:h-10" width="480" height="112" draggable="false">
                </a>

                <div class="hidden items-center gap-1 lg:flex" aria-label="Navigasi dashboard">
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}" @if (request()->routeIs($item['active'])) aria-current="page" @endif class="rounded-full px-3 py-2 text-sm font-bold transition {{ request()->routeIs($item['active']) ? 'bg-gold-500 text-zinc-950' : 'text-zinc-600 hover:bg-zinc-200/70 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-white/10 dark:hover:text-white' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    <a href="{{ route('profile.edit') }}" @if (request()->routeIs('profile.*')) aria-current="page" @endif class="rounded-full px-3 py-2 text-sm font-bold transition {{ request()->routeIs('profile.*') ? 'bg-gold-500 text-zinc-950' : 'text-zinc-600 hover:bg-zinc-200/70 hover:text-zinc-950 dark:text-zinc-300 dark:hover:bg-white/10 dark:hover:text-white' }}">
                        Profil
                    </a>
                </div>
            </div>

            <div class="hidden items-center gap-3 lg:flex">
                <a href="{{ route('public.home') }}" class="inline-flex h-10 items-center rounded-full border border-zinc-300 bg-white/80 px-4 text-sm font-bold text-zinc-800 transition hover:border-gold-500/60 hover:text-gold-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-200 dark:hover:text-gold-400">
                    Website
                </a>

                <x-theme-toggle class="h-10 w-10" />

                <div class="min-w-0 border-l border-zinc-200 pl-4 dark:border-white/10">
                    <p class="max-w-44 truncate text-sm font-black text-zinc-950 dark:text-white">{{ $user?->name }}</p>
                    <p class="max-w-44 truncate text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ implode(', ', $roleLabels) ?: 'User' }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex h-10 items-center rounded-full bg-zinc-950 px-4 text-sm font-black text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-gold-500/40 focus:ring-offset-2 focus:ring-offset-zinc-50 dark:bg-gold-500 dark:text-zinc-950 dark:hover:bg-gold-400 dark:focus:ring-offset-zinc-950">
                        Keluar
                    </button>
                </form>
            </div>

            <div class="flex items-center gap-2 lg:hidden">
                <x-theme-toggle class="h-10 w-10" />
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-zinc-950 text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-gold-500/40 focus:ring-offset-2 focus:ring-offset-zinc-50 dark:bg-gold-500 dark:text-zinc-950 dark:hover:bg-gold-400 dark:focus:ring-offset-zinc-950" aria-label="Buka navigasi dashboard" x-on:click="open = ! open">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path x-show="!open" d="M3 5.5H17M3 10H17M3 14.5H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path x-cloak x-show="open" d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
        </div>

        <div x-cloak x-show="open" x-transition class="pb-4 lg:hidden">
            <div class="rounded-xl border border-zinc-200 bg-white p-3 shadow-xl dark:border-white/10 dark:bg-zinc-900">
                <div class="mb-3 border-b border-zinc-200 px-2 pb-3 dark:border-white/10">
                    <p class="truncate text-sm font-black text-zinc-950 dark:text-white">{{ $user?->name }}</p>
                    <p class="truncate text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $user?->email }}</p>
                </div>

                <div class="grid gap-1">
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}" @if (request()->routeIs($item['active'])) aria-current="page" @endif class="rounded-lg px-4 py-3 text-sm font-bold {{ request()->routeIs($item['active']) ? 'bg-gold-500 text-zinc-950' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-white/10' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    <a href="{{ route('profile.edit') }}" class="rounded-lg px-4 py-3 text-sm font-bold text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-white/10">Profil</a>
                    <a href="{{ route('public.home') }}" class="rounded-lg px-4 py-3 text-sm font-bold text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-white/10">Website</a>

                    <form method="POST" action="{{ route('logout') }}" class="pt-2">
                        @csrf
                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-zinc-950 px-4 py-2.5 text-sm font-black text-white transition hover:bg-zinc-800 dark:bg-gold-500 dark:text-zinc-950 dark:hover:bg-gold-400">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
