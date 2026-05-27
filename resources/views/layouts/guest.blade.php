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

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-zinc-50 font-sans text-zinc-950 antialiased selection:bg-gold-500 selection:text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100">
        <div class="flex min-h-screen bg-zinc-50 text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100">
            <aside class="relative hidden min-h-screen w-1/2 overflow-hidden bg-zinc-950 lg:flex">
                <div class="absolute inset-0" style="background-image: linear-gradient(135deg, #050505 0%, #171717 46%, #080808 100%);"></div>
                <div class="absolute inset-0 opacity-[0.14]" style="background-image: linear-gradient(rgba(255,255,255,.12) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.12) 1px, transparent 1px); background-size: 48px 48px;"></div>
                <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-gold-500/10 to-transparent"></div>

                <div class="relative z-10 flex h-full w-full flex-col justify-between p-12 xl:p-16">
                    <a href="/" class="inline-flex w-fit items-center gap-4 text-white" aria-label="Platinum Gym Padang">
                        <span class="inline-flex h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-zinc-950 ring-1 ring-gold-500/40 shadow-[0_0_24px_rgba(254,172,24,0.25)]">
                            <img src="{{ asset('images/logo-platinum-gym.jpg') }}" alt="Platinum Gym Padang" class="h-full w-full object-cover" draggable="false">
                        </span>
                        <span class="text-2xl font-extrabold tracking-wide">Platinum<span class="text-gold-500">Gym</span></span>
                    </a>

                    <div class="max-w-xl">
                        <div class="mb-8 inline-flex items-center rounded-lg border border-gold-500/30 bg-gold-500/10 px-4 py-2 text-sm font-semibold text-gold-400">
                            Premium Fitness Center Padang
                        </div>
                        <h1 class="mb-6 text-5xl font-extrabold leading-tight text-white xl:text-6xl">
                            PUSH YOUR<br><span class="text-gold-500">LIMITS.</span>
                        </h1>
                        <p class="max-w-md text-lg leading-8 text-zinc-300 xl:text-xl">
                            Masuk, kelola membership, dan lanjutkan progres latihan Anda bersama Platinum Gym Padang.
                        </p>
                    </div>

                    <p class="text-sm font-medium text-zinc-500">
                        Platinum Gym Padang
                    </p>
                </div>
            </aside>

            <main class="relative flex min-h-screen w-full items-start justify-center bg-zinc-50 px-5 pb-10 pt-28 dark:bg-zinc-950 sm:px-8 sm:pb-12 sm:pt-32 lg:w-1/2 lg:px-10 lg:pb-14 lg:pt-28 xl:items-center xl:pt-24">
                <div class="absolute inset-x-0 top-0 z-20 grid grid-cols-[minmax(0,1fr)_auto] items-start gap-3 px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
                    <a href="/" aria-label="Kembali ke Beranda" class="inline-flex h-10 w-10 min-w-0 max-w-full justify-self-start items-center justify-center gap-2 rounded-full border border-zinc-200 bg-white/85 px-0 text-sm font-semibold text-zinc-700 shadow-sm backdrop-blur-sm transition hover:border-gold-500/60 hover:bg-white hover:text-gold-600 active:scale-95 dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-gold-500 sm:h-11 sm:rounded-lg md:w-auto md:max-w-[17rem] md:px-4 lg:max-w-[17rem]">
                        <svg class="h-4 w-4 shrink-0 md:h-[18px] md:w-[18px]" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="hidden min-w-0 truncate md:inline">Kembali ke Beranda</span>
                    </a>

                    <button type="button" data-theme-toggle class="inline-flex h-10 w-10 shrink-0 items-center justify-center justify-self-end rounded-full border border-zinc-200 bg-white/85 text-zinc-700 shadow-sm backdrop-blur-sm transition hover:border-gold-500/60 hover:bg-white hover:text-gold-600 active:scale-95 dark:border-zinc-800 dark:bg-zinc-900/80 dark:text-zinc-300 dark:hover:bg-zinc-900 dark:hover:text-gold-500 sm:h-11 sm:w-11" aria-label="Ganti mode tampilan">
                        <svg class="h-5 w-5 dark:hidden" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M10 2.5V1M10 19V17.5M17.5 10H19M1 10H2.5M15.3 4.7L16.36 3.64M3.64 16.36L4.7 15.3M15.3 15.3L16.36 16.36M3.64 3.64L4.7 4.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                            <circle cx="10" cy="10" r="3.75" stroke="currentColor" stroke-width="1.7" />
                        </svg>
                        <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M16.5 12.3C15.47 12.82 14.3 13.12 13.06 13.12C8.8 13.12 5.35 9.68 5.35 5.43C5.35 4.36 5.57 3.34 5.97 2.41C3.42 3.61 1.65 6.2 1.65 9.2C1.65 13.34 5.01 16.7 9.15 16.7C12.41 16.7 15.19 14.62 16.23 11.71L16.5 12.3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>

                <div class="w-full max-w-[30rem]">
                    <a href="/" class="mb-8 inline-flex items-center gap-3 lg:hidden" aria-label="Platinum Gym Padang">
                        <span class="inline-flex h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-zinc-950 ring-1 ring-gold-500/40 shadow-[0_0_20px_rgba(254,172,24,0.18)]">
                            <img src="{{ asset('images/logo-platinum-gym.jpg') }}" alt="Platinum Gym Padang" class="h-full w-full object-cover" draggable="false">
                        </span>
                        <span class="text-2xl font-extrabold tracking-wide text-zinc-950 dark:text-white">Platinum<span class="text-gold-500">Gym</span></span>
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

            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.passwordToggle);

                    if (!input) {
                        return;
                    }

                    const shouldShow = input.type === 'password';
                    input.type = shouldShow ? 'text' : 'password';
                    button.setAttribute('aria-label', shouldShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                    button.querySelector('[data-eye-open]')?.classList.toggle('hidden', shouldShow);
                    button.querySelector('[data-eye-closed]')?.classList.toggle('hidden', !shouldShow);
                });
            });

            document.querySelectorAll('[data-password-feedback-input]').forEach((input) => {
                const feedback = document.getElementById(`${input.id}-feedback`);

                if (!feedback) {
                    return;
                }

                const updatePasswordFeedback = () => {
                    const shouldShow = input.value.length > 0 && input.value.length < 8;
                    feedback.classList.toggle('hidden', !shouldShow);
                };

                input.addEventListener('input', updatePasswordFeedback);
                input.addEventListener('blur', updatePasswordFeedback);
                updatePasswordFeedback();
            });

            document.querySelectorAll('[data-phone-feedback-input]').forEach((input) => {
                const feedback = document.getElementById(`${input.id}-feedback`);
                const phonePattern = /^08\d{8,12}$/;

                if (!feedback) {
                    return;
                }

                const updatePhoneFeedback = () => {
                    const normalized = input.value.replace(/\D+/g, '');
                    const shouldShow = normalized.length >= 2 && !phonePattern.test(normalized);
                    feedback.classList.toggle('hidden', !shouldShow);
                };

                input.addEventListener('input', updatePhoneFeedback);
                input.addEventListener('blur', updatePhoneFeedback);
                updatePhoneFeedback();
            });
        </script>
    </body>
</html>
