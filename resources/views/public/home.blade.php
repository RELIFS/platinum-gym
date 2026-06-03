<x-public-layout :settings="$settings" title="Platinum Gym Padang - Premium Fitness Center Padang" description="Platinum Gym Padang menyediakan gym, senam, personal trainer, Muaythai, Poundfit, dan produk fitness di pusat Kota Padang.">
    <section class="relative overflow-hidden bg-zinc-950 text-white">
        <div class="absolute inset-0 opacity-[0.14]" style="background-image: linear-gradient(rgba(255,255,255,.16) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.16) 1px, transparent 1px); background-size: 48px 48px;"></div>
        <div class="absolute -left-24 top-24 h-64 w-64 rounded-full bg-gold-500/12 blur-3xl"></div>

        <div class="public-container relative grid gap-8 py-8 sm:gap-10 sm:py-12 lg:min-h-[calc(90dvh-5rem)] lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:gap-12 lg:py-20">
            <div>
                <p class="public-eyebrow">Premium Fitness Center Padang</p>
                <h1 class="public-heading-balance mt-4 max-w-4xl text-4xl font-black leading-[0.98] tracking-tight sm:mt-5 sm:text-6xl lg:text-7xl">
                    Push Your <span class="text-gold-500">Limits</span> di Platinum Gym Padang.
                </h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-zinc-300 sm:mt-6 sm:text-lg sm:leading-8">
                    Latihan gym, senam, Muaythai, Poundfit, dan personal trainer dalam satu ekosistem yang rapi, mudah diakses, dan siap mendukung progres Anda.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap lg:mt-8">
                    <a href="{{ route('register') }}" class="public-button-primary">Daftar Member</a>
                    <a href="{{ route('public.services') }}" class="public-button-secondary border-white/10 bg-white/5 text-white hover:text-gold-400">Lihat Layanan</a>
                </div>
                <a href="{{ $settings['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex min-h-11 touch-manipulation items-center rounded-full text-sm font-bold text-gold-400 transition hover:text-gold-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950">
                    Tanya WhatsApp untuk konsultasi cepat
                </a>

                <div class="mt-10 hidden gap-3 lg:grid lg:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                        <p class="text-3xl font-black text-gold-500">6+</p>
                        <p class="mt-1 text-sm font-semibold text-zinc-300">Kategori layanan</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                        <p class="text-3xl font-black text-gold-500">PT</p>
                        <p class="mt-1 text-sm font-semibold text-zinc-300">Personal trainer</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5">
                        <p class="text-3xl font-black text-gold-500">WA</p>
                        <p class="mt-1 text-sm font-semibold text-zinc-300">Konsultasi cepat</p>
                    </div>
                </div>
            </div>

            <div class="relative lg:min-h-[34rem]">
                <div class="absolute inset-10 hidden rounded-[2.5rem] bg-gold-500/12 blur-3xl lg:block"></div>
                <div class="relative overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/[0.05] p-2 shadow-2xl backdrop-blur lg:absolute lg:right-0 lg:top-0 lg:w-[72%] lg:rounded-[2rem] lg:p-3 xl:w-[68%]">
                    <div class="relative aspect-[16/10] overflow-hidden rounded-[1.15rem] bg-zinc-900 lg:aspect-[3/4] lg:rounded-[1.5rem]">
                        <img src="{{ asset('images/public/gallery/platinum-gym-padang-instagram-05.webp') }}" alt="Latihan strength training di Platinum Gym Padang" class="h-full w-full object-cover" width="540" height="960" fetchpriority="high">
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/10 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 right-4 sm:bottom-5 sm:left-5 sm:right-5">
                            <span class="rounded-full bg-gold-500 px-3 py-2 text-[11px] font-black uppercase tracking-[0.16em] text-zinc-950 sm:px-4 sm:text-xs">Training Floor</span>
                            <p class="mt-3 text-xl font-black sm:text-2xl lg:mt-4">Gym, Kelas & PT</p>
                        </div>
                    </div>
                </div>
                <div class="absolute left-0 top-16 hidden w-[46%] overflow-hidden rounded-[1.5rem] border border-white/10 bg-zinc-900 p-2 shadow-2xl sm:w-[42%] lg:block">
                    <div class="relative aspect-[3/4] overflow-hidden rounded-[1.15rem]">
                        <img src="{{ asset('images/public/gallery/platinum-gym-padang-instagram-01.webp') }}" alt="Sesi personal trainer Platinum Gym Padang" class="h-full w-full object-cover" width="540" height="960" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/50 to-transparent"></div>
                    </div>
                </div>
                <div class="absolute bottom-0 left-20 hidden w-[48%] overflow-hidden rounded-[1.5rem] border border-white/10 bg-zinc-900 p-2 shadow-2xl sm:left-28 sm:w-[40%] lg:block">
                    <div class="relative aspect-[4/5] overflow-hidden rounded-[1.15rem]">
                        <img src="{{ asset('images/public/gallery/platinum-gym-padang-instagram-03.webp') }}" alt="Kelas Muaythai dan Boxing Platinum Gym Padang" class="h-full w-full object-cover" width="540" height="960" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/50 to-transparent"></div>
                    </div>
                </div>
                <div class="absolute bottom-8 right-6 hidden rounded-2xl border border-white/10 bg-zinc-950/85 p-5 shadow-2xl backdrop-blur lg:block">
                    <p class="text-4xl font-black text-gold-500">3</p>
                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-zinc-300">Program populer</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 lg:hidden">
                <div class="rounded-xl border border-white/10 bg-white/[0.04] p-3">
                    <p class="text-2xl font-black leading-none text-gold-500">6+</p>
                    <p class="mt-2 text-[11px] font-semibold leading-4 text-zinc-300">Layanan</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/[0.04] p-3">
                    <p class="text-2xl font-black leading-none text-gold-500">PT</p>
                    <p class="mt-2 text-[11px] font-semibold leading-4 text-zinc-300">Trainer</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/[0.04] p-3">
                    <p class="text-2xl font-black leading-none text-gold-500">WA</p>
                    <p class="mt-2 text-[11px] font-semibold leading-4 text-zinc-300">Konsultasi</p>
                </div>
            </div>
        </div>
    </section>

    @if ($promos->isNotEmpty())
        <section class="bg-gold-500 text-zinc-950">
            <div class="public-container py-6">
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($promos as $promo)
                        <div class="rounded-2xl border border-zinc-950/10 bg-white/30 p-5">
                            <p class="text-xs font-black uppercase tracking-[0.2em]">Promo Aktif</p>
                            <h2 class="mt-2 text-xl font-black">{{ $promo->title }}</h2>
                            <p class="mt-2 text-sm font-semibold leading-6 text-zinc-800">{{ $promo->description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="public-section bg-zinc-50 dark:bg-zinc-950">
        <div class="public-container">
            <div class="max-w-3xl">
                <p class="public-eyebrow">Layanan Utama</p>
                <h2 class="public-heading-balance mt-3 text-3xl font-black tracking-tight text-zinc-950 dark:text-white sm:text-4xl">Pilih program yang sesuai target latihan.</h2>
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

    <section class="public-section bg-white dark:bg-zinc-900/40">
        <div class="public-container">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-3xl">
                    <p class="public-eyebrow">Jadwal Kelas</p>
                    <h2 class="public-heading-balance mt-3 text-3xl font-black tracking-tight text-zinc-950 dark:text-white sm:text-4xl">Kelas aktif minggu ini.</h2>
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

    <section class="public-section bg-zinc-50 dark:bg-zinc-950">
        <div class="public-container grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div>
                <p class="public-eyebrow">Produk Fitness</p>
                <h2 class="public-heading-balance mt-3 text-3xl font-black tracking-tight text-zinc-950 dark:text-white sm:text-4xl">Dukungan nutrisi dan perlengkapan latihan.</h2>
                <p class="mt-5 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Produk ditampilkan sebagai katalog publik. Stok dan pembelian dikonfirmasi melalui admin.</p>
                <a href="{{ route('public.products') }}" class="public-button-primary mt-7">Lihat Produk</a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($products as $product)
                    @include('public.partials.product-card', ['product' => $product, 'settings' => $settings])
                @empty
                    <div class="public-card sm:col-span-2 xl:col-span-3">Data produk belum tersedia.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="public-section bg-white dark:bg-zinc-900/40">
        <div class="public-container">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-3xl">
                    <p class="public-eyebrow">Galeri</p>
                    <h2 class="public-heading-balance mt-3 text-3xl font-black tracking-tight text-zinc-950 dark:text-white sm:text-4xl">Suasana latihan Platinum Gym.</h2>
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

    <section class="public-section bg-zinc-950 text-white">
        <div class="public-container grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div>
                <p class="public-eyebrow">Testimoni</p>
                <h2 class="public-heading-balance mt-3 text-3xl font-black tracking-tight sm:text-4xl">Member datang untuk latihan, kembali karena progres.</h2>
                <p class="mt-5 text-sm leading-7 text-zinc-400">Pengalaman member dari kelas, gym, dan personal trainer.</p>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                @forelse ($testimonials as $testimonial)
                    <article class="rounded-2xl border border-white/10 bg-white/[0.05] p-6">
                        <p class="break-words text-sm leading-7 text-zinc-300">"{{ $testimonial->content }}"</p>
                        <div class="mt-6">
                            <p class="break-words font-black text-white">{{ $testimonial->name }}</p>
                            <p class="mt-1 break-words text-xs font-bold uppercase tracking-[0.16em] text-gold-400">{{ $testimonial->role }}</p>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-6 md:col-span-3">Testimoni belum tersedia.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="public-section bg-zinc-50 dark:bg-zinc-950">
        <div class="public-container">
            <div class="overflow-hidden rounded-[2rem] bg-zinc-950 p-6 text-white shadow-2xl sm:p-10 lg:p-12">
                <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <p class="public-eyebrow">Mulai Hari Ini</p>
                        <h2 class="public-heading-balance mt-3 text-3xl font-black tracking-tight sm:text-4xl">Siap mulai latihan di Platinum Gym Padang?</h2>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-zinc-300">Daftar akun untuk masuk ke dashboard member, atau hubungi admin melalui WhatsApp untuk konsultasi paket.</p>
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
