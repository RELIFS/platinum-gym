<x-public-layout :settings="$settings" title="Layanan Platinum Gym Padang" description="Daftar layanan Platinum Gym Padang: membership gym, senam, personal trainer, Muaythai, dan Poundfit.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'Layanan',
        'title' => 'Paket latihan untuk setiap target.',
        'description' => 'Pilih membership, kelas, personal trainer, atau paket sesi sesuai kebutuhan latihan Anda.',
    ])

    @include('public.partials.promo-strip', [
        'promos' => $promos,
        'promoSectionId' => 'promo-layanan',
        'promoTitle' => 'Promo aktif untuk paket pilihan.',
        'promoDescription' => 'Gunakan daftar promo ini sebagai ringkasan sebelum melihat detail paket, harga, dan ketentuan layanan.',
        'primaryUrl' => '#paket-layanan',
        'primaryLabel' => 'Lihat Detail Paket',
        'secondaryUrl' => route('register'),
        'secondaryLabel' => 'Daftar Member',
    ])

    <section id="paket-layanan" class="public-section public-section-muted scroll-mt-24">
        <div class="public-container space-y-14">
            @forelse ($packagesByKind as $kind => $packages)
                <div class="public-motion-reveal" data-motion="reveal">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="public-eyebrow">{{ \Illuminate\Support\Str::headline($kind) }}</p>
                            <h2 class="public-heading-balance mt-3 text-3xl font-black text-zinc-950 dark:text-white">Paket {{ \Illuminate\Support\Str::headline($kind) }}</h2>
                        </div>
                        <a href="{{ route('register') }}" class="public-button-secondary">Daftar Paket</a>
                    </div>
                    <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($packages as $package)
                            @include('public.partials.package-card', ['package' => $package, 'settings' => $settings])
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="public-card">Data layanan belum tersedia.</div>
            @endforelse
        </div>
    </section>

    <section class="public-section public-section-plain">
        <div class="public-container grid gap-6 lg:grid-cols-3">
            <article class="public-card public-motion-card public-motion-reveal" data-motion="reveal card">
                <h2 class="text-xl font-black text-zinc-950 dark:text-white">Membership</h2>
                <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Paket bulanan untuk akses gym, senam, atau kombinasi sesuai kategori dan ketentuan.</p>
            </article>
            <article class="public-card public-motion-card public-motion-reveal" data-motion="reveal card" data-motion-delay="80">
                <h2 class="text-xl font-black text-zinc-950 dark:text-white">Paket Sesi</h2>
                <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Personal trainer dan Muaythai memakai jumlah sesi. Beberapa paket membutuhkan membership aktif.</p>
            </article>
            <article class="public-card public-motion-card public-motion-reveal" data-motion="reveal card" data-motion-delay="160">
                <h2 class="text-xl font-black text-zinc-950 dark:text-white">Booking</h2>
                <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Booking digital sedang disiapkan. Informasi paket, harga, dan ketentuan ditampilkan di website sebelum pembelian aktif melalui dashboard member.</p>
            </article>
        </div>
    </section>
</x-public-layout>
