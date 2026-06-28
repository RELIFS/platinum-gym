@php($motionDelay = (($loop->index ?? 0) % 4) * 80)

<article class="group public-card public-card-hover public-motion-card public-motion-reveal public-product-card flex h-full flex-col overflow-hidden" data-motion="reveal card" data-motion-delay="{{ $motionDelay }}">
    <div class="public-media-frame public-product-media aspect-[4/3] bg-zinc-100 dark:bg-zinc-900">
        @if ($product->image_path)
            <img src="{{ asset($product->image_path) }}" alt="{{ $product->image_alt ?? 'Foto produk '.$product->name.' Platinum Gym Padang' }}" class="h-full w-full object-contain p-3 transition duration-500 group-hover:scale-105" width="640" height="480" loading="lazy">
        @else
            <div class="flex h-full items-center justify-center bg-gradient-to-br from-zinc-950 via-zinc-900 to-gold-600/30">
                <span class="text-5xl font-black text-gold-500">{{ \Illuminate\Support\Str::of($product->name)->substr(0, 1)->upper() }}</span>
            </div>
        @endif
        <span class="absolute right-3 top-3 rounded-full bg-zinc-950/85 px-3 py-1 text-xs font-black text-white shadow-lg ring-1 ring-white/15 dark:bg-gold-500 dark:text-zinc-950">Stok: {{ number_format($product->stock, 0, ',', '.') }}</span>
    </div>
    <div class="mt-5 flex min-w-0 flex-1 flex-col">
        <p class="break-words text-xs font-black uppercase tracking-[0.16em] text-gold-600 dark:text-gold-400">{{ $product->category?->name ?? 'Produk' }}</p>
        <h3 class="public-product-title mt-2 break-words text-lg font-black leading-snug text-zinc-950 dark:text-white">{{ $product->name }}</h3>
        <p class="mt-3 break-words text-2xl font-black leading-none text-zinc-950 dark:text-white">@include('public.partials.price', ['amount' => $product->price])</p>
        <p class="public-product-description mt-4 break-words text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $product->description ?? 'Pembelian tersedia langsung di lokasi Platinum Gym Padang.' }}</p>
    </div>
</article>
