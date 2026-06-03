<x-public-layout :settings="$settings" title="Produk Platinum Gym Padang" description="Katalog produk makanan, minuman, suplemen, dan perlengkapan fitness di Platinum Gym Padang.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'Produk',
        'title' => 'Katalog pendukung latihan dan nutrisi.',
        'description' => 'Cari produk berdasarkan kategori atau nama. Stok dan pemesanan dikonfirmasi melalui WhatsApp admin.',
    ])

    <section class="public-section bg-zinc-50 dark:bg-zinc-950">
        <div class="public-container">
            <form method="GET" action="{{ route('public.products') }}" class="public-card grid gap-4 lg:grid-cols-[0.85fr_1fr_auto] lg:items-end" aria-describedby="products-filter-status">
                <div>
                    <label for="kategori" class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-300">Kategori</label>
                    <select id="kategori" name="kategori" class="public-input">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected($selectedCategory?->id === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="q" class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-300">Cari produk</label>
                    <input id="q" name="q" type="search" value="{{ $search }}" class="public-input" placeholder="Contoh: Whey, Aqua, Glove">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="public-button-primary w-full lg:w-auto">Cari</button>
                    <a href="{{ route('public.products') }}" class="public-button-secondary w-full lg:w-auto">Reset</a>
                </div>
            </form>

            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <a href="{{ route('public.products', ['kategori' => $category->slug]) }}" @if ($selectedCategory?->id === $category->id) aria-current="page" @endif class="max-w-full break-words rounded-full px-4 py-2 text-sm font-bold transition {{ $selectedCategory?->id === $category->id ? 'bg-gold-500 text-zinc-950' : 'bg-white text-zinc-700 hover:text-gold-600 dark:bg-white/10 dark:text-zinc-300 dark:hover:text-gold-400' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            <p id="products-filter-status" class="mt-5 break-words text-sm font-semibold text-zinc-600 dark:text-zinc-400" role="status">
                Menampilkan {{ $products->count() }} produk{{ $selectedCategory ? ' kategori '.$selectedCategory->name : '' }}{{ $search !== '' ? ' untuk pencarian "'.$search.'"' : '' }}.
            </p>

            <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($products as $product)
                    @include('public.partials.product-card', ['product' => $product, 'settings' => $settings])
                @empty
                    <div class="public-card sm:col-span-2 xl:col-span-4">
                        <h2 class="text-xl font-black text-zinc-950 dark:text-white">Produk tidak ditemukan.</h2>
                        <p class="mt-2 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Coba kata kunci lain atau reset filter.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-public-layout>
