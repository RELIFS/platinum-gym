<?php

namespace App\Features\Gymmi\Contracts;

use App\Features\Gymmi\Support\GymmiNormalizedInput;

interface GymmiInputNormalizerClient
{
    public function normalize(string $message, string $context): ?GymmiNormalizedInput;
}
