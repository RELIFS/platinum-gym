@php
    $whatsappUrl = ($settings['whatsapp_url'] ?? 'https://wa.me/6282174777761');
    $productCtaUrl = $whatsappUrl.(str_contains($whatsappUrl, '?') ? '&' : '?').http_build_query([
        'text' => 'Halo Platinum Gym Padang, saya ingin tanya stok dan harga '.$product->name.'.',
    ]);
@endphp

<article class="public-card public-card-hover flex h-full flex-col">
    <div class="flex h-28 items-center justify-center rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-gold-600/30 ring-1 ring-white/10">
        <span class="text-4xl font-black text-gold-500">{{ \Illuminate\Support\Str::of($product->name)->substr(0, 1)->upper() }}</span>
    </div>
    <p class="mt-5 break-words text-xs font-black uppercase tracking-[0.18em] text-gold-600 dark:text-gold-400">{{ $product->category?->name ?? 'Produk' }}</p>
    <h3 class="mt-2 break-words text-lg font-black text-zinc-950 dark:text-white">{{ $product->name }}</h3>
    <p class="mt-3 break-words text-2xl font-black text-zinc-950 dark:text-white">@include('public.partials.price', ['amount' => $product->price])</p>
    <p class="mt-3 break-words text-sm leading-6 text-zinc-600 dark:text-zinc-400">Tanyakan stok dan rekomendasi produk ke admin sebelum pembelian.</p>
    <a href="{{ $productCtaUrl }}" target="_blank" rel="noopener noreferrer" class="public-button-secondary mt-auto w-full">Tanya Produk</a>
</article>
