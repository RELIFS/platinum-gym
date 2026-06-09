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

<div class="relative mx-auto w-full max-w-[34rem] lg:mx-0 lg:min-h-[34rem] lg:max-w-none" aria-label="Visual fasilitas dan aktivitas Platinum Gym Padang">
    <div class="relative z-20 overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/[0.055] p-2 shadow-[0_32px_90px_rgba(0,0,0,0.34)] backdrop-blur lg:absolute lg:right-0 lg:top-0 lg:w-[72%] lg:rounded-[2rem] lg:p-3 xl:w-[68%]">
        <div class="relative aspect-[16/10] overflow-hidden rounded-[1.15rem] bg-zinc-900 lg:aspect-[4/5] lg:rounded-[1.5rem]">
            <img src="{{ asset($mainVisual['src']) }}" alt="{{ $mainVisual['alt'] }}" class="h-full w-full object-cover" width="{{ $mainVisual['width'] }}" height="{{ $mainVisual['height'] }}" loading="eager" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/75 via-zinc-950/8 to-transparent"></div>
            <div class="absolute bottom-4 left-4 right-4 flex flex-wrap items-end justify-between gap-3 sm:bottom-5 sm:left-5 sm:right-5">
                <div class="min-w-0 rounded-2xl bg-zinc-950/72 px-4 py-3 ring-1 ring-white/10 backdrop-blur">
                    <p class="text-[0.65rem] font-black uppercase tracking-[0.18em] text-gold-400">Training Floor</p>
                    <p class="mt-1 break-words text-sm font-black leading-tight text-white sm:text-base">Gym, kelas, dan PT.</p>
                </div>
                <div class="inline-grid min-h-14 min-w-24 place-items-center rounded-2xl bg-gold-500 px-4 py-2.5 text-center text-zinc-950 shadow-[0_18px_46px_rgba(254,172,24,0.28)]">
                    <p class="text-2xl font-black leading-none sm:text-3xl">3</p>
                    <p class="mt-1 text-[0.6rem] font-black uppercase tracking-[0.16em]">Program</p>
                </div>
            </div>
        </div>
    </div>

    <div class="absolute left-0 top-16 z-10 hidden w-[43%] overflow-hidden rounded-[1.5rem] border border-white/10 bg-zinc-900 p-2 shadow-[0_28px_80px_rgba(0,0,0,0.36)] lg:block">
        <div class="relative aspect-[4/5] overflow-hidden rounded-[1.15rem]">
            <img src="{{ asset($facilityVisual['src']) }}" alt="{{ $facilityVisual['alt'] }}" class="h-full w-full object-cover transition duration-500 hover:scale-105" width="{{ $facilityVisual['width'] }}" height="{{ $facilityVisual['height'] }}" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/45 to-transparent"></div>
        </div>
    </div>

    <div class="absolute bottom-6 left-[6%] z-10 hidden w-[34%] overflow-hidden rounded-[1.5rem] border border-white/10 bg-zinc-900 p-2 shadow-[0_28px_80px_rgba(0,0,0,0.36)] lg:block xl:left-[10%]">
        <div class="relative aspect-[4/5] overflow-hidden rounded-[1.15rem]">
            <img src="{{ asset($activityVisual['src']) }}" alt="{{ $activityVisual['alt'] }}" class="h-full w-full object-cover transition duration-500 hover:scale-105" width="{{ $activityVisual['width'] }}" height="{{ $activityVisual['height'] }}" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/55 via-transparent to-transparent"></div>
            <span class="absolute bottom-4 left-4 max-w-[calc(100%-2rem)] rounded-full bg-zinc-950/80 px-3 py-1.5 text-[0.65rem] font-black uppercase tracking-[0.16em] text-gold-400 ring-1 ring-white/10">Muaythai</span>
        </div>
    </div>
</div>
