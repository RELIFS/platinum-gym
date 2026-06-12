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
    <body class="bg-zinc-50 font-sans text-zinc-950 antialiased selection:bg-gold-500 selection:text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100">
        <div class="flex min-h-dvh bg-zinc-50 text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100">
            <aside class="relative hidden min-h-dvh w-[48%] overflow-hidden bg-zinc-950 lg:flex xl:w-1/2">
                <div class="absolute inset-0 bg-cover bg-center opacity-75" aria-hidden="true" style="background-image: url('{{ asset('images/public/gallery/platinum-gym-padang-bench-press-detail.webp') }}');"></div>
                <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(3,3,3,0.94)_0%,rgba(9,9,11,0.78)_48%,rgba(9,9,11,0.46)_100%)]" aria-hidden="true"></div>
                <div class="absolute inset-0 opacity-[0.10]" aria-hidden="true" style="background-image: linear-gradient(rgba(255,255,255,.12) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.12) 1px, transparent 1px); background-size: 56px 56px;"></div>
                <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-gold-500/18 via-zinc-950/30 to-transparent"></div>
                <div class="absolute -left-24 top-24 h-72 w-72 rounded-full bg-gold-500/20 blur-3xl" aria-hidden="true"></div>

                <div class="relative z-10 flex h-full w-full flex-col justify-between p-12 xl:p-16">
                    <a href="/" class="inline-flex w-fit items-center text-white" aria-label="Platinum Gym Padang">
                        <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="brand-logo h-12 w-auto" draggable="false" width="480" height="112">
                    </a>

                    <div class="max-w-xl">
                        <div class="mb-8 inline-flex items-center rounded-lg border border-gold-500/35 bg-zinc-950/45 px-4 py-2 text-sm font-semibold text-gold-400 shadow-2xl shadow-zinc-950/30 backdrop-blur-md">
                            Premium Fitness Center Padang
                        </div>
                        <div class="mb-6 text-5xl font-extrabold leading-tight text-white xl:text-6xl" aria-hidden="true">
                            PUSH YOUR<br><span class="text-gold-500">LIMITS.</span>
                        </div>
                        <p class="max-w-md text-lg leading-8 text-zinc-300 xl:text-xl">
                            Masuk, kelola membership, dan lanjutkan progres latihan Anda bersama Platinum Gym Padang.
                        </p>
                    </div>

                    <div class="grid max-w-lg grid-cols-3 gap-3">
                        <div class="rounded-xl border border-white/10 bg-zinc-950/35 p-4 backdrop-blur-md">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Member</p>
                            <p class="mt-2 text-sm font-bold text-white">Portal Akun</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-zinc-950/35 p-4 backdrop-blur-md">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Booking</p>
                            <p class="mt-2 text-sm font-bold text-white">Kelas Gym</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-zinc-950/35 p-4 backdrop-blur-md">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Check-in</p>
                            <p class="mt-2 text-sm font-bold text-white">QR Member</p>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="relative flex min-h-dvh w-full items-start justify-center overflow-x-hidden bg-zinc-50 px-5 pb-8 pt-[4.5rem] dark:bg-zinc-950 sm:px-8 sm:pb-12 sm:pt-28 lg:w-[52%] lg:px-10 lg:pb-10 lg:pt-24 xl:w-1/2 xl:pt-20">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_16%_8%,rgba(254,180,37,0.12),transparent_30%),radial-gradient(circle_at_86%_18%,rgba(24,24,27,0.08),transparent_28%)] dark:bg-[radial-gradient(circle_at_16%_8%,rgba(254,180,37,0.14),transparent_30%),radial-gradient(circle_at_86%_18%,rgba(255,255,255,0.06),transparent_28%)]" aria-hidden="true"></div>
                <div class="absolute inset-x-0 top-0 z-20 grid grid-cols-[minmax(0,1fr)_auto] items-start gap-3 px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
                    <a href="/" aria-label="Kembali ke Beranda" class="inline-flex h-10 w-10 min-w-0 max-w-full justify-self-start items-center justify-center gap-2 rounded-full border border-zinc-200 bg-white/85 px-0 text-sm font-semibold text-zinc-700 shadow-sm backdrop-blur-sm transition hover:border-gold-500/60 hover:bg-white hover:text-gold-600 active:scale-95 dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-gold-500 sm:h-11 sm:rounded-lg md:w-auto md:max-w-[17rem] md:px-4 lg:max-w-[17rem]">
                        <svg class="h-4 w-4 shrink-0 md:h-[18px] md:w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="hidden min-w-0 truncate md:inline">Kembali ke Beranda</span>
                    </a>

                    <x-theme-toggle class="h-10 w-10 sm:h-11 sm:w-11" />
                </div>

                <div class="relative z-10 w-full max-w-[30rem]">
                    <a href="/" class="mb-6 inline-flex items-center lg:hidden" aria-label="Platinum Gym Padang">
                        <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="Platinum Gym Padang" class="brand-logo h-11 w-auto" draggable="false" width="480" height="112">
                    </a>

                    <div class="auth-panel">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>

    </body>
</html>
