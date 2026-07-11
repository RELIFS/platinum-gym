@php
    $heroEyebrow = $eyebrow ?? null;
    $heroTitle = $title ?? '';
    $heroDescription = $description ?? null;
    $primaryUrl = $primaryUrl ?? null;
    $primaryLabel = $primaryLabel ?? null;
    $secondaryUrl = $secondaryUrl ?? null;
    $secondaryLabel = $secondaryLabel ?? null;
@endphp

<section class="public-page-hero">
    <div class="public-surface-grid absolute inset-0 opacity-25" aria-hidden="true"></div>
    <div class="public-page-hero-accent absolute right-0 top-0 hidden h-full w-2/5 bg-gradient-to-l from-gold-500/16 via-gold-500/6 to-transparent lg:block" aria-hidden="true"></div>
    <div class="absolute right-8 top-10 hidden h-24 w-24 rotate-12 rounded-2xl border border-gold-500/25 xl:block" aria-hidden="true"></div>
    <div class="absolute bottom-10 right-24 hidden h-px w-32 bg-gold-500/55 lg:block" aria-hidden="true"></div>
    <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold-500/50 to-transparent" aria-hidden="true"></div>

    <div class="public-container relative py-14 sm:py-16 lg:py-20">
        <div class="public-page-hero-content public-motion-reveal max-w-4xl" data-motion="reveal">
            @if ($heroEyebrow)
                <div class="inline-flex max-w-full items-center gap-3">
                    <span class="h-px w-10 shrink-0 bg-gold-500" aria-hidden="true"></span>
                    <p class="public-eyebrow">{{ $heroEyebrow }}</p>
                </div>
            @endif
            <h1 class="public-heading-balance mt-4 text-4xl type-display leading-[1.08] tracking-normal text-zinc-950 dark:text-zinc-100 sm:text-5xl sm:leading-[1.04] lg:text-6xl xl:text-6xl">
                {{ $heroTitle }}
            </h1>
            @if ($heroDescription)
                <p class="public-text-pretty mt-5 max-w-2xl text-base leading-7 text-zinc-600 dark:text-zinc-300 sm:text-lg sm:leading-8">
                    {{ $heroDescription }}
                </p>
            @endif
            @if (($primaryUrl && $primaryLabel) || ($secondaryUrl && $secondaryLabel))
                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    @if ($primaryUrl && $primaryLabel)
                        <a href="{{ $primaryUrl }}" class="public-button-primary public-motion-cta" data-motion="cta">{{ $primaryLabel }}</a>
                    @endif
                    @if ($secondaryUrl && $secondaryLabel)
                        <a href="{{ $secondaryUrl }}" class="public-button-secondary">{{ $secondaryLabel }}</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
