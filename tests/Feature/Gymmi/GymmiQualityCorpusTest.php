<?php

use App\Features\Gymmi\Support\GymmiIntentDetector;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    Cache::flush();
    config([
        'services.gemini.enabled' => false,
        'services.gemini.normalizer_enabled' => false,
    ]);
    Http::preventStrayRequests();
    Setting::query()->updateOrCreate(
        ['key' => 'operational_hours'],
        ['value' => json_encode(['monday_saturday' => '08:00-22:00', 'sunday' => 'Tutup']), 'type' => 'json', 'group' => 'general'],
    );
});

it('classifies the deterministic quality corpus without substring collisions', function () {
    $detector = app(GymmiIntentDetector::class);
    $cases = require base_path('tests/Fixtures/Gymmi/quality-corpus.php');

    foreach ($cases as $case) {
        if (! isset($case['intent'])) {
            continue;
        }

        expect($detector->detect($case['message'])['intent'])->toBe($case['intent']);
    }
});

it('answers public corpus cases through the authoritative endpoint', function () {
    $cases = collect(require base_path('tests/Fixtures/Gymmi/quality-corpus.php'))
        ->filter(fn (array $case): bool => $case['surface'] === 'public' && isset($case['status']))
        ->values();

    foreach ($cases as $case) {
        $response = $this->postJson(route('gymmi.chat'), [
            'message' => $case['message'],
            'client_message_id' => (string) Str::uuid(),
        ])->assertOk()->assertJsonPath('status', $case['status']);

        foreach ($case['contains'] ?? [] as $needle) {
            expect((string) $response->json('reply.text'))->toContain($needle);
        }
    }
});
