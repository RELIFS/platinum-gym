<x-public-layout :settings="$settings" title="Kalkulator BMI Platinum Gym Padang" description="Hitung Body Mass Index sebagai referensi umum sebelum mulai program latihan di Platinum Gym Padang.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'BMI Calculator',
        'title' => 'Cek BMI sebagai referensi awal latihan.',
        'description' => 'Masukkan berat dan tinggi badan. Data tidak dikirim ke server dan tidak disimpan.',
    ])

    <section class="public-section public-section-muted">
        <div class="public-container grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div class="public-card">
                <p class="public-eyebrow">Catatan</p>
                <h2 class="mt-3 text-2xl font-black text-zinc-950 dark:text-white">BMI bukan diagnosis medis.</h2>
                <p class="mt-4 text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    BMI membantu memberi gambaran umum komposisi tubuh berdasarkan berat dan tinggi. Untuk program spesifik, konsultasikan dengan coach atau tenaga kesehatan bila punya kondisi medis.
                </p>
                <a href="{{ route('public.services') }}" class="public-button-primary mt-7">Lihat Program Latihan</a>
            </div>

            <div class="public-card" x-data="{
                weight: '',
                height: '',
                get validWeight() { return Number(this.weight) >= 20 && Number(this.weight) <= 300 },
                get validHeight() { return Number(this.height) >= 80 && Number(this.height) <= 250 },
                get bmi() {
                    if (!this.validWeight || !this.validHeight) return null;
                    const meter = Number(this.height) / 100;
                    return Number(this.weight) / (meter * meter);
                },
                get rounded() { return this.bmi ? this.bmi.toFixed(1) : '-' },
                get category() {
                    if (!this.bmi) return 'Masukkan data valid';
                    if (this.bmi < 18.5) return 'Berat badan kurang';
                    if (this.bmi < 25) return 'Normal';
                    if (this.bmi < 30) return 'Berat badan berlebih';
                    return 'Obesitas';
                },
                get recommendation() {
                    if (!this.bmi) return 'Berat 20-300 kg dan tinggi 80-250 cm.';
                    if (this.bmi < 18.5) return 'Fokus pada latihan kekuatan, asupan kalori cukup, dan progres bertahap.';
                    if (this.bmi < 25) return 'Pertahankan rutinitas latihan dan kombinasikan strength dengan cardio.';
                    if (this.bmi < 30) return 'Mulai dengan program konsisten, defisit kalori sehat, dan latihan low-impact.';
                    return 'Mulai perlahan, prioritaskan keamanan sendi, dan konsultasikan kondisi khusus.';
                }
            }">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="weight" class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-300">Berat badan (kg)</label>
                        <input id="weight" x-model="weight" type="number" min="20" max="300" step="0.1" inputmode="decimal" aria-describedby="weight-help" class="public-input" placeholder="Contoh: 65">
                        <p id="weight-help" class="mt-2 text-xs font-medium text-zinc-500">Rentang valid 20-300 kg.</p>
                    </div>
                    <div>
                        <label for="height" class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-300">Tinggi badan (cm)</label>
                        <input id="height" x-model="height" type="number" min="80" max="250" step="0.1" inputmode="decimal" aria-describedby="height-help" class="public-input" placeholder="Contoh: 170">
                        <p id="height-help" class="mt-2 text-xs font-medium text-zinc-500">Rentang valid 80-250 cm.</p>
                    </div>
                </div>

                <div class="mt-8 rounded-2xl border border-gold-500/30 bg-gradient-to-br from-white via-zinc-50 to-gold-500/10 p-6 text-zinc-950 shadow-[0_24px_70px_rgba(24,24,27,0.08)] dark:border-gold-500/25 dark:bg-none dark:bg-white/[0.06] dark:text-white dark:shadow-[0_24px_70px_rgba(254,172,24,0.14)]" role="status" aria-live="polite" aria-atomic="true">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-400">Hasil BMI</p>
                    <p class="mt-3 text-6xl font-black text-gold-500" x-text="rounded">-</p>
                    <p class="mt-3 text-2xl font-black" x-text="category">Masukkan data valid</p>
                    <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-300" x-text="recommendation">Berat 20-300 kg dan tinggi 80-250 cm.</p>
                </div>

                <p class="mt-5 text-xs leading-6 text-zinc-500 dark:text-zinc-400">
                    Kalkulator ini berjalan di browser. Tidak ada data berat atau tinggi yang disimpan oleh sistem.
                </p>
            </div>
        </div>
    </section>
</x-public-layout>
