<?php

namespace App\Features\Gymmi\Support;

use Illuminate\Support\Str;

class GymmiResponseFormatter
{
    public function reply(string $text): string
    {
        $text = trim(strip_tags($text));
        $text = preg_replace('/[ \t]+/', ' ', $text) ?: $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?: $text;

        return Str::limit(trim($text), 1600, '');
    }

    public function userMessage(string $message): string
    {
        return Str::limit(trim(strip_tags($message)), 700, '');
    }

    public function logMessage(string $message): string
    {
        $message = $this->userMessage($message);
        $message = preg_replace('/((?:api[_\s-]?key|token|secret|password|kata\s*sandi)\s*[:=]\s*)\S+/i', '$1[redacted]', $message) ?: $message;

        return Str::limit($message, 300, '');
    }

    /**
     * @param  array<int, string>  $snippets
     */
    public function snippetReply(array $snippets, string $fallback): string
    {
        $snippets = collect($snippets)
            ->map(fn (string $snippet): string => $this->cleanSnippet($snippet))
            ->filter()
            ->take(6)
            ->values();

        if ($snippets->isEmpty()) {
            return $this->reply($fallback);
        }

        $privateReply = $this->privateClassReply($snippets->all());

        if ($privateReply !== null) {
            return $this->reply($privateReply);
        }

        if ($snippets->count() === 1) {
            return $this->reply($snippets->first());
        }

        return $this->reply($snippets
            ->take(5)
            ->map(fn (string $snippet): string => '- '.$snippet)
            ->implode("\n"));
    }

    private function cleanSnippet(string $snippet): string
    {
        $snippet = trim($snippet);
        $snippet = str_replace(['session_based', 'included'], ['kelas terpisah atau berbayar', 'akses mengikuti paket yang sesuai'], $snippet);
        $snippet = preg_replace('/\b(Gemini|fallback|provider|rate limit|snippet|prompt|data lokal)\b/i', '', $snippet) ?: $snippet;
        $snippet = preg_replace('/[ \t]+/', ' ', $snippet) ?: $snippet;
        $snippet = preg_replace("/\n[ \t]+/", "\n", $snippet) ?: $snippet;
        $snippet = preg_replace("/\n{3,}/", "\n\n", $snippet) ?: $snippet;

        return trim($snippet);
    }

    /**
     * @param  array<int, string>  $snippets
     */
    private function privateClassReply(array $snippets): ?string
    {
        $uncertainty = collect($snippets)
            ->first(fn (string $snippet): bool => str_contains($snippet, 'Belum bisa dipastikan') && str_contains($snippet, 'sesi privat'));

        if (! is_string($uncertainty)) {
            return null;
        }

        $classSnippet = collect($snippets)
            ->first(fn (string $snippet): bool => str_contains($snippet, 'tercatat sebagai kelas berjadwal') || str_contains($snippet, 'tersedia'));

        $context = $this->classContextSentence(is_string($classSnippet) ? $classSnippet : null);

        return collect([
            'Belum bisa dipastikan dari data resmi apakah Muaythai tersedia sebagai sesi privat.',
            $context,
            'Jika ingin latihan hanya dengan coach, silakan konfirmasi ketersediaan ke admin Platinum Gym.',
        ])->filter()->implode(' ');
    }

    private function classContextSentence(?string $snippet): ?string
    {
        if (! is_string($snippet) || $snippet === '') {
            return 'Data saat ini mencatat Muaythai sebagai kelas berjadwal dengan kapasitas terbatas.';
        }

        $className = str_contains($snippet, 'Muaythai') ? 'Muaythai' : 'Kelas tersebut';
        $coach = $this->match('/bersama\s+([^,.]+)/i', $snippet);
        $capacity = $this->match('/kapasitas\s+(\d+\s+peserta)/i', $snippet);

        return collect([
            'Data saat ini mencatat '.$className.' sebagai kelas berjadwal',
            $coach ? 'bersama '.$coach : null,
            $capacity ? 'dengan kapasitas '.$capacity : 'dengan kapasitas terbatas',
        ])->filter()->implode(' ').'.';
    }

    private function match(string $pattern, string $value): ?string
    {
        if (preg_match($pattern, $value, $matches) !== 1) {
            return null;
        }

        return trim((string) ($matches[1] ?? '')) ?: null;
    }
}
