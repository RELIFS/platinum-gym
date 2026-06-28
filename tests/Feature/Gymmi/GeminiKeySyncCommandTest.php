<?php

use App\Features\Gymmi\Support\GeminiApiKeyPool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    Cache::flush();
    File::ensureDirectoryExists(storage_path('framework/testing'));
});

function fakeGeminiKeysForSyncTest(int $count): array
{
    return collect(range(1, $count))
        ->map(fn (int $number): string => 'AIza'.str_pad((string) $number, 36, 'A', STR_PAD_LEFT))
        ->all();
}

test('gymmi gemini key sync dry run reports counts without printing keys', function () {
    $keys = fakeGeminiKeysForSyncTest(100);
    $source = storage_path('framework/testing/gemini-keys-dry-run.txt');

    File::put($source, implode(PHP_EOL, [
        ...$keys,
        $keys[0],
        'not-a-gemini-key',
        'GEMINI_API_KEY=invalid',
        '',
    ]));

    $this->artisan('gymmi:sync-gemini-keys', ['source' => $source])
        ->expectsOutputToContain('Validasi Gemini API keys selesai.')
        ->expectsOutputToContain('Valid unique keys: 100')
        ->expectsOutputToContain('Duplicate keys: 1')
        ->expectsOutputToContain('Invalid tokens: 2')
        ->expectsOutputToContain('Dry-run aktif. Tidak ada file yang diubah.')
        ->doesntExpectOutputToContain($keys[0])
        ->doesntExpectOutputToContain($keys[99])
        ->assertExitCode(0);
});

test('gymmi gemini key sync writes env target while preserving existing values', function () {
    $keys = fakeGeminiKeysForSyncTest(3);
    $source = storage_path('framework/testing/gemini-keys-write.txt');
    $env = storage_path('framework/testing/gemini-sync.env');

    File::put($source, implode(PHP_EOL, $keys));
    File::put($env, implode(PHP_EOL, [
        'APP_NAME="Platinum Gym Padang"',
        'GEMINI_MODEL=gemini-2.0-flash',
        'GEMINI_API_KEYS="old-value"',
        'GYMMI_AI_ENABLED=true',
        '',
    ]));

    $this->artisan('gymmi:sync-gemini-keys', [
        'source' => $source,
        '--write-env' => true,
        '--env' => $env,
    ])
        ->expectsOutputToContain('GEMINI_API_KEYS berhasil disimpan ke env target.')
        ->expectsOutputToContain('Nilai key tidak ditampilkan di output.')
        ->doesntExpectOutputToContain($keys[0])
        ->assertExitCode(0);

    $contents = File::get($env);

    expect($contents)
        ->toContain('APP_NAME="Platinum Gym Padang"')
        ->toContain('GEMINI_MODEL=gemini-2.0-flash')
        ->toContain('GYMMI_AI_ENABLED=true')
        ->toContain('GEMINI_API_KEYS="'.implode(',', $keys).'"')
        ->not->toContain('old-value');
});

test('gymmi gemini key pool status parses many env keys safely', function () {
    $keys = fakeGeminiKeysForSyncTest(100);

    config([
        'services.gemini.api_key' => null,
        'services.gemini.api_keys' => implode(',', $keys),
        'services.gemini.model' => 'gemini-test-flash',
        'services.gemini.max_retries' => 2,
    ]);

    $status = app(GeminiApiKeyPool::class)->status();

    expect($status)
        ->toMatchArray([
            'configured' => 100,
            'available' => 100,
            'invalid' => 0,
            'cooldown' => 0,
            'max_attempts' => 2,
            'model' => 'gemini-test-flash',
            'model_circuit_open' => false,
        ]);
});

test('gymmi gemini key status command prints safe metadata only', function () {
    $keys = fakeGeminiKeysForSyncTest(100);

    config([
        'services.gemini.api_key' => null,
        'services.gemini.api_keys' => implode(PHP_EOL, $keys),
        'services.gemini.model' => 'gemini-test-flash',
        'services.gemini.max_retries' => 2,
    ]);

    $this->artisan('gymmi:sync-gemini-keys', ['--status' => true])
        ->expectsOutputToContain('Status Gemini key pool Gymmi')
        ->expectsOutputToContain('Configured keys: 100')
        ->expectsOutputToContain('Available keys: 100')
        ->expectsOutputToContain('Max attempts per request: 2')
        ->expectsOutputToContain('Model: gemini-test-flash')
        ->doesntExpectOutputToContain($keys[0])
        ->doesntExpectOutputToContain($keys[99])
        ->assertExitCode(0);
});
