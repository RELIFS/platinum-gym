<x-public-layout :settings="$settings" title="Platinum Gym Padang - Premium Fitness Center Padang" description="Platinum Gym Padang menyediakan gym, senam, personal trainer, Muaythai, Poundfit, dan produk fitness di pusat Kota Padang.">
    <section class="relative overflow-hidden text-white bg-zinc-950">
        <div class="absolute inset-0 opacity-[0.10]" aria-hidden="true" style="background-image: linear-gradient(rgba(255,255,255,.16) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.16) 1px, transparent 1px); background-size: 56px 56px;"></div>
        <div class="public-container public-home-hero-grid">
            <div class="min-w-0 public-motion-reveal" data-motion="reveal">
                <p class="public-eyebrow">Premium Fitness Center Padang</p>
                <h1 class="public-home-hero-title public-heading-balance">
                    <span class="block text-gold-500">Platinum Gym</span>
                    <span class="block">Your Comfort Gym</span>
                    <span class="block">In Padang</span>
                </h1>
                <p class="max-w-2xl mt-4 text-base leading-7 text-zinc-300 sm:mt-6 sm:text-lg sm:leading-8">
                    Gym, senam, Muaythai, Poundfit, dan personal trainer dalam satu ekosistem yang rapi, mudah diakses, dan siap mendukung progres Anda.
                </p>

                <div class="public-home-hero-actions">
                    <a href="{{ route('register') }}" class="w-full public-button-primary public-motion-cta sm:w-auto" data-motion="cta">Daftar Member</a>
                    <a href="{{ route('public.services') }}" class="w-full text-white public-button-secondary border-white/10 bg-white/5 hover:text-gold-400 sm:w-auto">Lihat Layanan</a>
                </div>
                <a href="{{ route('public.location') }}" class="inline-flex items-center max-w-full py-1 pr-2 mt-3 text-sm font-bold break-words transition rounded-full min-h-11 touch-manipulation text-gold-400 hover:text-gold-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950">
                    Cek lokasi dan jam operasional
                </a>

                <div class="hidden gap-3 mt-10 lg:grid lg:grid-cols-3">
                    <div class="min-w-0 public-motion-reveal rounded-2xl border border-white/10 bg-white/[0.04] p-5" data-motion="reveal" data-motion-delay="80">
                        <p class="text-3xl font-black break-words text-gold-500">{{ $stats['packages'] }}</p>
                        <p class="mt-1 text-sm font-semibold break-words text-zinc-300">Paket aktif</p>
                    </div>
                    <div class="min-w-0 public-motion-reveal rounded-2xl border border-white/10 bg-white/[0.04] p-5" data-motion="reveal" data-motion-delay="120">
                        <p class="text-3xl font-black break-words text-gold-500">{{ $stats['classes'] }}</p>
                        <p class="mt-1 text-sm font-semibold break-words text-zinc-300">Jenis kelas</p>
                    </div>
                    <div class="min-w-0 public-motion-reveal rounded-2xl border border-white/10 bg-white/[0.04] p-5" data-motion="reveal" data-motion-delay="160">
                        <p class="text-3xl font-black break-words text-gold-500">{{ $stats['products'] }}</p>
                        <p class="mt-1 text-sm font-semibold break-words text-zinc-300">Produk katalog</p>
                    </div>
                </div>
            </div>

            @include('public.partials.home-hero-visual')

            <div class="public-home-stat-grid public-motion-reveal lg:hidden" data-motion="reveal" data-motion-delay="120">
                <div class="public-home-stat-card">
                    <p class="public-home-stat-value">{{ $stats['packages'] }}</p>
                    <p class="public-home-stat-label">Paket</p>
                </div>
                <div class="public-home-stat-card">
                    <p class="public-home-stat-value">{{ $stats['classes'] }}</p>
                    <p class="public-home-stat-label">Kelas</p>
                </div>
                <div class="public-home-stat-card">
                    <p class="public-home-stat-value">{{ $stats['products'] }}</p>
                    <p class="public-home-stat-label">Produk</p>
                </div>
            </div>
        </div>
    </section>

    @include('public.partials.promo-strip', [
        'promos' => $promos,
        'promoSectionId' => 'promo-beranda',
        'promoTitle' => 'Promo aktif untuk mulai lebih hemat.',
        'promoDescription' => 'Lihat promo terbaru, cek paket yang sesuai, lalu daftar member langsung dari website Platinum Gym Padang.',
        'primaryUrl' => route('public.services'),
        'primaryLabel' => 'Lihat Paket Promo',
        'secondaryUrl' => route('public.classes'),
        'secondaryLabel' => 'Cek Jadwal Kelas',
    ])

    <section class="public-section public-section-muted">
        <div class="public-container">
            <div class="public-home-section-heading">
                <p class="public-eyebrow">Layanan Utama</p>
                <h2 class="mt-3 text-3xl font-black public-heading-balance text-zinc-950 dark:text-white sm:text-4xl">Pilih program yang sesuai target latihan.</h2>
            </div>

            <div class="grid gap-5 mt-10 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($packages as $package)
                    @include('public.partials.package-card', ['package' => $package, 'settings' => $settings])
                @empty
                    <div class="public-card md:col-span-2 xl:col-span-3">Data layanan belum tersedia.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="public-section public-section-plain">
        <div class="public-container">
            <div class="public-home-section-toolbar">
                <div class="public-home-section-heading">
                    <p class="public-eyebrow">Jadwal Kelas</p>
                    <h2 class="mt-3 text-3xl font-black public-heading-balance text-zinc-950 dark:text-white sm:text-4xl">Kelas aktif minggu ini.</h2>
                </div>
                <a href="{{ route('public.classes') }}" class="w-full public-button-secondary sm:w-auto sm:shrink-0">Lihat Semua Jadwal</a>
            </div>

            <div class="grid gap-5 mt-10 md:grid-cols-2 xl:grid-cols-4">
                @forelse ($classSchedules as $schedule)
                    @include('public.partials.schedule-card', ['schedule' => $schedule, 'settings' => $settings, 'dayLabels' => $dayLabels])
                @empty
                    <div class="public-card md:col-span-2 xl:col-span-4">Jadwal kelas belum tersedia.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="public-section public-section-muted">
        <div class="public-container grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div class="min-w-0">
                <p class="public-eyebrow">Produk Fitness</p>
                <h2 class="mt-3 text-3xl font-black public-heading-balance text-zinc-950 dark:text-white sm:text-4xl">Dukungan nutrisi dan perlengkapan latihan.</h2>
                <p class="mt-5 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Produk ditampilkan sebagai katalog publik dengan harga dan stok aktual. Pembelian dilakukan langsung di lokasi Platinum Gym Padang.</p>
                <a href="{{ route('public.products') }}" class="w-full public-button-primary public-motion-cta mt-7 sm:w-auto" data-motion="cta">Lihat Produk</a>
            </div>
            <div class="grid min-w-0 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($products as $product)
                    @include('public.partials.product-card', ['product' => $product])
                @empty
                    <div class="public-card sm:col-span-2 xl:col-span-3">Data produk belum tersedia.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="public-section public-section-plain">
        <div class="public-container">
            <div class="public-home-section-toolbar">
                <div class="public-home-section-heading">
                    <p class="public-eyebrow">Galeri</p>
                    <h2 class="mt-3 text-3xl font-black public-heading-balance text-zinc-950 dark:text-white sm:text-4xl">Suasana latihan Platinum Gym.</h2>
                </div>
                <a href="{{ route('public.gallery') }}" class="w-full public-button-secondary sm:w-auto sm:shrink-0">Buka Galeri</a>
            </div>
            <div class="grid gap-5 mt-10 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($galleries as $item)
                    @include('public.partials.gallery-card', ['item' => $item, 'index' => $loop->index])
                @empty
                    <div class="public-card md:col-span-2 xl:col-span-3">Galeri belum tersedia.</div>
                @endforelse
            </div>
        </div>
    </section>

    @if ($testimonials->isNotEmpty())
        <section class="text-white public-section bg-zinc-950">
            <div class="public-container grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div class="min-w-0">
                    <p class="public-eyebrow">Testimoni</p>
                    <h2 class="mt-3 text-3xl font-black public-heading-balance sm:text-4xl">Member datang untuk latihan, kembali karena progres.</h2>
                    <p class="mt-5 text-sm leading-7 text-zinc-400">Pengalaman member dari kelas, gym, dan personal trainer.</p>
                </div>
                <div class="grid min-w-0 gap-5 md:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <article class="public-motion-card public-motion-reveal min-w-0 rounded-xl border border-white/10 bg-white/[0.05] p-5 sm:rounded-2xl sm:p-6" data-motion="reveal card" data-motion-delay="{{ ($loop->index % 3) * 80 }}">
                            <div class="flex flex-wrap items-center min-w-0 gap-2 mb-4 text-gold-400" aria-label="Rating {{ (int) $testimonial->rating }} dari 5">
                                <span aria-hidden="true" class="text-base tracking-[0.12em]">{{ str_repeat('★', max(0, min(5, (int) $testimonial->rating))) }}{{ str_repeat('☆', max(0, 5 - min(5, (int) $testimonial->rating))) }}</span>
                                <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-400">{{ (int) $testimonial->rating }}/5</span>
                            </div>
                            <p class="text-sm leading-7 break-words text-zinc-300">"{{ $testimonial->content }}"</p>
                            <div class="mt-6">
                                <p class="font-black text-white break-words">{{ $testimonial->name }}</p>
                                <p class="mt-1 break-words text-xs font-bold uppercase tracking-[0.16em] text-gold-400">{{ $testimonial->role }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    <section class="public-section public-section-muted">
        <div class="public-container">
            <div class="public-home-cta-frame">
                <div class="public-home-cta-panel">
                    <div class="grid min-w-0 gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div class="min-w-0">
                            <p class="public-eyebrow">Mulai Hari Ini</p>
                            <h2 class="mt-3 text-3xl font-black public-heading-balance text-zinc-950 dark:text-white sm:text-4xl">Siap mulai latihan di Platinum Gym Padang?</h2>
                            <p class="max-w-2xl mt-4 text-sm leading-7 text-zinc-600 dark:text-zinc-300">Daftar akun untuk masuk ke dashboard member, melihat layanan, dan mengikuti proses membership secara lebih rapi.</p>
                        </div>
                        <div class="public-home-cta-actions">
                            <a href="{{ route('register') }}" class="w-full public-button-primary public-motion-cta sm:w-auto" data-motion="cta">Daftar Member</a>
                            <a href="{{ route('public.location') }}" class="w-full public-button-secondary sm:w-auto">Lihat Lokasi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
