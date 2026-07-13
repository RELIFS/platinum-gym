<?php

namespace App\Features\Gymmi\Support;

class GymmiEvidenceSet
{
    /**
     * @param  array<int, GymmiEvidence>  $items
     */
    public function __construct(
        public readonly array $items = [],
    ) {}

    /**
     * @return array<int, string>
     */
    public function ids(): array
    {
        return collect($this->items)->map(fn (GymmiEvidence $item): string => $item->id)->values()->all();
    }

    /**
     * @return array<int, string>
     */
    public function protectedLiterals(): array
    {
        return collect($this->items)->flatMap(fn (GymmiEvidence $item): array => $item->protectedLiterals)->filter()->unique()->values()->all();
    }

    public function promptContext(): string
    {
        return collect($this->items)
            ->sortByDesc(fn (GymmiEvidence $item): int => $item->priority)
            ->map(fn (GymmiEvidence $item): string => '['.$item->id.'] '.$item->text())
            ->implode("\n");
    }
}
