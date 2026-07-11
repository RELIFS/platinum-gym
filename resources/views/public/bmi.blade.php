<x-public-layout :settings="$settings" title="Kalkulator BMI Platinum Gym Padang" description="Hitung Body Mass Index sebagai referensi umum sebelum mulai program latihan di Platinum Gym Padang.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'BMI Calculator',
        'title' => 'Cek BMI sebagai referensi awal latihan.',
        'description' => 'Masukkan berat dan tinggi badan. Data tidak dikirim ke server dan tidak disimpan.',
        'primaryUrl' => '#bmi-calculator',
        'primaryLabel' => 'Hitung BMI',
        'secondaryUrl' => route('public.services'),
        'secondaryLabel' => 'Lihat Program',
    ])

    <section id="bmi-calculator" class="public-section public-section-muted scroll-mt-24">
        <div class="public-container grid gap-8 xl:grid-cols-[22rem_minmax(0,1fr)] xl:items-start 2xl:grid-cols-[24rem_minmax(0,1fr)]">
            <aside class="public-card public-motion-card public-motion-reveal xl:p-5" data-motion="reveal card">
                <p class="public-eyebrow">Catatan</p>
                <h2 class="mt-3 text-2xl type-title text-zinc-950 dark:text-zinc-100">BMI bukan diagnosis medis.</h2>
                <p class="mt-4 text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    BMI hanya referensi awal. Untuk program spesifik, konsultasikan dengan coach atau tenaga kesehatan bila punya kondisi medis, riwayat cedera, atau target perubahan berat badan yang agresif.
                </p>

                <div class="mt-7 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                <a href="{{ route('public.services') }}" class="public-button-primary public-motion-cta" data-motion="cta">Lihat Program Latihan</a>
                    <a href="{{ route('public.location') }}" class="public-button-secondary">Konsultasi di Gym</a>
                </div>
            </aside>

            <div
                class="public-card public-motion-reveal overflow-hidden"
                data-motion="reveal"
                data-motion-delay="100"
                x-data="{
                    weight: '',
                    height: '',
                    categories: [
                        { label: 'Bobot terlalu rendah', range: '&lt;= 15.9', min: null, max: 16, tone: 'bg-sky-500/12 text-sky-800 ring-sky-500/25 dark:text-sky-200' },
                        { label: 'Sangat kurang bobot', range: '16.0 - 16.9', min: 16, max: 17, tone: 'bg-cyan-500/12 text-cyan-800 ring-cyan-500/25 dark:text-cyan-200' },
                        { label: 'Kurang bobot', range: '17.0 - 18.4', min: 17, max: 18.5, tone: 'bg-blue-500/12 text-blue-800 ring-blue-500/25 dark:text-blue-200' },
                        { label: 'Normal', range: '18.5 - 24.9', min: 18.5, max: 25, tone: 'bg-emerald-500/12 text-emerald-800 ring-emerald-500/25 dark:text-emerald-200' },
                        { label: 'Kelebihan bobot', range: '25.0 - 29.9', min: 25, max: 30, tone: 'bg-amber-500/14 text-amber-800 ring-amber-500/25 dark:text-amber-200' },
                        { label: 'Obesitas kelas I', range: '30.0 - 34.9', min: 30, max: 35, tone: 'bg-orange-500/14 text-orange-800 ring-orange-500/25 dark:text-orange-200' },
                        { label: 'Obesitas kelas II', range: '35.0 - 39.9', min: 35, max: 40, tone: 'bg-red-500/14 text-red-800 ring-red-500/25 dark:text-red-200' },
                        { label: 'Obesitas kelas III', range: '&gt;= 40.0', min: 40, max: null, tone: 'bg-rose-500/14 text-rose-800 ring-rose-500/25 dark:text-rose-200' },
                    ],
                    get validWeight() { return Number(this.weight) >= 20 && Number(this.weight) <= 300 },
                    get validHeight() { return Number(this.height) >= 80 && Number(this.height) <= 250 },
                    get bmi() {
                        if (!this.validWeight || !this.validHeight) return null;
                        const meter = Number(this.height) / 100;
                        return Number(this.weight) / (meter * meter);
                    },
                    get rounded() { return this.bmi ? this.bmi.toFixed(1) : '-' },
                    get activeCategory() {
                        if (!this.bmi) return null;
                        return this.categories.find((category) => {
                            const aboveMin = category.min === null || this.bmi >= category.min;
                            const belowMax = category.max === null || this.bmi < category.max;

                            return aboveMin && belowMax;
                        });
                    },
                    get category() { return this.activeCategory ? this.activeCategory.label : 'Masukkan data valid' },
                    get categoryRange() { return this.activeCategory ? this.activeCategory.range : '20-300 kg dan 80-250 cm' },
                    get recommendation() {
                        if (!this.bmi) return 'Isi berat dan tinggi dalam rentang valid untuk melihat hasil IMT.';
                        if (this.bmi < 18.5) return 'Fokus pada latihan kekuatan, asupan kalori cukup, dan progres bertahap.';
                        if (this.bmi < 25) return 'Pertahankan rutinitas latihan dan kombinasikan strength dengan cardio.';
                        if (this.bmi < 30) return 'Mulai dengan program konsisten, defisit kalori sehat, dan latihan low-impact.';
                        return 'Mulai perlahan, prioritaskan keamanan sendi, dan konsultasikan kondisi khusus.';
                    },
                    isActive(category) {
                        if (!this.bmi) return false;
                        const aboveMin = category.min === null || this.bmi >= category.min;
                        const belowMax = category.max === null || this.bmi < category.max;

                        return aboveMin && belowMax;
                    },
                }"
            >
                <div class="grid gap-6 lg:grid-cols-[minmax(16rem,0.9fr)_minmax(20rem,1.1fr)] lg:items-start">
                    <div class="min-w-0">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="weight" class="mb-2 block text-sm type-control text-zinc-700 dark:text-zinc-300">Berat badan (kg)</label>
                                <input id="weight" x-model="weight" type="number" min="20" max="300" step="0.1" inputmode="decimal" aria-describedby="weight-help" class="public-input text-lg type-compact sm:text-base" placeholder="Contoh: 60">
                                <p id="weight-help" class="mt-2 text-xs type-compact leading-5 text-zinc-500 dark:text-zinc-400">Rentang valid 20-300 kg.</p>
                            </div>
                            <div>
                                <label for="height" class="mb-2 block text-sm type-control text-zinc-700 dark:text-zinc-300">Tinggi badan (cm)</label>
                                <input id="height" x-model="height" type="number" min="80" max="250" step="0.1" inputmode="decimal" aria-describedby="height-help" class="public-input text-lg type-compact sm:text-base" placeholder="Contoh: 165">
                                <p id="height-help" class="mt-2 text-xs type-compact leading-5 text-zinc-500 dark:text-zinc-400">Rentang valid 80-250 cm.</p>
                            </div>
                        </div>

                    </div>

                    <div class="min-w-0 rounded-2xl border border-zinc-200 bg-gradient-to-br from-white via-zinc-50 to-gold-500/10 p-4 dark:border-white/10 dark:bg-none dark:bg-zinc-950/55 sm:p-5" role="status" aria-live="polite" aria-atomic="true" data-bmi-live-result data-bmi-gauge-summary>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs type-control uppercase tracking-[0.12em] text-zinc-600 dark:text-gold-400">Hasil IMT</p>
                                <p class="mt-2 break-words text-2xl type-emphasis text-zinc-950 dark:text-zinc-100" x-text="category">Masukkan data valid</p>
                                <p class="mt-1 text-sm type-control text-zinc-500 dark:text-zinc-400">Rentang: <span x-text="categoryRange">20-300 kg dan 80-250 cm</span></p>
                            </div>
                            <p class="shrink-0 text-5xl type-emphasis leading-none text-zinc-950 dark:text-gold-400 sm:text-6xl" x-text="rounded">-</p>
                        </div>
                        <p class="mt-4 text-sm leading-7 text-zinc-600 dark:text-zinc-300" x-text="recommendation">Isi berat dan tinggi dalam rentang valid untuk melihat hasil IMT.</p>
                    </div>
                </div>

                <div class="mt-8 min-w-0 rounded-[1.35rem] border border-gold-500/25 bg-[linear-gradient(180deg,#ffffff,#f8fafc)] p-4 shadow-[0_24px_70px_rgba(24,24,27,0.08)] dark:border-gold-500/20 dark:bg-none dark:bg-white/[0.055] dark:shadow-[0_24px_70px_rgba(254,172,24,0.10)] sm:p-6" data-bmi-visual-panel>
                    <div class="max-w-3xl">
                        <div class="min-w-0">
                            <p class="public-eyebrow">Visual IMT</p>
                            <h3 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Rentang BMI</h3>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                Bar warna memberi orientasi cepat; detail kategori tetap tertulis di bawahnya.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 w-full" data-bmi-gauge>
                        <div class="rounded-2xl border border-zinc-200 bg-white/75 p-3 dark:border-white/10 dark:bg-zinc-950/35 sm:p-4" data-bmi-segmented-range>
                            <div class="grid h-4 grid-cols-[1.1fr_1.15fr_1fr_1.25fr] overflow-hidden rounded-full shadow-inner" aria-hidden="true">
                                <div class="bg-sky-500"></div>
                                <div class="bg-emerald-500"></div>
                                <div class="bg-amber-500"></div>
                                <div class="bg-red-500"></div>
                            </div>
                            <div class="mt-3 grid gap-2 text-xs type-control text-zinc-600 dark:text-zinc-300 sm:grid-cols-4">
                                <div class="rounded-xl bg-sky-500/10 px-3 py-2 text-sky-800 ring-1 ring-sky-500/20 dark:text-sky-200">
                                    <span class="block text-[0.62rem] uppercase tracking-[0.12em]">Kurang</span>
                                    <span class="mt-1 block tabular-nums">&lt; 18.5</span>
                                </div>
                                <div class="rounded-xl bg-emerald-500/10 px-3 py-2 text-emerald-800 ring-1 ring-emerald-500/20 dark:text-emerald-200">
                                    <span class="block text-[0.62rem] uppercase tracking-[0.12em]">Normal</span>
                                    <span class="mt-1 block tabular-nums">18.5 - 24.9</span>
                                </div>
                                <div class="rounded-xl bg-amber-500/10 px-3 py-2 text-amber-800 ring-1 ring-amber-500/20 dark:text-amber-200">
                                    <span class="block text-[0.62rem] uppercase tracking-[0.12em]">Berlebih</span>
                                    <span class="mt-1 block tabular-nums">25.0 - 29.9</span>
                                </div>
                                <div class="rounded-xl bg-red-500/10 px-3 py-2 text-red-800 ring-1 ring-red-500/20 dark:text-red-200">
                                    <span class="block text-[0.62rem] uppercase tracking-[0.12em]">Obesitas</span>
                                    <span class="mt-1 block tabular-nums">&gt;= 30.0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-7 grid gap-2 sm:grid-cols-2" aria-label="Kategori BMI" data-bmi-category-list>
                        <template x-for="categoryItem in categories" x-bind:key="categoryItem.label">
                            <div
                                class="grid min-w-0 gap-2 rounded-xl px-3 py-3 text-sm leading-5 ring-1 transition sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center"
                                x-bind:class="isActive(categoryItem) ? categoryItem.tone + ' type-control shadow-sm' : 'bg-white/70 text-zinc-600 ring-zinc-200 dark:bg-zinc-950/35 dark:text-zinc-300 dark:ring-white/10'"
                                x-bind:aria-current="isActive(categoryItem) ? 'true' : null"
                            >
                                <span class="min-w-0">
                                    <span class="block" x-text="categoryItem.label"></span>
                                    <span
                                        x-show="isActive(categoryItem)"
                                        class="mt-1 inline-flex rounded-full bg-white/70 px-2 py-0.5 text-[0.62rem] type-control uppercase tracking-[0.12em] text-zinc-700 ring-1 ring-black/5 dark:bg-white/10 dark:text-zinc-100 dark:ring-white/10"
                                    >Kategori Anda</span>
                                </span>
                                <span class="shrink-0 type-control tabular-nums" x-text="categoryItem.range"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
