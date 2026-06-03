<x-public-layout :settings="$settings" title="Layanan Platinum Gym Padang" description="Daftar layanan Platinum Gym Padang: membership gym, senam, personal trainer, Muaythai, dan Poundfit.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'Layanan',
        'title' => 'Paket latihan untuk setiap target.',
        'description' => 'Pilih membership, kelas, personal trainer, atau paket sesi sesuai kebutuhan latihan Anda.',
    ])

    @if ($promos->isNotEmpty())
        <section class="bg-gold-500 text-zinc-950">
            <div class="public-container py-6">
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($promos as $promo)
                        <article class="rounded-2xl border border-zinc-950/10 bg-white/30 p-5">
                            <p class="text-xs font-black uppercase tracking-[0.2em]">Promo Aktif</p>
                            <h2 class="mt-2 text-xl font-black">{{ $promo->title }}</h2>
                            <p class="mt-2 text-sm font-semibold leading-6 text-zinc-800">{{ $promo->description }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="public-section bg-zinc-50 dark:bg-zinc-950">
        <div class="public-container space-y-14">
            @forelse ($packagesByKind as $kind => $packages)
                <div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="public-eyebrow">{{ \Illuminate\Support\Str::headline($kind) }}</p>
                            <h2 class="mt-3 text-3xl font-black tracking-tight text-zinc-950 dark:text-white">Paket {{ \Illuminate\Support\Str::headline($kind) }}</h2>
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

    <section class="public-section bg-white dark:bg-zinc-900/40">
        <div class="public-container grid gap-6 lg:grid-cols-3">
            <article class="public-card">
                <h2 class="text-xl font-black text-zinc-950 dark:text-white">Membership</h2>
                <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Paket bulanan untuk akses gym, senam, atau kombinasi sesuai kategori dan ketentuan.</p>
            </article>
            <article class="public-card">
                <h2 class="text-xl font-black text-zinc-950 dark:text-white">Paket Sesi</h2>
                <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Personal trainer dan Muaythai memakai jumlah sesi. Beberapa paket membutuhkan membership aktif.</p>
            </article>
            <article class="public-card">
                <h2 class="text-xl font-black text-zinc-950 dark:text-white">Booking</h2>
                <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Booking digital sedang disiapkan. Untuk saat ini, masuk ke akun atau hubungi admin untuk arahan lanjut.</p>
            </article>
        </div>
    </section>
</x-public-layout>
