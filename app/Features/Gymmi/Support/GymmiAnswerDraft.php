<?php

namespace App\Features\Gymmi\Support;

class GymmiAnswerDraft
{
    /**
     * @param  array<int, string>  $usedFactIds
     */
    public function __construct(
        public readonly string $answer,
        public readonly array $usedFactIds,
    ) {}

    public static function fromJson(string $text): ?self
    {
        $text = trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($text)) ?: $text);
        $payload = json_decode($text, true);

        if (! is_array($payload) || ! is_string($payload['answer'] ?? null) || ! is_array($payload['used_fact_ids'] ?? null)) {
            return null;
        }

        $answer = trim(strip_tags($payload['answer']));
        $ids = collect($payload['used_fact_ids'])
            ->filter(fn (mixed $id): bool => is_string($id) && filled($id))
            ->unique()
            ->values()
            ->all();

        return $answer !== '' ? new self($answer, $ids) : null;
    }
}
