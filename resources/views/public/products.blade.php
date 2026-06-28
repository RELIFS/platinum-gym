<x-public-layout :settings="$settings" title="Produk Platinum Gym Padang" description="Katalog produk makanan, minuman, suplemen, dan perlengkapan fitness di Platinum Gym Padang.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'Produk',
        'title' => 'Katalog pendukung latihan dan nutrisi.',
        'description' => 'Cari produk berdasarkan kategori atau nama. Website menampilkan katalog, harga, foto, dan stok aktual untuk pembelian langsung di lokasi Platinum Gym Padang.',
    ])

    <section class="public-section public-section-muted">
        <div class="public-container">
            <div class="public-product-notice public-motion-reveal mb-8 grid gap-5 pr-20 sm:pr-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center lg:pr-6" data-motion="reveal">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">Informasi Pembelian</p>
                    <p class="mt-2 max-w-3xl break-words text-sm font-semibold leading-7 text-zinc-700 dark:text-zinc-200">Pembelian produk dilakukan langsung di lokasi Platinum Gym Padang. Stok diperbarui oleh admin sesuai ketersediaan di lokasi.</p>
                    <dl class="mt-5 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan katalog produk">
                        <div class="public-product-stat">
                            <dt>Total Produk</dt>
                            <dd>{{ $products->count() }}</dd>
                        </div>
                        <div class="public-product-stat">
                            <dt>Kategori</dt>
                            <dd>{{ $selectedCategory?->name ?? 'Semua' }}</dd>
                        </div>
                        <div class="public-product-stat">
                            <dt>Pembelian</dt>
                            <dd>Di Lokasi</dd>
                        </div>
                    </dl>
                </div>
                <a href="{{ route('public.location') }}" class="public-button-primary public-motion-cta w-full max-w-[calc(100%-4rem)] sm:max-w-full lg:w-auto" data-motion="cta">Lihat Lokasi</a>
            </div>

            <form method="GET" action="{{ route('public.products') }}" class="public-product-filter public-motion-reveal grid gap-4 pr-20 sm:pr-5 md:p-7 lg:grid-cols-[0.85fr_1fr_auto] lg:items-end" aria-describedby="products-filter-status" data-motion="reveal" data-motion-delay="80">
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
                    <input id="q" name="q" type="search" value="{{ $search }}" class="public-input" placeholder="Contoh: Whey, Aqua, Glove" autocomplete="off" autocapitalize="none" spellcheck="false">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="public-button-primary w-full lg:w-auto">Cari</button>
                    <a href="{{ route('public.products') }}" class="public-button-secondary w-full lg:w-auto">Reset</a>
                </div>
            </form>

            <nav class="mt-8 flex flex-wrap gap-2 pb-16 pr-20 sm:pb-0 sm:pr-0" aria-label="Filter kategori produk">
                @foreach ($categories as $category)
                    <a href="{{ route('public.products', ['kategori' => $category->slug]) }}" @if ($selectedCategory?->id === $category->id) aria-current="page" @endif class="public-filter-chip {{ $selectedCategory?->id === $category->id ? 'bg-gold-500 text-zinc-950 shadow-[0_14px_32px_rgba(254,172,24,0.22)]' : 'bg-white text-zinc-700 hover:text-gold-600 dark:bg-white/10 dark:text-zinc-300 dark:hover:text-gold-400' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </nav>

            <p id="products-filter-status" class="mt-5 break-words text-sm font-semibold text-zinc-600 dark:text-zinc-400" role="status">
                Menampilkan {{ $products->count() }} produk{{ $selectedCategory ? ' kategori '.$selectedCategory->name : '' }}{{ $search !== '' ? ' untuk pencarian "'.$search.'"' : '' }}.
            </p>

            <div class="mt-10 grid items-stretch gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($products as $product)
                    @include('public.partials.product-card', ['product' => $product])
                @empty
                    <div class="public-card sm:col-span-2 lg:col-span-3 xl:col-span-4">
                        <h2 class="text-xl font-black text-zinc-950 dark:text-white">Produk tidak ditemukan.</h2>
                        <p class="mt-2 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Coba kata kunci lain atau reset filter.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-public-layout>
