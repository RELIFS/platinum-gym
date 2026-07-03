<?php

namespace App\Features\Gymmi\Support;

class GymmiNormalizedInput
{
    /**
     * @param  array<int, string>  $intents
     * @param  array<string, mixed>  $entities
     * @param  array<int, string>  $unsafeFlags
     */
    public function __construct(
        public readonly string $message,
        public readonly array $intents = [],
        public readonly array $entities = [],
        public readonly int $confidence = 0,
        public readonly array $unsafeFlags = [],
        public readonly string $source = 'local',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        return [
            'normalizer_source' => $this->source,
            'normalizer_confidence' => $this->confidence,
            'normalizer_intents' => $this->intents,
            'normalizer_unsafe_flags' => $this->unsafeFlags,
        ];
    }
}
