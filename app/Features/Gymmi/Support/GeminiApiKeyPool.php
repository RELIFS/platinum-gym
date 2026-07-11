<?php

namespace App\Features\Gymmi\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class GeminiApiKeyPool
{
    /**
     * @return array{configured: int, available: int, invalid: int, cooldown: int, max_attempts: int, model: string, model_circuit_open: bool}
     */
    public function status(?string $model = null): array
    {
        $keys = $this->keys();
        $model = $model ?: (string) config('services.gemini.model', 'gemini-2.0-flash');

        return [
            'configured' => count($keys),
            'available' => count($this->availableKeys()),
            'invalid' => collect($keys)->filter(fn (string $key): bool => Cache::has($this->invalidKey($key)))->count(),
            'cooldown' => collect($keys)->filter(fn (string $key): bool => Cache::has($this->keyCooldownCacheKey($key)))->count(),
            'max_attempts' => $this->maxAttempts(),
            'model' => $model,
            'model_circuit_open' => $this->modelCircuitOpen($model),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function availableKeys(): array
    {
        return collect($this->keys())
            ->reject(fn (string $key): bool => Cache::has($this->invalidKey($key)) || Cache::has($this->keyCooldownCacheKey($key)))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<int, string>
     */
    public function prioritized(array $keys): array
    {
        if (count($keys) <= 1) {
            return $keys;
        }

        $firstIndex = random_int(0, count($keys) - 1);

        return array_values(array_merge(
            array_slice($keys, $firstIndex),
            array_slice($keys, 0, $firstIndex),
        ));
    }

    public function markInvalid(string $key): void
    {
        Cache::put($this->invalidKey($key), true, now()->addMinutes(30));
    }

    public function coolDownKey(string $key): void
    {
        Cache::put($this->keyCooldownCacheKey($key), true, now()->addSeconds($this->keyCooldownSeconds()));
    }

    public function openModelCircuit(string $model): void
    {
        Cache::put($this->modelCircuitKey($model), true, now()->addSeconds($this->circuitBreakerSeconds()));
    }

    public function modelCircuitOpen(string $model): bool
    {
        return Cache::has($this->modelCircuitKey($model));
    }

    public function maxAttempts(): int
    {
        return max(1, min(2, (int) config('services.gemini.max_retries', 1)));
    }

    /**
     * @return array<int, string>
     */
    private function keys(): array
    {
        $configuredKeys = config('services.gemini.api_keys', []);

        $rawKeys = array_filter(array_merge(
            is_array($configuredKeys) ? $configuredKeys : Arr::wrap($configuredKeys),
            Arr::wrap(config('services.gemini.api_key')),
        ));

        return collect($rawKeys)
            ->flatMap(fn (mixed $value) => is_array($value) ? $value : (preg_split('/[\r\n,]+/', (string) $value) ?: []))
            ->map(fn (string $value) => trim($value, " \t\n\r\0\x0B\"'"))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function modelCircuitKey(string $model): string
    {
        return 'gymmi:gemini:model-circuit:'.sha1($model);
    }

    private function invalidKey(string $key): string
    {
        return 'gymmi:gemini:key-invalid:'.sha1($key);
    }

    private function keyCooldownCacheKey(string $key): string
    {
        return 'gymmi:gemini:key-cooldown:'.sha1($key);
    }

    private function circuitBreakerSeconds(): int
    {
        return max(30, (int) config('services.gemini.circuit_breaker_seconds', 300));
    }

    private function keyCooldownSeconds(): int
    {
        return max(30, (int) config('services.gemini.key_cooldown_seconds', 120));
    }
}
