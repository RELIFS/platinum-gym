<?php

namespace App\Features\Gymmi\Support;

use Illuminate\Support\Str;

class GymmiTextNormalizer
{
    /**
     * @var array<string, string>|null
     */
    private ?array $phraseMap = null;

    /**
     * @var array<string, string>|null
     */
    private ?array $wordMap = null;

    /**
     * @var array<string, true>|null
     */
    private ?array $canonicalTerms = null;

    public function normalize(string $value): string
    {
        $normalized = $this->base($value);

        if ($normalized === '') {
            return '';
        }

        $normalized = $this->replacePhrases($normalized);

        return collect(explode(' ', $normalized))
            ->flatMap(fn (string $token): array => explode(' ', $this->normalizeToken($token)))
            ->filter()
            ->implode(' ');
    }

    private function base(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();
    }

    private function replacePhrases(string $value): string
    {
        foreach ($this->phraseMap() as $phrase => $replacement) {
            $pattern = '/(^|\s)'.preg_quote($phrase, '/').'($|\s)/u';
            $value = preg_replace($pattern, '$1'.$replacement.'$2', $value) ?: $value;
            $value = Str::of($value)->squish()->toString();
        }

        return $value;
    }

    private function normalizeToken(string $token): string
    {
        $wordMap = $this->wordMap();
        $canonicalTerms = $this->canonicalTerms();

        foreach ($this->tokenCandidates($token) as $candidate) {
            if (isset($wordMap[$candidate])) {
                return $wordMap[$candidate];
            }

            if (isset($canonicalTerms[$candidate])) {
                return $candidate;
            }
        }

        return $token;
    }

    /**
     * @return array<int, string>
     */
    private function tokenCandidates(string $token): array
    {
        return collect([
            $token,
            $this->collapseRepeatedLetters($token, 2),
            $this->collapseRepeatedLetters($token, 1),
        ])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function collapseRepeatedLetters(string $token, int $maxLength): string
    {
        return preg_replace_callback('/([a-z])\1+/u', function (array $matches) use ($maxLength): string {
            return str_repeat($matches[1], min(strlen($matches[0]), $maxLength));
        }, $token) ?: $token;
    }

    /**
     * @return array<string, string>
     */
    private function phraseMap(): array
    {
        if ($this->phraseMap !== null) {
            return $this->phraseMap;
        }

        return $this->phraseMap = collect(config('gymmi.normalizer.phrases', []))
            ->mapWithKeys(fn (string $value, string $key): array => [$this->base($key) => $this->base($value)])
            ->sortKeysUsing(fn (string $a, string $b): int => strlen($b) <=> strlen($a))
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function wordMap(): array
    {
        if ($this->wordMap !== null) {
            return $this->wordMap;
        }

        return $this->wordMap = collect(config('gymmi.normalizer.words', []))
            ->mapWithKeys(fn (string $value, string $key): array => [$this->base($key) => $this->base($value)])
            ->all();
    }

    /**
     * @return array<string, true>
     */
    private function canonicalTerms(): array
    {
        if ($this->canonicalTerms !== null) {
            return $this->canonicalTerms;
        }

        return $this->canonicalTerms = collect(config('gymmi.normalizer.canonical_terms', []))
            ->map(fn (string $value): string => $this->base($value))
            ->filter()
            ->mapWithKeys(fn (string $value): array => [$value => true])
            ->all();
    }
}
