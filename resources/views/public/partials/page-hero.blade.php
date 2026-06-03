<section class="relative overflow-hidden bg-zinc-950 text-white">
    <div class="absolute inset-0 opacity-[0.16]" style="background-image: linear-gradient(rgba(255,255,255,.16) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.16) 1px, transparent 1px); background-size: 48px 48px;"></div>
    <div class="absolute -right-24 top-10 h-72 w-72 rounded-full bg-gold-500/20 blur-3xl"></div>
    <div class="public-container relative py-16 sm:py-20 lg:py-24">
        <div class="max-w-3xl">
            @if ($eyebrow)
                <p class="public-eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1 class="public-heading-balance mt-4 text-4xl font-black leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                {{ $title }}
            </h1>
            @if ($description)
                <p class="mt-5 max-w-2xl text-base leading-8 text-zinc-300 sm:text-lg">
                    {{ $description }}
                </p>
            @endif
        </div>
    </div>
</section>
