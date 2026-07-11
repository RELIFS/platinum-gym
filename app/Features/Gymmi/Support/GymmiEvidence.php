<?php

namespace App\Features\Gymmi\Support;

class GymmiEvidence
{
    /**
     * @param  array<string, mixed>  $fields
     * @param  array<int, string>  $protectedLiterals
     */
    public function __construct(
        public readonly string $id,
        public readonly string $domain,
        public readonly string $scope,
        public readonly string $source,
        public readonly int $priority,
        public readonly array $fields,
        public readonly array $protectedLiterals = [],
    ) {}

    public function text(): string
    {
        return collect($this->fields)
            ->filter(fn (mixed $value): bool => is_scalar($value) && filled((string) $value))
            ->map(fn (mixed $value, string $key): string => $key.': '.(string) $value)
            ->implode('; ');
    }
}
