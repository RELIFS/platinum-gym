<?php

namespace App\Features\Gymmi\Support;

use App\Models\User;

class GymmiPromptBuilder
{
    public function __construct(
        private readonly GymmiContextBuilder $contextBuilder,
    ) {}

    /**
     * @param  array{type: string, answer: string|null, snippets: array<int, string>, topic: string|null, confidence: int, intent?: array{intent: string, subject: string|null, normalized: string}}  $match
     */
    public function build(array $match, string $context, ?User $user): string
    {
        $intent = $match['intent'] ?? null;
        $intentName = is_array($intent) ? (string) ($intent['intent'] ?? 'general') : 'general';
        $subject = is_array($intent) ? (string) ($intent['subject'] ?? '') : '';

        $sections = [
            'Identitas: Gymmi adalah asisten resmi Platinum Gym Padang.',
            'Aturan jawaban: jawab seperti customer service Platinum Gym yang ramah, profesional, ringkas, dan natural. Jawab langsung sesuai pertanyaan user dalam 2-4 kalimat untuk jawaban biasa, bukan merangkum semua data. Gunakan hanya data pada konteks ini. Jika data tidak ada, katakan belum bisa dipastikan dari data resmi dan arahkan ke admin. Untuk pertanyaan ya/tidak, awali dengan "Ya", "Tidak", atau "Belum bisa dipastikan dari data resmi." Jangan mengarang harga, jadwal, stok, kontak, email, promo, status privat, token, atau data internal. Jangan menyebut istilah internal seperti Gemini, fallback, provider, rate limit, snippet, prompt, data lokal, atau mekanisme sistem. Jika data live berbeda dari dataset, pakai data live pada daftar snippet.',
            'Konteks percakapan: '.$context,
            'Topik: '.($match['topic'] ?: 'Platinum Gym'),
            'Intent: '.$intentName.($subject !== '' ? ' / '.$subject : ''),
        ];

        if (($match['snippets'] ?? []) !== []) {
            $sections[] = "Data boleh dipakai:\n".collect($match['snippets'])
                ->take(8)
                ->map(fn (string $snippet, int $index): string => ($index + 1).'. '.$snippet)
                ->implode("\n");

            $sections[] = 'Batasan: pilih data yang paling relevan dengan intent. Jika user menyebut Muaythai, jangan jawab dengan Aerobic, Poundfit, atau kelas lain kecuali diminta membandingkan. Jangan menyalin daftar data mentah; ubah menjadi jawaban yang enak dibaca di chat mobile.';
        }

        if ($context === 'member' && $user) {
            $memberContext = $this->contextBuilder->buildMemberOnly($user);

            if ($memberContext !== '') {
                $sections[] = "Data member login sendiri:\n".$memberContext;
            }
        }

        return implode("\n\n", array_filter($sections));
    }
}
