<?php

namespace App\Features\Gymmi\Support;

use Illuminate\Support\Str;

class GymmiKnowledgeMatcher
{
    /**
     * @return array{type: string, answer: string|null, snippets: array<int, string>, topic: string|null, confidence: int}
     */
    public function match(string $message, array $knowledge): array
    {
        $normalized = $this->normalize($message);
        $tokens = $this->tokens($normalized);

        if ($this->isAmbiguous($tokens, $normalized)) {
            return $this->result('ambiguous', null, [], null, 0);
        }

        $aliases = $this->matchedAliases($normalized, $knowledge['aliases'] ?? []);
        $faq = $this->bestFaq($normalized, $tokens, $aliases, $knowledge['faq'] ?? []);

        if (($faq['score'] ?? 0) >= 70 && ! $this->shouldUseKnowledgeContext($normalized)) {
            return $this->result('faq', (string) $faq['answer'], [], (string) $faq['category'], (int) $faq['score']);
        }

        $snippets = array_values(array_filter(array_merge(
            $this->configSnippets($normalized, $knowledge['config'] ?? []),
            $this->catalogSnippets($normalized, $tokens, $aliases, $knowledge['catalog'] ?? []),
            isset($faq['answer']) && ($faq['score'] ?? 0) >= 42 ? [(string) $faq['answer']] : [],
        )));

        if ($snippets !== []) {
            return $this->result('knowledge', null, $snippets, $aliases[0]['category'] ?? ($faq['category'] ?? null), 50);
        }

        return $this->result('none', null, [], null, 0);
    }

    /**
     * @param  array<int, string>  $snippets
     * @return array{type: string, answer: string|null, snippets: array<int, string>, topic: string|null, confidence: int}
     */
    private function result(string $type, ?string $answer, array $snippets, ?string $topic, int $confidence): array
    {
        return compact('type', 'answer', 'snippets', 'topic', 'confidence');
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private function isAmbiguous(array $tokens, string $normalized): bool
    {
        if (count($tokens) > 3) {
            return false;
        }

        foreach (['harga', 'jadwal', 'paket', 'kelas', 'coach', 'trainer'] as $word) {
            if ($normalized === $word || $normalized === 'info '.$word || $normalized === $word.'nya') {
                return true;
            }
        }

        return false;
    }

    private function shouldUseKnowledgeContext(string $message): bool
    {
        if ($this->messageHasAny($message, ['rangkum', 'versi singkat', 'buatkan', 'susun', 'jelaskan singkat'])) {
            return true;
        }

        if ($this->messageHasAny($message, ['maps', 'google maps', 'whatsapp', 'instagram'])) {
            return true;
        }

        return preg_match('/^info\s+(membership|member|paket|gym)\b/u', $message) === 1;
    }

    /**
     * @param  array<int, array<string, mixed>>  $aliases
     * @return array<int, array{category: string, intent: string, phrase: string}>
     */
    private function matchedAliases(string $message, array $aliases): array
    {
        return collect($aliases)
            ->map(function (array $alias): ?array {
                $phrase = $this->normalize((string) ($alias['phrase'] ?? ''));

                if ($phrase === '') {
                    return null;
                }

                return [
                    'category' => (string) ($alias['category'] ?? ''),
                    'intent' => $this->normalize((string) ($alias['intent'] ?? '')),
                    'phrase' => $phrase,
                ];
            })
            ->filter()
            ->filter(fn (array $alias): bool => $this->containsPhrase($message, $alias['phrase']))
            ->sortByDesc(fn (array $alias): int => mb_strlen($alias['phrase']))
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $tokens
     * @param  array<int, array{category: string, intent: string, phrase: string}>  $aliases
     * @param  array<int, array<string, mixed>>  $faqs
     * @return array<string, mixed>
     */
    private function bestFaq(string $message, array $tokens, array $aliases, array $faqs): array
    {
        $best = ['score' => 0];
        $aliasIntents = collect($aliases)->pluck('intent')->filter()->values();
        $aliasCategories = collect($aliases)->pluck('category')->map(fn (string $category): string => $this->normalize($category))->filter()->values();

        foreach ($faqs as $faq) {
            $question = $this->normalize((string) ($faq['question'] ?? ''));
            $answer = (string) ($faq['answer'] ?? '');
            $category = (string) ($faq['category'] ?? '');

            if ($question === '' || $answer === '') {
                continue;
            }

            $score = 0;

            if ($message === $question) {
                $score += 100;
            } elseif ($this->containsPhrase($message, $question) || $this->containsPhrase($question, $message)) {
                $score += mb_strlen($message) > 5 ? 82 : 30;
            }

            $questionTokens = $this->tokens($question);
            $score += count(array_intersect($tokens, $questionTokens)) * 14;

            foreach ($aliasIntents as $intent) {
                if ($intent !== '' && ($this->containsPhrase($question, $intent) || str_contains($this->normalize($answer), $intent))) {
                    $score += 34;
                }
            }

            if ($aliasCategories->contains($this->normalize($category))) {
                $score += 20;
            }

            if ($score > ($best['score'] ?? 0)) {
                $best = [
                    'score' => $score,
                    'answer' => $answer,
                    'category' => $category,
                    'question' => (string) ($faq['question'] ?? ''),
                ];
            }
        }

        return $best;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, string>
     */
    private function configSnippets(string $message, array $config): array
    {
        $map = [
            'jam_operasional_senin_sabtu' => ['jam', 'buka', 'operasional', 'senin', 'sabtu'],
            'jam_operasional_minggu' => ['jam', 'minggu', 'tutup'],
            'alamat' => ['alamat', 'lokasi', 'dimana'],
            'google_maps' => ['maps', 'google maps', 'lokasi'],
            'whatsapp' => ['wa', 'whatsapp', 'kontak', 'admin'],
            'instagram' => ['instagram', 'ig'],
            'metode_pembayaran' => ['bayar', 'pembayaran', 'transfer', 'qris', 'cash'],
            'kebijakan_libur' => ['libur', 'hari besar', 'nasional'],
        ];

        $snippets = [];

        foreach ($map as $key => $needles) {
            if (! $this->messageHasAny($message, $needles)) {
                continue;
            }

            $value = $config[$key]['value'] ?? null;

            if (is_string($value) && $value !== '') {
                $snippets[] = ($config[$key]['label'] ?? Str::headline(str_replace('_', ' ', $key))).': '.$value;
            }
        }

        return $snippets;
    }

    /**
     * @param  array<int, string>  $tokens
     * @param  array<int, array{category: string, intent: string, phrase: string}>  $aliases
     * @param  array<string, array<int, array<string, mixed>>>  $catalog
     * @return array<int, string>
     */
    private function catalogSnippets(string $message, array $tokens, array $aliases, array $catalog): array
    {
        $categories = [
            'membership' => ['membership', 'member', 'paket', 'gym', 'mahasiswa', 'harga'],
            'fasilitas' => ['fasilitas', 'wc', 'locker', 'parkir'],
            'alat_gym' => ['alat', 'chest', 'leg', 'treadmill', 'dumbbell', 'otot'],
            'coach' => ['coach', 'pelatih', 'trainer'],
            'personal_trainer' => ['personal trainer', 'pt', 'sesi trainer'],
            'class_senam' => ['senam', 'aerobic', 'zumba', 'poundfit'],
            'class_terpisah' => ['muaythai', 'muay thai', 'kelas terpisah'],
            'makanan' => ['makanan', 'snack', 'roti'],
            'minuman' => ['minuman', 'air', 'susu', 'protein'],
            'produk_lainnya' => ['produk', 'hand wrap', 'aksesoris', 'sarung tinju'],
            'kebijakan' => ['kebijakan', 'aturan', 'refund', 'booking', 'cancel'],
        ];

        $aliasText = collect($aliases)->flatMap(fn (array $alias): array => [$alias['category'], $alias['intent'], $alias['phrase']])->implode(' ');
        $snippets = [];

        foreach ($categories as $category => $needles) {
            if (! $this->messageHasAny($message.' '.$aliasText, $needles)) {
                continue;
            }

            foreach (($catalog[$category] ?? []) as $row) {
                $rowText = $this->rowText($category, $row);
                $rowNormalized = $this->normalize($rowText);
                $score = count(array_intersect($tokens, $this->tokens($rowNormalized)));

                if ($score > 0 || $this->messageHasAny($rowNormalized, $tokens) || count($tokens) <= 2) {
                    $snippets[] = $rowText;
                }

                if (count($snippets) >= 8) {
                    return $snippets;
                }
            }
        }

        return $snippets;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowText(string $category, array $row): string
    {
        $label = match ($category) {
            'membership' => $row['nama_paket'] ?? null,
            'fasilitas' => $row['nama_fasilitas'] ?? null,
            'alat_gym' => $row['nama_alat'] ?? null,
            'coach' => $row['nama_coach'] ?? null,
            'personal_trainer' => $row['nama_paket'] ?? null,
            'class_senam', 'class_terpisah' => $row['nama'] ?? null,
            default => $row['nama_produk'] ?? $row['judul'] ?? null,
        };

        $details = collect($row)
            ->except(['id', 'status', 'normalized'])
            ->reject(fn (mixed $value): bool => blank($value) || $value === $label)
            ->take(5)
            ->map(fn (mixed $value, string $key): string => Str::headline(str_replace('_', ' ', $key)).' '.$value)
            ->implode(', ');

        return trim((string) $label.($details !== '' ? ': '.$details : ''));
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();
    }

    /**
     * @return array<int, string>
     */
    private function tokens(string $value): array
    {
        $stopwords = ['apa', 'ada', 'yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'info', 'berapa', 'gimana', 'bagaimana', 'saya', 'mau', 'ingin'];

        return collect(explode(' ', $this->normalize($value)))
            ->filter(fn (string $token): bool => mb_strlen($token) >= 3 && ! in_array($token, $stopwords, true))
            ->unique()
            ->values()
            ->all();
    }

    private function containsPhrase(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }

        return (bool) preg_match('/(^|\s)'.preg_quote($needle, '/').'($|\s)/u', $haystack);
    }

    /**
     * @param  array<int, string>  $needles
     */
    private function messageHasAny(string $message, array $needles): bool
    {
        foreach ($needles as $needle) {
            $needle = $this->normalize((string) $needle);

            if ($needle !== '' && str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }
}
