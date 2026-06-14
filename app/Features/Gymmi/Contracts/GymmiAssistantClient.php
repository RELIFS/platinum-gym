<?php

namespace App\Features\Gymmi\Contracts;

interface GymmiAssistantClient
{
    /**
     * @param  array<int, array{from?: string, text?: string}>  $history
     */
    public function ask(string $message, string $context, array $history = []): ?string;
}
