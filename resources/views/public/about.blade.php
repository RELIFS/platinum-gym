<x-public-layout :settings="$settings" title="Tentang Platinum Gym Padang" description="Profil Platinum Gym Padang, fasilitas, tim pelatih, dan nilai layanan fitness premium di Padang.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'Tentang Kami',
        'title' => 'Fitness center premium untuk progres yang konsisten.',
        'description' => 'Platinum Gym Padang hadir sebagai ruang latihan yang strategis, aktif, dan ramah untuk member pemula sampai berpengalaman.',
    ])

    <section class="public-section public-section-muted">
        <div class="public-container grid gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div>
                <p class="public-eyebrow">Profil</p>
                <h2 class="public-heading-balance mt-3 text-3xl font-black text-zinc-950 dark:text-white sm:text-4xl">Tempat latihan di pusat Kota Padang.</h2>
                <p class="mt-5 text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Platinum Gym Padang menggabungkan layanan gym, kelas grup, personal trainer, Muaythai, Poundfit, dan kebutuhan produk fitness. Website ini menjadi pintu masuk digital untuk mengenal layanan, melihat jadwal, dan mulai membuat akun member.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('public.services') }}" class="public-button-primary">Lihat Layanan</a>
                    <a href="{{ route('register') }}" class="public-button-secondary">Daftar Member</a>
                </div>
            </div>

            <div class="relative">
                <div class="relative overflow-hidden rounded-[2rem] border border-zinc-200 bg-white p-3 shadow-2xl dark:border-white/10 dark:bg-white/[0.04]">
                    <div class="relative aspect-[4/3] overflow-hidden rounded-[1.5rem] bg-zinc-950">
                        <img src="{{ asset('images/public/gallery/platinum-gym-padang-training-floor.webp') }}" alt="Training floor dan alat strength Platinum Gym Padang" class="h-full w-full object-cover" width="600" height="336" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/90 via-zinc-950/20 to-transparent"></div>
                        <div class="absolute bottom-5 left-5 right-5 text-white">
                            <p class="public-eyebrow">Suasana Gym</p>
                            <h3 class="mt-2 text-2xl font-black">Tim pelatih, kelas, dan latihan dalam satu ekosistem.</h3>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-zinc-50 p-4 text-center dark:bg-zinc-950/80">
                            <p class="text-3xl font-black text-gold-600 dark:text-gold-400">{{ $stats['packages'] }}</p>
                            <p class="mt-1 text-xs font-bold text-zinc-600 dark:text-zinc-400">Paket aktif</p>
                        </div>
                        <div class="rounded-2xl bg-zinc-50 p-4 text-center dark:bg-zinc-950/80">
                            <p class="text-3xl font-black text-gold-600 dark:text-gold-400">{{ $stats['classes'] }}</p>
                            <p class="mt-1 text-xs font-bold text-zinc-600 dark:text-zinc-400">Jenis kelas</p>
                        </div>
                        <div class="rounded-2xl bg-zinc-50 p-4 text-center dark:bg-zinc-950/80">
                            <p class="text-3xl font-black text-gold-600 dark:text-gold-400">{{ $stats['trainers'] }}</p>
                            <p class="mt-1 text-xs font-bold text-zinc-600 dark:text-zinc-400">Tim pelatih aktif</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="public-section public-section-plain">
        <div class="public-container">
            <div class="max-w-3xl">
                <p class="public-eyebrow">Keunggulan</p>
                <h2 class="public-heading-balance mt-3 text-3xl font-black text-zinc-950 dark:text-white sm:text-4xl">Dirancang untuk latihan yang jelas, aman, dan berkelanjutan.</h2>
            </div>
            <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['Fasilitas lengkap', 'Latihan beban, cardio, kelas grup, dan sesi privat dalam satu tempat.'],
                    ['Tim aktif', 'Tim pelatih membantu member berlatih sesuai kebutuhan.'],
                    ['Lokasi strategis', 'Berada di Sawahan, Padang Timur, mudah dicapai dari area pusat kota.'],
                    ['Ekosistem digital', 'Akun member, dashboard, dan booking disiapkan bertahap untuk pengalaman yang lebih rapi.'],
                ] as [$title, $body])
                    <article class="public-card public-card-hover">
                        <h3 class="text-lg font-black text-zinc-950 dark:text-white">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-400">{{ $body }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="public-section public-section-muted">
        <div class="public-container">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-3xl">
                    <p class="public-eyebrow">Tim Pelatih</p>
                    <h2 class="public-heading-balance mt-3 text-3xl font-black text-zinc-950 dark:text-white sm:text-4xl">Pendamping latihan sesuai program.</h2>
                </div>
                <a href="{{ route('public.classes') }}" class="public-button-secondary">Lihat Jadwal</a>
            </div>

            <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @forelse ($trainers as $trainer)
                    @php
                        $trainerDisplayName = \App\Features\PublicWebsite\Support\PublicTrainerPresenter::displayName($trainer);
                        $trainerRole = \App\Features\PublicWebsite\Support\PublicTrainerPresenter::roleWithSpecialization($trainer);
                        $trainerInitial = \App\Features\PublicWebsite\Support\PublicTrainerPresenter::initial($trainer);
                    @endphp
                    <article class="public-card public-card-hover">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-950 text-2xl font-black text-gold-500 dark:bg-gold-500 dark:text-zinc-950">
                            {{ $trainerInitial }}
                        </div>
                        <h3 class="mt-5 text-lg font-black text-zinc-950 dark:text-white">{{ $trainerDisplayName }}</h3>
                        <p class="mt-2 text-sm font-bold text-gold-600 dark:text-gold-400">{{ $trainerRole }}</p>
                        <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $trainer->bio ?? 'Mendampingi member sesuai program dan jadwal yang tersedia.' }}</p>
                    </article>
                @empty
                    <div class="public-card md:col-span-2 xl:col-span-4">Data tim pelatih belum tersedia.</div>
                @endforelse
            </div>
        </div>
    </section>
</x-public-layout>
