<?php

namespace App\Features\Gymmi\Support;

class GymmiGroundedResponder
{
    public function __construct(
        private readonly GymmiFallbackResponder $fallback,
        private readonly GymmiResponseFormatter $formatter,
    ) {}

    /**
     * @param  array{type: string, answer: string|null, snippets: array<int, string>, topic: string|null, confidence: int}  $match
     */
    public function answer(array $match, GymmiTurnPlan $plan, string $surface): string
    {
        if (($match['type'] ?? 'none') === 'ambiguous') {
            return $this->formatter->reply($this->fallback->ambiguous($surface));
        }

        if (($match['type'] ?? 'none') === 'none') {
            return $this->formatter->reply($this->fallback->outOfScope($surface));
        }

        if ($match['type'] === 'faq' && filled($match['answer'])) {
            return $this->formatter->reply((string) $match['answer']);
        }

        $snippets = $match['snippets'] ?? [];

        if ($snippets !== []) {
            return $this->formatter->reply($this->fallback->fromMatch($match));
        }

        return $this->formatter->reply($this->fallback->outOfScope($surface));
    }
}
