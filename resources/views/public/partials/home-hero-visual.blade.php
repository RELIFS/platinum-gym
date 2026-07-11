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
        <div class="public-home-visual-media">
            <img src="{{ asset($mainVisual['src']) }}" alt="{{ $mainVisual['alt'] }}" class="object-cover w-full h-full" width="{{ $mainVisual['width'] }}" height="{{ $mainVisual['height'] }}" loading="eager" fetchpriority="high">
            <div class="public-home-visual-primary-overlay"></div>
            <div class="public-home-visual-caption">
                <div class="public-home-visual-caption-copy">
                    <p class="text-[0.65rem] type-control uppercase tracking-[0.12em] text-zinc-600 dark:text-gold-400">Training Room</p>
                    <p class="mt-1 text-sm type-control leading-tight text-zinc-950 break-words dark:text-zinc-100 sm:text-base">Gym, Classes, Personal Trainer</p>
                </div>
                <div class="public-home-visual-program-badge">
                    <p class="text-xl type-emphasis leading-none sm:text-3xl">3</p>
                    <p class="mt-1 text-[0.6rem] type-control uppercase tracking-[0.12em]">Program</p>
                </div>
            </div>
        </div>
    </div>

    <div class="public-home-visual-secondary-card left-0 top-16 w-[43%]">
        <div class="public-home-visual-secondary-media">
            <img src="{{ asset($facilityVisual['src']) }}" alt="{{ $facilityVisual['alt'] }}" class="object-cover w-full h-full transition duration-500 hover:scale-105" width="{{ $facilityVisual['width'] }}" height="{{ $facilityVisual['height'] }}" loading="lazy">
            <div class="public-home-visual-secondary-overlay"></div>
        </div>
    </div>

    <div class="public-home-visual-secondary-card bottom-6 left-[6%] w-[34%] xl:left-[10%]">
        <div class="public-home-visual-secondary-media">
            <img src="{{ asset($activityVisual['src']) }}" alt="{{ $activityVisual['alt'] }}" class="object-cover w-full h-full transition duration-500 hover:scale-105" width="{{ $activityVisual['width'] }}" height="{{ $activityVisual['height'] }}" loading="lazy">
            <div class="public-home-visual-activity-overlay"></div>
            <span class="public-home-visual-chip">Muaythai</span>
        </div>
    </div>
</div>
