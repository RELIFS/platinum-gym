@php
    $mainVisual = [
        'src' => 'images/public/gallery/platinum-gym-padang-bench-press-detail.webp',
        'alt' => 'Area bench press Platinum Gym Padang',
        'width' => 562,
        'height' => 1000,
    ];

    $facilityVisual = [
        'src' => 'images/public/gallery/platinum-gym-padang-training-floor.webp',
        'alt' => 'Training floor Platinum Gym Padang',
        'width' => 600,
        'height' => 336,
    ];

    $activityVisual = [
        'src' => 'images/public/gallery/platinum-gym-padang-instagram-07.webp',
        'alt' => 'Sesi Muaythai Platinum Gym Padang',
        'width' => 540,
        'height' => 960,
    ];
@endphp

<div class="public-home-hero-visual public-motion-depth public-motion-reveal" aria-label="Visual fasilitas dan aktivitas Platinum Gym Padang" data-motion="reveal depth" data-motion-delay="100">
    <div class="public-home-visual-card">
        <div class="relative aspect-[16/10] overflow-hidden rounded-[1.15rem] bg-zinc-900 lg:aspect-[4/5] lg:rounded-[1.5rem]">
            <img src="{{ asset($mainVisual['src']) }}" alt="{{ $mainVisual['alt'] }}" class="object-cover w-full h-full" width="{{ $mainVisual['width'] }}" height="{{ $mainVisual['height'] }}" loading="eager" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/75 via-zinc-950/8 to-transparent"></div>
            <div class="public-home-visual-caption">
                <div class="public-home-visual-caption-copy">
                    <p class="text-[0.65rem] font-black uppercase tracking-[0.18em] text-gold-400">Training Room</p>
                    <p class="mt-1 text-sm font-black leading-tight text-white break-words sm:text-base">Gym, Classes, Personal Trainer</p>
                </div>
                <div class="public-home-visual-program-badge">
                    <p class="text-xl font-black leading-none sm:text-3xl">3</p>
                    <p class="mt-1 text-[0.6rem] font-black uppercase tracking-[0.16em]">Program</p>
                </div>
            </div>
        </div>
    </div>

    <div class="absolute left-0 top-16 z-10 hidden w-[43%] overflow-hidden rounded-[1.5rem] border border-white/10 bg-zinc-900 p-2 shadow-[0_28px_80px_rgba(0,0,0,0.36)] lg:block">
        <div class="relative aspect-[4/5] overflow-hidden rounded-[1.15rem]">
            <img src="{{ asset($facilityVisual['src']) }}" alt="{{ $facilityVisual['alt'] }}" class="object-cover w-full h-full transition duration-500 hover:scale-105" width="{{ $facilityVisual['width'] }}" height="{{ $facilityVisual['height'] }}" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/45 to-transparent"></div>
        </div>
    </div>

    <div class="absolute bottom-6 left-[6%] z-10 hidden w-[34%] overflow-hidden rounded-[1.5rem] border border-white/10 bg-zinc-900 p-2 shadow-[0_28px_80px_rgba(0,0,0,0.36)] lg:block xl:left-[10%]">
        <div class="relative aspect-[4/5] overflow-hidden rounded-[1.15rem]">
            <img src="{{ asset($activityVisual['src']) }}" alt="{{ $activityVisual['alt'] }}" class="object-cover w-full h-full transition duration-500 hover:scale-105" width="{{ $activityVisual['width'] }}" height="{{ $activityVisual['height'] }}" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/55 via-transparent to-transparent"></div>
            <span class="absolute bottom-4 left-4 max-w-[calc(100%-2rem)] rounded-full bg-zinc-950/80 px-3 py-1.5 text-[0.65rem] font-black uppercase tracking-[0.16em] text-gold-400 ring-1 ring-white/10">Muaythai</span>
        </div>
    </div>
</div>
