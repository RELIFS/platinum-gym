@php
    $class = $schedule->gymClass;
    $price = $class?->promo_price ?? $class?->member_price ?? $class?->non_member_price;
    $whatsappUrl = ($settings['whatsapp_url'] ?? 'https://wa.me/6282174777761');
    $dayLabel = $dayLabels[$schedule->day_of_week] ?? 'Jadwal';
    $scheduleCtaUrl = $whatsappUrl.(str_contains($whatsappUrl, '?') ? '&' : '?').http_build_query([
        'text' => 'Halo Platinum Gym Padang, saya ingin tanya kelas '.($class?->name ?? 'Platinum Gym').' hari '.$dayLabel.' jam '.substr((string) $schedule->start_time, 0, 5).'.',
    ]);
    $classType = \Illuminate\Support\Str::lower((string) $class?->class_type);
    $visual = match (true) {
        str_contains($classType, 'pound') => 'platinum-gym-padang-instagram-02.webp',
        str_contains($classType, 'muay') => 'platinum-gym-padang-instagram-03.webp',
        str_contains($classType, 'zumba'), str_contains($classType, 'aerobic'), str_contains($classType, 'senam') => 'platinum-gym-padang-instagram-06.webp',
        default => 'platinum-gym-padang-instagram-05.webp',
    };
@endphp

<article class="public-card public-card-hover flex h-full flex-col">
    <div class="relative -mx-2 -mt-2 mb-5 aspect-[4/3] overflow-hidden rounded-2xl bg-zinc-950">
        <img src="{{ asset('images/public/gallery/'.$visual) }}" alt="Ilustrasi {{ $class?->name ?? 'kelas' }} Platinum Gym Padang" class="h-full w-full object-cover transition duration-500 hover:scale-105" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/80 via-zinc-950/15 to-transparent"></div>
        <div class="absolute bottom-4 left-4 right-4 flex min-w-0 flex-wrap items-end justify-between gap-3">
            <span class="max-w-full break-words rounded-full bg-gold-500 px-3 py-1 text-xs font-black uppercase tracking-[0.16em] text-zinc-950">{{ $dayLabel }}</span>
            <span class="break-words rounded-full bg-zinc-950/80 px-3 py-1 text-xs font-black text-white ring-1 ring-white/10">{{ substr((string) $schedule->start_time, 0, 5) }}</span>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <span class="max-w-full break-words rounded-full bg-gold-500 px-3 py-1 text-xs font-black uppercase tracking-[0.16em] text-zinc-950">
            {{ $dayLabel }}
        </span>
        <span class="max-w-full break-words rounded-full bg-zinc-100 px-3 py-1 text-xs font-bold text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
            {{ $class ? \Illuminate\Support\Str::headline($class->class_type) : 'Kelas' }}
        </span>
    </div>
    <h3 class="mt-5 break-words text-xl font-black text-zinc-950 dark:text-white">{{ $class?->name ?? 'Kelas Platinum' }}</h3>
    <dl class="mt-5 space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
        <div class="flex min-w-0 justify-between gap-4">
            <dt class="shrink-0 font-semibold text-zinc-500 dark:text-zinc-500">Waktu</dt>
            <dd class="min-w-0 break-words text-right font-bold text-zinc-900 dark:text-zinc-100">{{ substr((string) $schedule->start_time, 0, 5) }} - {{ substr((string) $schedule->end_time, 0, 5) }}</dd>
        </div>
        <div class="flex min-w-0 justify-between gap-4">
            <dt class="shrink-0 font-semibold text-zinc-500 dark:text-zinc-500">Coach</dt>
            <dd class="min-w-0 break-words text-right font-bold text-zinc-900 dark:text-zinc-100">{{ $schedule->trainer?->name ?? 'Tim Platinum' }}</dd>
        </div>
        <div class="flex min-w-0 justify-between gap-4">
            <dt class="shrink-0 font-semibold text-zinc-500 dark:text-zinc-500">Kuota</dt>
            <dd class="min-w-0 break-words text-right font-bold text-zinc-900 dark:text-zinc-100">{{ $schedule->capacity ?? $class?->capacity }} peserta</dd>
        </div>
        <div class="flex min-w-0 justify-between gap-4">
            <dt class="shrink-0 font-semibold text-zinc-500 dark:text-zinc-500">Harga</dt>
            <dd class="min-w-0 break-words text-right font-bold text-gold-600 dark:text-gold-400">@include('public.partials.price', ['amount' => $price])</dd>
        </div>
    </dl>
    <a href="{{ $scheduleCtaUrl }}" target="_blank" rel="noopener noreferrer" class="public-button-secondary mt-6 w-full">Tanya Kelas</a>
</article>
