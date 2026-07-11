<?php

namespace App\Features\Gymmi\Support;

use Illuminate\Support\Facades\Cache;

class GymmiKnowledgeRepository
{
    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $path = $this->path();
        $overridesPath = $this->overridesPath();

        if (! is_file($path)) {
            return $this->emptyKnowledge();
        }

        $cacheKey = 'gymmi:knowledge:'.sha1($path.'|'.filemtime($path).'|'.$overridesPath.'|'.(is_file($overridesPath) ? filemtime($overridesPath) : 'none'));

        return Cache::rememberForever($cacheKey, function () use ($path, $overridesPath): array {
            $payload = json_decode((string) file_get_contents($path), true);

            if (! is_array($payload)) {
                return $this->emptyKnowledge();
            }

            return $this->mergeOverrides($payload, $overridesPath);
        });
    }

    public function configValue(string $key): ?string
    {
        $config = $this->all()['config'] ?? [];

        if (! is_array($config)) {
            return null;
        }

        $value = $config[$key]['value'] ?? $config[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function path(): string
    {
        return (string) config('gymmi.knowledge_base_path', resource_path('data/gymmi/knowledge-base.json'));
    }

    public function overridesPath(): string
    {
        return (string) config('gymmi.knowledge_overrides_path', resource_path('data/gymmi/knowledge-overrides.json'));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function mergeOverrides(array $payload, string $overridesPath): array
    {
        if (! is_file($overridesPath)) {
            return $payload;
        }

        $overrides = json_decode((string) file_get_contents($overridesPath), true);

        if (! is_array($overrides)) {
            return $payload;
        }

        foreach (['faq', 'aliases'] as $key) {
            $identity = fn (array $row): string => strtolower(trim((string) ($row['question'] ?? $row['phrase'] ?? json_encode($row))));
            $base = collect($payload[$key] ?? [])
                ->filter(fn (mixed $row): bool => is_array($row))
                ->keyBy($identity);
            $overrideRows = collect($overrides[$key] ?? [])
                ->filter(fn (mixed $row): bool => is_array($row))
                ->keyBy($identity);

            $payload[$key] = $base
                ->merge($overrideRows)
                ->values()
                ->all();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyKnowledge(): array
    {
        return [
            'metadata' => ['available' => false],
            'config' => [],
            'faq' => [],
            'aliases' => [],
            'catalog' => [],
            'validation' => [],
        ];
    }
}
