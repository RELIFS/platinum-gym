<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials.head-brand')

        <script>
            (() => {
                const savedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-slate-50 text-slate-950 transition-colors dark:bg-slate-950 dark:text-white lg:flex">
            <aside class="relative hidden min-h-screen w-1/2 overflow-hidden bg-zinc-950 lg:flex">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(254,172,24,0.22),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(255,217,120,0.12),transparent_24%),linear-gradient(135deg,#020617_0%,#111827_45%,#09090b_100%)]"></div>
                <div class="absolute inset-0 opacity-20 [background-image:linear-gradient(rgba(255,255,255,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.08)_1px,transparent_1px)] [background-size:48px_48px]"></div>
                <div class="absolute -bottom-24 -right-20 h-72 w-72 rounded-full bg-gold-500/20 blur-3xl"></div>
                <div class="relative z-10 flex h-full w-full flex-col justify-between p-12 xl:p-16">
                    <a href="/" class="inline-flex w-fit items-center gap-3 text-white">
                        <span class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-gold-500/40 bg-zinc-950 shadow-[0_0_28px_rgba(254,172,24,0.22)]">
                            <img src="{{ asset('images/logo-platinum-gym.jpg') }}" alt="Platinum Gym Padang" class="h-full w-full object-cover">
                        </span>
                        <span class="text-2xl font-black tracking-wide">Platinum<span class="text-gold-500">Gym</span></span>
                    </a>

                    <div class="max-w-xl">
                        <div class="mb-8 inline-flex items-center rounded-full border border-gold-500/30 bg-gold-500/10 px-4 py-2 text-sm font-semibold text-gold-400">
                            Premium Fitness Center Padang
                        </div>
                        <h1 class="mb-6 text-5xl font-black leading-tight tracking-tight text-white xl:text-6xl">
                            PUSH YOUR<br><span class="text-gold-500">LIMITS.</span>
                        </h1>
                        <p class="max-w-md text-lg leading-8 text-white/75 xl:text-xl">
                            Bergabunglah dengan komunitas kebugaran paling premium di Padang dan transformasikan hidup Anda.
                        </p>
                    </div>
                </div>
            </aside>

            <main class="relative flex min-h-screen w-full items-center justify-center px-5 py-20 sm:px-8 lg:w-1/2 lg:px-10">
                <a href="/" aria-label="Kembali ke Beranda" class="absolute left-4 top-4 inline-flex h-11 items-center justify-center gap-2 rounded-full border border-slate-200 bg-white/80 px-4 text-sm font-semibold text-slate-700 shadow-sm backdrop-blur transition hover:border-gold-500/60 hover:text-gold-600 dark:border-slate-800 dark:bg-slate-900/80 dark:text-slate-300 dark:hover:text-gold-500 sm:left-6 sm:top-6">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="hidden sm:inline">Kembali ke Beranda</span>
                </a>

                <button type="button" data-theme-toggle class="absolute right-4 top-4 inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white/80 text-slate-700 shadow-sm backdrop-blur transition hover:border-gold-500/60 hover:text-gold-600 dark:border-slate-800 dark:bg-slate-900/80 dark:text-slate-300 dark:hover:text-gold-500 sm:right-6 sm:top-6" aria-label="Ganti mode tampilan">
                    <svg class="h-5 w-5 dark:hidden" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M10 2.5V1M10 19V17.5M17.5 10H19M1 10H2.5M15.3 4.7L16.36 3.64M3.64 16.36L4.7 15.3M15.3 15.3L16.36 16.36M3.64 3.64L4.7 4.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        <circle cx="10" cy="10" r="3.75" stroke="currentColor" stroke-width="1.7" />
                    </svg>
                    <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M16.5 12.3C15.47 12.82 14.3 13.12 13.06 13.12C8.8 13.12 5.35 9.68 5.35 5.43C5.35 4.36 5.57 3.34 5.97 2.41C3.42 3.61 1.65 6.2 1.65 9.2C1.65 13.34 5.01 16.7 9.15 16.7C12.41 16.7 15.19 14.62 16.23 11.71L16.5 12.3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                    </svg>
                </button>

                <div class="w-full max-w-md">
                    <a href="/" class="mb-8 inline-flex items-center gap-3 lg:hidden">
                        <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl border border-gold-500/40 bg-slate-950 shadow-[0_0_24px_rgba(254,172,24,0.2)]">
                            <img src="{{ asset('images/logo-platinum-gym.jpg') }}" alt="Platinum Gym Padang" class="h-full w-full object-cover">
                        </span>
                        <span class="text-2xl font-black tracking-wide text-slate-950 dark:text-white">Platinum<span class="text-gold-500">Gym</span></span>
                    </a>

                    {{ $slot }}
                </div>
            </main>
        </div>

        <script>
            document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const isDark = document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                });
            });
        </script>
    </body>
</html>
