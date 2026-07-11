<?php

namespace App\Features\Gymmi\Support;

class GymmiQueryPlanner
{
    public function __construct(
        private readonly GymmiIntentDetector $intentDetector,
        private readonly GymmiKnowledgeMatcher $matcher,
    ) {}

    /**
     * @param  array<string, mixed>  $knowledge
     * @param  array<int, string>  $normalizerIntents
     * @param  array<string, mixed>  $entities
     */
    public function plan(string $message, array $knowledge, array $normalizerIntents = [], array $entities = [], bool $followUp = false): GymmiTurnPlan
    {
        $detected = $this->intentDetector->detect($message);
        $match = $this->matcher->match($message, $knowledge);
        $intents = collect([$detected['intent'], ...$normalizerIntents])
            ->filter(fn (mixed $intent): bool => is_string($intent) && $intent !== '' && $intent !== 'general')
            ->unique()
            ->take(2)
            ->values()
            ->all();

        if ($intents === []) {
            $intents = ['general'];
        }

        return new GymmiTurnPlan(
            message: $message,
            intents: $intents,
            subject: $detected['subject'],
            entities: $entities,
            followUp: $followUp,
            ambiguous: ($match['type'] ?? null) === 'ambiguous',
        );
    }
}
