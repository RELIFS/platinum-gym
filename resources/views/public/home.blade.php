<x-public-layout :settings="$settings" title="Platinum Gym Padang - Premium Fitness Center Padang" description="Platinum Gym Padang menyediakan gym, senam, personal trainer, Muaythai, Poundfit, dan produk fitness di pusat Kota Padang.">
    <section class="relative overflow-hidden bg-zinc-950 text-white">
    <div class="absolute inset-0 opacity-[0.10]" aria-hidden="true" style="background-image: linear-gradient(rgba(255,255,255,.16) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.16) 1px, transparent 1px); background-size: 56px 56px;"></div>
        <div class="public-container relative grid gap-8 py-8 sm:gap-10 sm:py-12 lg:min-h-[calc(90dvh-5rem)] lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:gap-12 lg:py-20">
            <div>
                <p class="public-eyebrow">Premium Fitness Center Padang</p>
                <h1 class="public-heading-balance mt-4 max-w-4xl text-4xl font-black leading-[0.98] sm:mt-5 sm:text-6xl lg:text-7xl">
                    Push Your <span class="text-gold-500">Limits</span> di Platinum Gym Padang.
                </h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-zinc-300 sm:mt-6 sm:text-lg sm:leading-8">
                    Latihan gym, senam, Muaythai, Poundfit, dan personal trainer dalam satu ekosistem yang rapi, mudah diakses, dan siap mendukung progres Anda.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap lg:mt-8">
                    <a href="{{ route('register') }}" class="public-button-primary">Daftar Member</a>
                    <a href="{{ route('public.services') }}" class="public-button-secondary border-white/10 bg-white/5 text-white hover:text-gold-400">Lihat Layanan</a>
                </div>
                <a href="{{ route('public.location') }}" class="mt-3 inline-flex min-h-11 touch-manipulation items-center rounded-full text-sm font-bold text-gold-400 transition hover:text-gold-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950">
                    Cek lokasi dan jam operasional
                </a>

                <div class="mt-10 hidden gap-3 lg:grid lg:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                        <p class="text-3xl font-black text-gold-500">{{ $stats['packages'] }}</p>
                        <p class="mt-1 text-sm font-semibold text-zinc-300">Paket aktif</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                        <p class="text-3xl font-black text-gold-500">{{ $stats['classes'] }}</p>
                        <p class="mt-1 text-sm font-semibold text-zinc-300">Jenis kelas</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                        <p class="text-3xl font-black text-gold-500">{{ $stats['products'] }}</p>
                        <p class="mt-1 text-sm font-semibold text-zinc-300">Produk katalog</p>
                    </div>
                </div>
            </div>

            @include('public.partials.home-hero-visual')

            <div class="grid grid-cols-3 gap-2 lg:hidden">
                <div class="rounded-xl border border-white/10 bg-white/[0.04] p-3">
                    <p class="text-2xl font-black leading-none text-gold-500">{{ $stats['packages'] }}</p>
                    <p class="mt-2 text-[11px] font-semibold leading-4 text-zinc-300">Paket</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/[0.04] p-3">
                    <p class="text-2xl font-black leading-none text-gold-500">{{ $stats['classes'] }}</p>
                    <p class="mt-2 text-[11px] font-semibold leading-4 text-zinc-300">Kelas</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/[0.04] p-3">
                    <p class="text-2xl font-black leading-none text-gold-500">{{ $stats['products'] }}</p>
                    <p class="mt-2 text-[11px] font-semibold leading-4 text-zinc-300">Produk</p>
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
            <div class="max-w-3xl">
                <p class="public-eyebrow">Layanan Utama</p>
                <h2 class="public-heading-balance mt-3 text-3xl font-black text-zinc-950 dark:text-white sm:text-4xl">Pilih program yang sesuai target latihan.</h2>
            </div>

            <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
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
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-3xl">
                    <p class="public-eyebrow">Jadwal Kelas</p>
                    <h2 class="public-heading-balance mt-3 text-3xl font-black text-zinc-950 dark:text-white sm:text-4xl">Kelas aktif minggu ini.</h2>
                </div>
                <a href="{{ route('public.classes') }}" class="public-button-secondary">Lihat Semua Jadwal</a>
            </div>

            <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
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
            <div>
                <p class="public-eyebrow">Produk Fitness</p>
                <h2 class="public-heading-balance mt-3 text-3xl font-black text-zinc-950 dark:text-white sm:text-4xl">Dukungan nutrisi dan perlengkapan latihan.</h2>
                <p class="mt-5 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Produk ditampilkan sebagai katalog publik dengan harga dan stok aktual. Pembelian dilakukan langsung di lokasi Platinum Gym Padang.</p>
                <a href="{{ route('public.products') }}" class="public-button-primary mt-7">Lihat Produk</a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
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
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-3xl">
                    <p class="public-eyebrow">Galeri</p>
                    <h2 class="public-heading-balance mt-3 text-3xl font-black text-zinc-950 dark:text-white sm:text-4xl">Suasana latihan Platinum Gym.</h2>
                </div>
                <a href="{{ route('public.gallery') }}" class="public-button-secondary">Buka Galeri</a>
            </div>
            <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($galleries as $item)
                    @include('public.partials.gallery-card', ['item' => $item, 'index' => $loop->index])
                @empty
                    <div class="public-card md:col-span-2 xl:col-span-3">Galeri belum tersedia.</div>
                @endforelse
            </div>
        </div>
    </section>

    @if ($testimonials->isNotEmpty())
        <section class="public-section bg-zinc-950 text-white">
            <div class="public-container grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div>
                    <p class="public-eyebrow">Testimoni</p>
                    <h2 class="public-heading-balance mt-3 text-3xl font-black sm:text-4xl">Member datang untuk latihan, kembali karena progres.</h2>
                    <p class="mt-5 text-sm leading-7 text-zinc-400">Pengalaman member dari kelas, gym, dan personal trainer.</p>
                </div>
                <div class="grid gap-5 md:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <article class="rounded-2xl border border-white/10 bg-white/[0.05] p-6">
                            <p class="break-words text-sm leading-7 text-zinc-300">"{{ $testimonial->content }}"</p>
                            <div class="mt-6">
                                <p class="break-words font-black text-white">{{ $testimonial->name }}</p>
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
            <div class="overflow-hidden rounded-[2rem] bg-zinc-950 p-6 text-white shadow-2xl sm:p-10 lg:p-12">
                <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <p class="public-eyebrow">Mulai Hari Ini</p>
                        <h2 class="public-heading-balance mt-3 text-3xl font-black sm:text-4xl">Siap mulai latihan di Platinum Gym Padang?</h2>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-zinc-300">Daftar akun untuk masuk ke dashboard member, melihat layanan, dan mengikuti proses membership secara lebih rapi.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row">
                        <a href="{{ route('register') }}" class="public-button-primary">Daftar Member</a>
                        <a href="{{ route('public.location') }}" class="public-button-secondary border-white/10 bg-white/5 text-white hover:text-gold-400">Lihat Lokasi</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
