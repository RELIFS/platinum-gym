<?php

namespace App\Features\Gymmi\Support;

class GymmiTurnPlan
{
    /**
     * @param  array<int, string>  $intents
     * @param  array<string, mixed>  $entities
     */
    public function __construct(
        public readonly string $message,
        public readonly array $intents,
        public readonly ?string $subject,
        public readonly array $entities = [],
        public readonly bool $followUp = false,
        public readonly bool $ambiguous = false,
    ) {}

    public function primaryIntent(): string
    {
        return $this->intents[0] ?? 'general';
    }

    /**
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        return [
            'intent' => $this->primaryIntent(),
            'intents' => $this->intents,
            'subject' => $this->subject,
            'follow_up' => $this->followUp,
        ];
    }
}
