<x-public-layout :settings="$settings" title="Galeri Platinum Gym Padang" description="Galeri aktivitas, kelas, personal trainer, dan suasana latihan Platinum Gym Padang.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'Galeri',
        'title' => 'Cuplikan suasana latihan di Platinum Gym.',
        'description' => 'Lihat dokumentasi aktivitas kelas, personal trainer, dan suasana latihan Platinum Gym Padang.',
    ])

    <section class="public-section bg-zinc-50 dark:bg-zinc-950">
        <div class="public-container">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($galleries as $item)
                    @include('public.partials.gallery-card', ['item' => $item, 'index' => $loop->index])
                @empty
                    <div class="public-card md:col-span-2 xl:col-span-3">
                        <h2 class="text-xl font-black text-zinc-950 dark:text-white">Galeri belum tersedia.</h2>
                        <p class="mt-2 text-sm leading-7 text-zinc-600 dark:text-zinc-400">Konten galeri akan ditampilkan setelah data dipublikasi.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-public-layout>
