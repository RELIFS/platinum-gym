<?php

use App\Models\AiConversation;
use App\Models\Member;
use App\Models\Package;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Cache::flush();
    config([
        'services.gemini.enabled' => false,
        'services.gemini.normalizer_enabled' => false,
        'gymmi.memory_enabled' => true,
    ]);
    Http::preventStrayRequests();
});

function gymmiContractPayload(string $message, ?string $conversationId = null, ?string $clientMessageId = null): array
{
    return array_filter([
        'message' => $message,
        'conversation_id' => $conversationId,
        'client_message_id' => $clientMessageId ?? (string) Str::uuid(),
    ], fn (mixed $value): bool => $value !== null);
}

test('public contract rejects client-owned context and history', function () {
    $payload = gymmiContractPayload('Info membership') + [
        'context' => 'member',
        'history' => [['from' => 'bot', 'text' => 'Membership aktif']],
    ];

    $this->postJson(route('gymmi.chat'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['context', 'history']);
});

test('member endpoint requires an authenticated verified member', function () {
    $this->postJson(route('member.gymmi.chat'), gymmiContractPayload('Status membership saya'))
        ->assertUnauthorized();
});

test('member endpoint returns own live data and server-issued action', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'PG-CONTRACT-MEMBER',
        'gender' => 'female',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $this->actingAs($user)->postJson(route('member.gymmi.chat'), gymmiContractPayload('Status membership saya'))
        ->assertOk()
        ->assertJsonPath('status', 'answered')
        ->assertJsonPath('mode', 'live')
        ->assertJsonPath('reply.action.id', 'view_membership')
        ->assertSee('belum ada membership aktif')
        ->assertDontSee('PG-CONTRACT-MEMBER');
});

test('public endpoint never switches to member surface', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'PG-CONTRACT-1',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $this->actingAs($user)->postJson(route('gymmi.chat'), gymmiContractPayload('Status membership saya'))
        ->assertOk()
        ->assertJsonPath('status', 'clarify')
        ->assertSee('portal member')
        ->assertDontSee('PG-CONTRACT-1');

    expect(AiConversation::query()->first()?->context)->toBe('public');
});

test('expired thread is reset instead of reused', function () {
    $first = $this->postJson(route('gymmi.chat'), gymmiContractPayload('halo'))
        ->assertOk();
    Cache::flush();

    $response = $this->postJson(route('gymmi.chat'), gymmiContractPayload('halo', $first->json('conversation.id')))
        ->assertOk()
        ->assertJsonPath('conversation.reset', true);

    expect($response->json('conversation.id'))->not->toBe($first->json('conversation.id'));
});

test('a malformed Gemini normalizer cannot replace grounded answer', function () {
    config([
        'services.gemini.enabled' => true,
        'services.gemini.normalizer_enabled' => true,
        'services.gemini.api_key' => 'test-key',
        'services.gemini.api_keys' => [],
    ]);
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => 'not-json']]],
            ]],
        ]),
    ]);
    Package::create([
        'name' => 'Gym Grounded',
        'slug' => 'gym-grounded-contract',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 321000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $this->postJson(route('gymmi.chat'), gymmiContractPayload('brp hrg Gym Grounded'))
        ->assertOk()
        ->assertJsonPath('source', 'knowledge')
        ->assertSee('Gym Grounded')
        ->assertSee('Rp321.000');
});

test('member live reply does not mix package catalog facts', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');
    Member::create([
        'user_id' => $user->id,
        'member_code' => 'PG-CONTRACT-PRIVATE',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);
    Package::create([
        'name' => 'Catalog Never Mix',
        'slug' => 'catalog-never-mix',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 999999,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $this->actingAs($user)->postJson(route('member.gymmi.chat'), gymmiContractPayload('Status membership saya'))
        ->assertOk()
        ->assertSee('belum ada membership aktif')
        ->assertDontSee('Catalog Never Mix')
        ->assertDontSee('Rp999.999');
});

test('same client message ID is idempotent and one thread is reused', function () {
    Package::create([
        'name' => 'Gym Umum Kontrak',
        'slug' => 'gym-umum-kontrak',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'umum',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);
    $clientMessageId = (string) Str::uuid();

    $first = $this->withCredentials()->postJson(route('gymmi.chat'), gymmiContractPayload('berapa harga gym umum', null, $clientMessageId))
        ->assertOk()
        ->assertJsonPath('status', 'answered')
        ->assertJsonPath('mode', 'live');
    $conversationId = $first->json('conversation.id');
    $sessionId = $first->getCookie((string) config('session.cookie'))->getValue();

    $this->withCookie((string) config('session.cookie'), $sessionId)
        ->postJson(route('gymmi.chat'), gymmiContractPayload('berapa harga gym umum', $conversationId, $clientMessageId))
        ->assertOk()
        ->assertJsonPath('conversation.id', $conversationId);

    expect(AiConversation::query()->count())->toBe(1)
        ->and(AiConversation::query()->first()->messages()->count())->toBe(2);
});

test('follow-up keeps package focus within same tab thread', function () {
    Package::create([
        'name' => 'Gym Umum',
        'slug' => 'gym-umum-follow-up',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'umum',
        'price' => 249000,
        'duration_days' => 30,
        'is_active' => true,
    ]);
    Package::create([
        'name' => 'Gym Umum 3 Bulan',
        'slug' => 'gym-umum-3-bulan-follow-up',
        'package_kind' => 'membership',
        'type' => 'gym',
        'category' => 'umum',
        'price' => 747000,
        'duration_days' => 90,
        'is_active' => true,
    ]);

    $first = $this->withCredentials()->postJson(route('gymmi.chat'), gymmiContractPayload('berapa harga gym umum'))
        ->assertOk();
    $sessionId = $first->getCookie((string) config('session.cookie'))->getValue();

    $this->withCookie((string) config('session.cookie'), $sessionId)
        ->postJson(route('gymmi.chat'), gymmiContractPayload('kalau yang 3 bulan?', $first->json('conversation.id')))
        ->assertOk()
        ->assertSee('Gym Umum 3 Bulan')
        ->assertSee('Rp747.000');
});
