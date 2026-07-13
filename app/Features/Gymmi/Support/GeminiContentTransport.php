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
     * Compatibility wrapper for existing clients.
     *
     * @param  array<string, mixed>  $body
     */
    public function generate(array $body, string $purpose): ?string
    {
        return $this->generateResult($body, $purpose)->text;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public function generateResult(array $body, string $purpose, ?int $deadlineNs = null, int $maxAttempts = 1): GeminiTransportResult
    {
        $startedAt = hrtime(true);
        $deadlineNs ??= $startedAt + ((int) config('gymmi.deadline_ms', 7500) * 1_000_000);

        if (! (bool) config('services.gemini.enabled', true)) {
            return $this->result(null, 'disabled', 0, $startedAt);
        }

        $keys = $this->keyPool->availableKeys();
        $model = $this->model();

        if ($keys === [] || $this->keyPool->modelCircuitOpen($model)) {
            return $this->result(null, $keys === [] ? 'no_key' : 'circuit_open', 0, $startedAt);
        }

        $attemptLimit = max(1, min($maxAttempts, 2, $this->keyPool->maxAttempts()));
        $attempts = 0;
        $lastOutcome = 'unavailable';

        foreach (array_slice($this->keyPool->prioritized($keys), 0, $attemptLimit) as $key) {
            $remainingMs = (int) floor(($deadlineNs - hrtime(true)) / 1_000_000);

            if ($remainingMs < 500) {
                $lastOutcome = 'deadline_exhausted';
                break;
            }

            $attempts++;

            try {
                $requestTimeout = max(1, min((int) config('services.gemini.timeout', 7), (int) ceil($remainingMs / 1000)));
                $connectTimeout = max(1, min((int) config('services.gemini.connect_timeout', 3), $requestTimeout));
                $response = Http::baseUrl((string) config('services.gemini.base_url'))
                    ->timeout($requestTimeout)
                    ->connectTimeout($connectTimeout)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'x-goog-api-key' => $key,
                    ])
                    ->post("/v1beta/models/{$model}:generateContent", $body);

                if ($response->successful()) {
                    $text = $this->extractText($response->json());
                    $usage = $response->json('usageMetadata', []);

                    return new GeminiTransportResult(
                        text: $text,
                        outcome: $text ? 'success' : 'empty',
                        attempts: $attempts,
                        latencyMs: $this->elapsedMs($startedAt),
                        promptTokens: is_array($usage) ? ($usage['promptTokenCount'] ?? null) : null,
                        completionTokens: is_array($usage) ? ($usage['candidatesTokenCount'] ?? null) : null,
                    );
                }

                $status = $response->status();
                $lastOutcome = 'http_'.$status;
                Log::warning('Gemini Gymmi request failed.', compact('status', 'model', 'purpose', 'attempts'));

                if ($status === 429) {
                    $this->keyPool->coolDownKey($key);
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

                break;
            } catch (Throwable $exception) {
                $lastOutcome = 'exception';
                Log::warning('Gemini Gymmi request exception.', [
                    'model' => $model,
                    'purpose' => $purpose,
                    'attempt' => $attempts,
                    'exception' => $exception::class,
                ]);
                break;
            }
        }

        return $this->result(null, $lastOutcome, $attempts, $startedAt);
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

        $text = collect($parts)->pluck('text')->filter()->implode("\n");
        $text = trim(strip_tags($text));

        return $text !== '' ? Str::limit($text, 1600, '') : null;
    }

    private function result(?string $text, string $outcome, int $attempts, int $startedAt): GeminiTransportResult
    {
        return new GeminiTransportResult($text, $outcome, $attempts, $this->elapsedMs($startedAt));
    }

    private function elapsedMs(int $startedAt): int
    {
        return (int) round((hrtime(true) - $startedAt) / 1_000_000);
    }
}
