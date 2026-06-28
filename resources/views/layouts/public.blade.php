@props([
    'title' => 'Platinum Gym Padang',
    'description' => 'Platinum Gym Padang adalah pusat kebugaran premium di Padang untuk gym, senam, personal trainer, Muaythai, Poundfit, dan produk fitness.',
])

@php($layoutMeta = $layoutMeta ?? \App\Features\PublicWebsite\ViewModels\PublicLayoutViewModel::make($settings ?? [], $title, $description))

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ $layoutMeta['description'] }}">
        <meta name="theme-color" content="#fafafa">
        <meta property="og:locale" content="id_ID">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $layoutMeta['siteName'] }}">
        <meta property="og:title" content="{{ $layoutMeta['pageTitle'] }}">
        <meta property="og:description" content="{{ $layoutMeta['description'] }}">
        <meta property="og:url" content="{{ $layoutMeta['canonicalUrl'] }}">
        <meta property="og:image" content="{{ $layoutMeta['socialImageUrl'] }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $layoutMeta['pageTitle'] }}">
        <meta name="twitter:description" content="{{ $layoutMeta['description'] }}">
        <meta name="twitter:image" content="{{ $layoutMeta['socialImageUrl'] }}">
        <link rel="canonical" href="{{ $layoutMeta['canonicalUrl'] }}">

        @include('partials.head-brand', ['title' => $layoutMeta['pageTitle']])

        <script type="application/ld+json">
            @json($layoutMeta['structuredData'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        </script>

        @include('partials.theme-script')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-zinc-50 font-sans text-zinc-950 antialiased selection:bg-gold-500 selection:text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100">
        <a href="#main-content" class="public-skip-link">Lewati navigasi utama</a>

        <div class="min-h-screen overflow-hidden" data-public-motion-root>
            @include('public.partials.header', ['settings' => $settings ?? []])

            <main id="main-content" tabindex="-1">
                {{ $slot }}
            </main>

            @include('public.partials.footer', ['settings' => $settings ?? []])
        </div>

        @include('public.partials.chatbot', ['chatbotConfig' => $chatbotConfig ?? null])

    </body>
</html>
