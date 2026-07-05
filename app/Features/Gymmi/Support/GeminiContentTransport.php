<?php

namespace App\Features\Gymmi\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GeminiContentTransport
{
    public function __construct(
        private readonly GeminiApiKeyPool $keyPool,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public function generate(array $body, string $purpose): ?string
    {
        if (! (bool) config('services.gemini.enabled', true)) {
            return null;
        }

        $keys = $this->keyPool->availableKeys();

        if ($keys === []) {
            return null;
        }

        $model = $this->model();

        if ($this->keyPool->modelCircuitOpen($model)) {
            return null;
        }

        foreach (array_slice($this->keyPool->prioritized($keys), 0, $this->keyPool->maxAttempts()) as $attempt => $key) {
            try {
                $response = Http::baseUrl((string) config('services.gemini.base_url'))
                    ->timeout((int) config('services.gemini.timeout', 12))
                    ->connectTimeout((int) config('services.gemini.connect_timeout', 5))
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'x-goog-api-key' => $key,
                    ])
                    ->post("/v1beta/models/{$model}:generateContent", $body);

                if ($response->successful()) {
                    return $this->extractText($response->json());
                }

                $status = $response->status();

                Log::warning('Gemini Gymmi request failed.', [
                    'status' => $status,
                    'model' => $model,
                    'purpose' => $purpose,
                    'attempt' => $attempt + 1,
                ]);

                if ($status === 429) {
                    $this->keyPool->coolDownKey($key);
                    $this->keyPool->openModelCircuit($model);

                    break;
                }

                if ($status === 404) {
                    $this->keyPool->openModelCircuit($model);

                    break;
                }

                if (in_array($status, [401, 403], true)) {
                    $this->keyPool->markInvalid($key);

                    continue;
                }

                if (! in_array($status, [500, 502, 503, 504], true)) {
                    break;
                }
            } catch (Throwable $exception) {
                Log::warning('Gemini Gymmi request exception.', [
                    'model' => $model,
                    'purpose' => $purpose,
                    'attempt' => $attempt + 1,
                    'exception' => $exception::class,
                ]);
            }
        }

        return null;
    }

    public function model(): string
    {
        return Str::of((string) config('services.gemini.model', 'gemini-2.0-flash'))
            ->after('models/')
            ->trim()
            ->toString();
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function extractText(?array $payload): ?string
    {
        $parts = Arr::get($payload ?? [], 'candidates.0.content.parts', []);

        if (! is_array($parts)) {
            return null;
        }

        $text = collect($parts)
            ->pluck('text')
            ->filter()
            ->implode("\n");

        $text = trim(strip_tags($text));

        return $text !== '' ? Str::limit($text, 1600, '') : null;
    }
}
