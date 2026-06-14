<?php

use App\Models\AiConversation;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function configureGeminiForTest(array $overrides = []): void
{
    config(array_merge([
        'services.gemini.api_key' => null,
        'services.gemini.api_keys' => 'test-gemini-key-one,test-gemini-key-two',
        'services.gemini.model' => 'gemini-test-flash',
        'services.gemini.base_url' => 'https://generativelanguage.googleapis.com',
        'services.gemini.enabled' => true,
        'services.gemini.timeout' => 5,
        'services.gemini.connect_timeout' => 2,
        'services.gemini.max_output_tokens' => 120,
        'services.gemini.temperature' => 0.2,
        'services.gemini.rate_limit_per_minute' => 12,
    ], $overrides));
}

test('public gymmi chat uses gemini and stores conversation', function () {
    configureGeminiForTest();

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'text' => 'Jadwal kelas aktif bisa dicek di halaman Kelas. Pilih filter hari untuk melihat sesi terbaru.',
                    ]],
                ],
            ]],
        ]),
    ]);

    $this->postJson(route('gymmi.chat'), [
        'message' => 'Ada jadwal kelas apa hari ini?',
        'context' => 'public',
        'history' => [
            ['from' => 'user', 'text' => 'Halo Gymmi'],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('source', 'gemini')
        ->assertJsonPath('reply.text', 'Jadwal kelas aktif bisa dicek di halaman Kelas. Pilih filter hari untuk melihat sesi terbaru.')
        ->assertDontSee('test-gemini-key-one')
        ->assertDontSee('test-gemini-key-two');

    Http::assertSent(fn ($request): bool => $request->hasHeader('x-goog-api-key')
        && str_contains($request->url(), '/v1beta/models/gemini-test-flash:generateContent'));

    $conversation = AiConversation::query()->first();

    expect($conversation)->not->toBeNull()
        ->and($conversation->context)->toBe('public')
        ->and($conversation->model)->toBe('gemini-test-flash')
        ->and($conversation->messages)->toHaveCount(2);
});

test('gymmi chat falls back locally when gemini key is unavailable', function () {
    configureGeminiForTest([
        'services.gemini.api_key' => null,
        'services.gemini.api_keys' => null,
    ]);

    Http::preventStrayRequests();

    $this->postJson(route('gymmi.chat'), [
        'message' => 'Info membership Platinum Gym',
        'context' => 'public',
    ])
        ->assertOk()
        ->assertJsonPath('source', 'fallback')
        ->assertJsonFragment(['text' => 'Paket membership tersedia untuk umum dan mahasiswa. Mulai dari Gym Umum, Gym Mahasiswa, serta paket khusus sesuai promo aktif. Untuk daftar, gunakan tombol Daftar Member.']);

    expect(AiConversation::query()->first()?->meta)->toMatchArray(['source' => 'fallback']);
});

test('gymmi chat validates message input', function () {
    configureGeminiForTest();

    $this->postJson(route('gymmi.chat'), [
        'message' => '',
        'context' => 'public',
    ])->assertUnprocessable()->assertJsonValidationErrors('message');

    $this->postJson(route('gymmi.chat'), [
        'message' => str_repeat('a', 701),
        'context' => 'public',
    ])->assertUnprocessable()->assertJsonValidationErrors('message');
});

test('member gymmi chat logs conversation to authenticated member user', function () {
    configureGeminiForTest([
        'services.gemini.api_key' => null,
        'services.gemini.api_keys' => null,
    ]);

    $user = User::factory()->create([
        'name' => 'Member Gymmi',
        'email' => 'member.gymmi@example.com',
    ]);
    $user->assignRole('member');

    Member::create([
        'user_id' => $user->id,
        'member_code' => 'PG-GYMMI-0001',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $this->actingAs($user)->postJson(route('gymmi.chat'), [
        'message' => 'Status membership saya bagaimana?',
        'context' => 'member',
    ])
        ->assertOk()
        ->assertJsonPath('source', 'fallback')
        ->assertJsonMissing(['PG-GYMMI-0001']);

    $conversation = AiConversation::query()->first();

    expect($conversation)->not->toBeNull()
        ->and($conversation->user_id)->toBe($user->id)
        ->and($conversation->context)->toBe('member');
});
