<?php

use App\Models\AiConversation;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Cache::flush();
    config([
        'gymmi.composer_enabled' => true,
        'services.gemini.enabled' => true,
        'services.gemini.normalizer_enabled' => false,
        'services.gemini.api_key' => 'composer-test-key',
        'services.gemini.api_keys' => [],
    ]);
});

function gymmiComposerPayload(string $message): array
{
    return ['message' => $message, 'client_message_id' => (string) Str::uuid()];
}

test('composer draft with unsupported fact falls back to deterministic answer', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'answer' => 'Admin dapat dihubungi di WhatsApp 081234567890.',
                        'used_fact_ids' => ['fact-1'],
                    ], JSON_THROW_ON_ERROR),
                ]]],
            ]],
        ]),
    ]);

    $this->postJson(route('gymmi.chat'), gymmiComposerPayload('Tolong buat versi singkat tentang fasilitas gym'))
        ->assertOk()
        ->assertJsonPath('source', 'knowledge')
        ->assertJsonPath('mode', 'fallback')
        ->assertDontSee('081234567890');

    expect(AiConversation::query()->latest()->first()?->meta['composer_rejection'] ?? null)
        ->toBe('unsupported_literal');
});

test('composer never receives member own-data', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'PG-COMPOSER-MEMBER',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);
    Http::preventStrayRequests();

    $this->actingAs($user)->postJson(route('member.gymmi.chat'), gymmiComposerPayload('Status membership saya'))
        ->assertOk()
        ->assertJsonPath('mode', 'live')
        ->assertDontSee('PG-COMPOSER-MEMBER');
});
