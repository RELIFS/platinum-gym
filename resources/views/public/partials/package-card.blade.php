@php
    $kind = \Illuminate\Support\Str::lower((string) $package->package_kind);
    $name = \Illuminate\Support\Str::lower($package->name);
    $registrationLabel = str_contains($kind, 'membership') ? 'Daftar Membership' : 'Daftar Paket';
    $visual = match (true) {
        str_contains($kind, 'pt'), str_contains($name, 'pt'), str_contains($name, 'trainer') => 'platinum-gym-padang-instagram-01.webp',
        str_contains($name, 'muaythai') => 'platinum-gym-padang-instagram-07.webp',
        str_contains($name, 'senam'), str_contains($name, 'zumba'), str_contains($name, 'aerobic') => 'platinum-gym-padang-instagram-06.webp',
        default => 'platinum-gym-padang-training-floor.webp',
    };
    $durationLabel = $package->durationMarketingLabel();
    $bonusLabel = $package->durationBonusLabel();
    $motionDelay = (($loop->index ?? 0) % 4) * 80;
@endphp

<article class="group public-card public-card-hover public-motion-card public-motion-reveal flex h-full flex-col" data-motion="reveal card" data-motion-delay="{{ $motionDelay }}">
    <div class="public-media-frame -mx-2 -mt-2 mb-5 aspect-[4/3]">
        <img src="{{ asset('images/public/gallery/'.$visual) }}" alt="Ilustrasi {{ $package->name }} di Platinum Gym Padang" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" width="600" height="1000" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/85 via-zinc-950/20 to-transparent"></div>
        <div class="absolute inset-0 ring-1 ring-inset ring-white/10"></div>
        <span class="absolute bottom-4 left-4 max-w-[calc(100%-2rem)] break-words rounded-full bg-gold-500 px-3 py-1 text-xs type-control uppercase tracking-[0.12em] text-zinc-950">
            {{ \Illuminate\Support\Str::headline($package->package_kind) }}
        </span>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <span class="max-w-full break-words rounded-full bg-zinc-100 px-3 py-1 text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
            {{ \Illuminate\Support\Str::headline($package->package_kind) }}
        </span>
        @if ($package->promo_price)
            <span class="rounded-full bg-gold-500 px-3 py-1 text-xs type-control uppercase tracking-[0.12em] text-zinc-950">Promo</span>
        @endif
        @if ($bonusLabel)
            <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs type-control uppercase tracking-[0.11em] text-emerald-800 dark:text-emerald-300">{{ $bonusLabel }}</span>
        @endif
    </div>
    <h3 class="mt-5 break-words text-xl type-title text-zinc-950 dark:text-zinc-100">{{ $package->name }}</h3>
    <p class="mt-3 break-words text-sm leading-7 text-zinc-600 dark:text-zinc-400">{{ $package->description }}</p>
    <div class="mt-6 flex flex-wrap items-end gap-x-2 gap-y-1">
        <p class="break-words text-3xl type-emphasis text-zinc-950 dark:text-zinc-100">@include('public.partials.price', ['amount' => $package->promo_price ?? $package->price])</p>
        @if ($durationLabel)
            <p class="break-words pb-1 text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-400">/{{ $durationLabel }}</p>
        @elseif ($package->session_count)
            <p class="break-words pb-1 text-xs type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-400">/{{ $package->session_count }} sesi</p>
        @endif
    </div>
    @if ($package->promo_price)
        <p class="mt-1 break-words text-sm type-control text-zinc-500 line-through">@include('public.partials.price', ['amount' => $package->price])</p>
    @endif
    <ul class="mt-6 space-y-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
        @foreach (($package->benefits ?? []) as $benefit)
            <li class="flex gap-2">
                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gold-500"></span>
                <span class="min-w-0 break-words">{{ $benefit }}</span>
            </li>
        @endforeach
    </ul>
    <a href="{{ route('register') }}" class="public-button-primary mt-auto w-full" aria-label="{{ $registrationLabel }} {{ $package->name }}">{{ $registrationLabel }}</a>
</article>
