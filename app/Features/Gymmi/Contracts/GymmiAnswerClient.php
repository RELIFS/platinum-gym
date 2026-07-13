<?php

namespace App\Features\Gymmi\Contracts;

interface GymmiAnswerClient
{
    /**
     * @param  array<int, array{from?: string, text?: string}>  $history
     */
    public function answer(string $message, string $context, array $history = []): ?string;
}
