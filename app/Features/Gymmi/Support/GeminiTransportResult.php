<?php

namespace App\Features\Gymmi\Support;

class GeminiTransportResult
{
    public function __construct(
        public readonly ?string $text,
        public readonly string $outcome,
        public readonly int $attempts,
        public readonly int $latencyMs,
        public readonly ?int $promptTokens = null,
        public readonly ?int $completionTokens = null,
    ) {}
}
