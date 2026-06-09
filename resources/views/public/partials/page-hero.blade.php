<section class="relative overflow-hidden bg-zinc-950 text-white">
    <div class="public-surface-grid absolute inset-0 opacity-30" aria-hidden="true"></div>
    <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold-500/50 to-transparent"></div>
    <div class="absolute right-0 top-0 h-full w-1/2 bg-gradient-to-l from-gold-500/18 via-gold-500/8 to-transparent"></div>
    <div class="absolute -right-10 bottom-10 hidden h-40 w-40 rotate-12 rounded-[2rem] border border-gold-500/25 lg:block"></div>
    <div class="absolute right-24 top-16 hidden h-1 w-28 rounded-full bg-gold-500/50 lg:block"></div>
    <div class="public-container relative py-16 sm:py-20 lg:py-24">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,0.78fr)_auto] lg:items-end">
            <div class="max-w-3xl">
                @if ($eyebrow)
                    <p class="public-eyebrow">{{ $eyebrow }}</p>
                @endif
                <h1 class="public-heading-balance mt-4 text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">
                    {{ $title }}
                </h1>
                @if ($description)
                    <p class="public-text-pretty mt-5 max-w-2xl text-base leading-8 text-zinc-300 sm:text-lg">
                        {{ $description }}
                    </p>
                @endif
            </div>
            <div class="hidden min-w-56 rounded-2xl border border-white/10 bg-white/[0.04] p-5 text-right shadow-2xl backdrop-blur lg:block">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-gold-400">Platinum Gym</p>
                <p class="mt-2 text-sm font-semibold leading-6 text-zinc-300">Gym, Class, PT</p>
            </div>
        </div>
    </div>
</section>
