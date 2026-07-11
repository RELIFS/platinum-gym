<x-public-layout :settings="$settings" title="Galeri Platinum Gym Padang" description="Galeri aktivitas, kelas, personal trainer, dan suasana latihan Platinum Gym Padang.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'Galeri',
        'title' => 'Cuplikan suasana latihan di Platinum Gym.',
        'description' => 'Lihat dokumentasi aktivitas kelas, personal trainer, dan suasana latihan Platinum Gym Padang.',
        'primaryUrl' => '#galeri-latihan',
        'primaryLabel' => 'Lihat Galeri',
        'secondaryUrl' => route('public.classes'),
        'secondaryLabel' => 'Lihat Kelas',
    ])

    <section id="galeri-latihan" class="public-section public-section-muted scroll-mt-24">
        <div class="public-container">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($galleries as $item)
                    @include('public.partials.gallery-card', ['item' => $item, 'index' => $loop->index])
                @empty
                    <div class="public-card md:col-span-2 xl:col-span-3">
                        <h2 class="text-xl type-title text-zinc-950 dark:text-zinc-100">Galeri belum tersedia.</h2>
                        <p class="mt-2 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Konten galeri akan ditampilkan setelah data dipublikasi.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-public-layout>
