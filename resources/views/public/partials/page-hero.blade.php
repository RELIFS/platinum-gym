<section class="public-page-hero">
    <div class="public-surface-grid absolute inset-0 opacity-25" aria-hidden="true"></div>
    <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold-500/50 to-transparent" aria-hidden="true"></div>
    <div class="public-page-hero-accent absolute right-0 top-0 hidden h-full w-2/5 bg-gradient-to-l from-gold-500/16 via-gold-500/6 to-transparent lg:block" aria-hidden="true"></div>
    <div class="absolute right-8 top-10 hidden h-24 w-24 rotate-12 rounded-2xl border border-gold-500/25 xl:block" aria-hidden="true"></div>
    <div class="absolute bottom-10 right-24 hidden h-px w-32 bg-gold-500/55 lg:block" aria-hidden="true"></div>

    <div class="public-container relative py-14 sm:py-16 lg:py-20">
        <div class="public-page-hero-content public-motion-reveal max-w-4xl" data-motion="reveal">
                @if ($eyebrow)
                    <p class="public-eyebrow">{{ $eyebrow }}</p>
                @endif
                <h1 class="public-heading-balance mt-4 text-4xl font-black leading-[1.02] tracking-normal sm:text-5xl lg:text-6xl xl:text-7xl">
                    {{ $title }}
                </h1>
                @if ($description)
                    <p class="public-text-pretty mt-5 max-w-2xl text-base leading-7 text-zinc-600 dark:text-zinc-300 sm:text-lg sm:leading-8">
                        {{ $description }}
                    </p>
                @endif
        </div>
    </div>
</section>
