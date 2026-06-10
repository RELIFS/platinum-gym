<article class="group public-card public-card-hover overflow-hidden p-0">
    <div class="relative flex aspect-[4/3] items-end overflow-hidden bg-zinc-950">
        @if ($item->image_path)
            <img src="{{ asset($item->image_path) }}" alt="{{ $item->image_alt ?? $item->title ?? 'Aktivitas Platinum Gym Padang' }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105" width="540" height="960" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/30 to-transparent"></div>
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-zinc-900 via-zinc-950 to-gold-600/30"></div>
            <div class="absolute inset-0 opacity-[0.08]" aria-hidden="true" style="background-image: linear-gradient(rgba(255,255,255,.18) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.18) 1px, transparent 1px); background-size: 40px 40px;"></div>
        @endif
        <div class="relative p-6">
            <p class="text-5xl font-black text-gold-500/90">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</p>
        </div>
    </div>
    <div class="p-6">
        <h3 class="break-words text-lg font-black text-zinc-950 dark:text-white">{{ $item->title ?? 'Aktivitas Platinum Gym' }}</h3>
        <p class="mt-2 break-words text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $item->caption ?? 'Dokumentasi aktivitas Platinum Gym Padang.' }}</p>
    </div>
</article>
