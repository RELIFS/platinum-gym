<?php

namespace App\Features\Gymmi\Clients;

use App\Features\Gymmi\Contracts\GymmiAssistantClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GeminiGymmiClient implements GymmiAssistantClient
{
    public function ask(string $message, string $context, array $history = []): ?string
    {
        if (! (bool) config('services.gemini.enabled', true)) {
            return null;
        }

        $keys = $this->keys();

        if ($keys === []) {
            return null;
        }

        $model = $this->model();
        $body = $this->payload($message, $context, $history);
        $circuitKey = $this->circuitKey($model);

        if (Cache::has($circuitKey)) {
            return null;
        }

        foreach ($this->prioritizedKeys($keys) as $attempt => $key) {
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

                Log::warning('Gemini Gymmi request failed.', [
                    'status' => $response->status(),
                    'model' => $model,
                    'attempt' => $attempt + 1,
                ]);

                if ($response->status() === 429) {
                    Cache::put($circuitKey, true, now()->addSeconds($this->circuitBreakerSeconds()));

                    break;
                }

                if ($response->status() === 404) {
                    break;
                }

                if (! in_array($response->status(), [429, 500, 502, 503, 504], true)) {
                    break;
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return null;
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

    /**
     * @param  array<int, string>  $keys
     * @return array<int, string>
     */
    private function prioritizedKeys(array $keys): array
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

    private function model(): string
    {
        return Str::of((string) config('services.gemini.model', 'gemini-2.0-flash'))
            ->after('models/')
            ->trim()
            ->toString();
    }

    private function circuitKey(string $model): string
    {
        return 'gymmi:gemini:circuit:'.sha1($model);
    }

    private function circuitBreakerSeconds(): int
    {
        return max(30, (int) config('services.gemini.circuit_breaker_seconds', 300));
    }

    /**
     * @param  array<int, array{from?: string, text?: string}>  $history
     * @return array<string, mixed>
     */
    private function payload(string $message, string $context, array $history): array
    {
        $historyText = collect($history)
            ->take(-6)
            ->map(function (array $item): string {
                $from = ($item['from'] ?? '') === 'bot' ? 'Gymmi' : 'User';
                $text = Str::limit(strip_tags((string) ($item['text'] ?? '')), 240, '');

                return "{$from}: {$text}";
            })
            ->filter(fn (string $line) => filled(trim($line)))
            ->implode("\n");

        $prompt = trim(implode("\n\n", array_filter([
            "Konteks Platinum Gym Padang:\n{$context}",
            $historyText !== '' ? "Riwayat percakapan ringkas:\n{$historyText}" : null,
            'Pertanyaan user: '.Str::limit($message, 700, ''),
        ])));

        return [
            'systemInstruction' => [
                'parts' => [[
                    'text' => 'Anda adalah Gymmi, asisten resmi website Platinum Gym Padang. Jawab dalam Bahasa Indonesia yang ramah, ringkas, dan praktis. Jawab hanya berdasarkan konteks yang diberikan dan pengetahuan umum aman tentang penggunaan website gym. Jika data tidak tersedia, arahkan user mengecek halaman terkait atau admin. Jangan meminta password, API key, token pembayaran, raw QR token, atau data sensitif. Jangan memberi diagnosis medis; sarankan konsultasi profesional untuk kondisi kesehatan.',
                ]],
            ],
            'contents' => [[
                'role' => 'user',
                'parts' => [[
                    'text' => $prompt,
                ]],
            ]],
            'generationConfig' => [
                'temperature' => (float) config('services.gemini.temperature', 0.45),
                'maxOutputTokens' => (int) config('services.gemini.max_output_tokens', 500),
            ],
        ];
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
