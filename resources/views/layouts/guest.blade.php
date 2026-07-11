<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#fafafa">

        @include('partials.head-brand', ['title' => 'Akun Platinum Gym Padang'])

        @include('partials.theme-script')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-zinc-50 text-zinc-950 selection:bg-gold-500 selection:text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100">
        <div class="flex min-h-dvh bg-zinc-50 text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100">
            <aside class="relative hidden min-h-dvh w-[48%] overflow-hidden bg-zinc-950 lg:flex xl:w-1/2">
                <div class="absolute inset-0 bg-center bg-cover opacity-75" aria-hidden="true" style="background-image: url('{{ asset('images/public/gallery/platinum-gym-padang-bench-press-detail.webp') }}');"></div>
                <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(3,3,3,0.94)_0%,rgba(9,9,11,0.78)_48%,rgba(9,9,11,0.46)_100%)]" aria-hidden="true"></div>
                <div class="absolute inset-0 opacity-[0.10]" aria-hidden="true" style="background-image: linear-gradient(rgba(255,255,255,.12) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.12) 1px, transparent 1px); background-size: 56px 56px;"></div>
                <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-gold-500/18 via-zinc-950/30 to-transparent"></div>
                <div class="absolute rounded-full -left-24 top-24 h-72 w-72 bg-gold-500/20 blur-3xl" aria-hidden="true"></div>

                <div class="relative z-10 flex flex-col justify-between w-full h-full p-12 xl:p-16">
                    <a href="/" class="inline-flex items-center text-zinc-100 w-fit" aria-label="Platinum Gym Padang">
                        <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="w-auto h-12 brand-logo" draggable="false" width="480" height="112">
                    </a>

                    <div class="max-w-xl">
                        <div class="inline-flex items-center px-4 py-2 mb-8 text-sm type-control border rounded-lg shadow-2xl border-gold-500/35 bg-zinc-950/45 text-gold-400 shadow-zinc-950/30 backdrop-blur-md">
                            Premium Fitness Center Padang
                        </div>
                        <div class="mb-6 text-5xl type-display leading-tight text-zinc-100 xl:text-6xl" aria-hidden="true">
                            <span class="block">YOUR COMFORT</span>
                            <span class="block text-gold-500">GYM</span>
                        </div>
                        <p class="max-w-md text-lg leading-8 text-zinc-300 xl:text-xl">
                            Masuk, kelola membership, dan lanjutkan progres latihan Anda bersama Platinum Gym Padang.
                        </p>
                    </div>

                    <div class="grid max-w-lg grid-cols-3 gap-3">
                        <div class="p-4 border rounded-xl border-white/10 bg-zinc-950/35 backdrop-blur-md">
                            <p class="text-xs type-control uppercase tracking-[0.18em] text-zinc-400">Member</p>
                            <p class="mt-2 text-sm type-control text-zinc-100">Portal Akun</p>
                        </div>
                        <div class="p-4 border rounded-xl border-white/10 bg-zinc-950/35 backdrop-blur-md">
                            <p class="text-xs type-control uppercase tracking-[0.18em] text-zinc-400">Booking</p>
                            <p class="mt-2 text-sm type-control text-zinc-100">Kelas Gym</p>
                        </div>
                        <div class="p-4 border rounded-xl border-white/10 bg-zinc-950/35 backdrop-blur-md">
                            <p class="text-xs type-control uppercase tracking-[0.18em] text-zinc-400">Check-in</p>
                            <p class="mt-2 text-sm type-control text-zinc-100">QR Member</p>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="relative flex min-h-dvh w-full items-start justify-center overflow-x-hidden bg-zinc-50 px-5 pb-8 pt-[4.5rem] dark:bg-zinc-950 sm:px-8 sm:pb-12 sm:pt-28 lg:w-[52%] lg:px-10 lg:pb-10 lg:pt-24 xl:w-1/2 xl:pt-20">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_16%_8%,rgba(254,180,37,0.12),transparent_30%),radial-gradient(circle_at_86%_18%,rgba(24,24,27,0.08),transparent_28%)] dark:bg-[radial-gradient(circle_at_16%_8%,rgba(254,180,37,0.14),transparent_30%),radial-gradient(circle_at_86%_18%,rgba(255,255,255,0.06),transparent_28%)]" aria-hidden="true"></div>
                <div class="absolute inset-x-0 top-0 z-20 grid grid-cols-[minmax(0,1fr)_auto] items-start gap-3 px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
                    <a href="/" aria-label="Kembali ke Beranda" class="inline-flex h-11 w-11 min-w-0 max-w-full touch-manipulation items-center justify-center gap-2 justify-self-start rounded-full border border-zinc-200 bg-white/85 px-0 text-sm type-control text-zinc-700 shadow-sm backdrop-blur-sm transition hover:border-gold-500/60 hover:bg-white hover:text-gold-text focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/35 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-50 active:scale-95 dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:focus-visible:ring-gold-400/35 dark:focus-visible:ring-offset-zinc-950 sm:rounded-lg md:w-auto md:max-w-[17rem] md:px-4 lg:max-w-[17rem]">
                        <svg class="h-4 w-4 shrink-0 md:h-[18px] md:w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="hidden min-w-0 truncate md:inline">Kembali ke Beranda</span>
                    </a>

                    <x-theme-toggle class="h-11 w-11" />
                </div>

                <div class="relative z-10 w-full max-w-[30rem]">
                    <a href="/" class="inline-flex items-center mb-6 lg:hidden" aria-label="Platinum Gym Padang">
                        <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="w-auto brand-logo h-11" draggable="false" width="480" height="112">
                    </a>

                    <div class="auth-panel">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>

    </body>
</html>
