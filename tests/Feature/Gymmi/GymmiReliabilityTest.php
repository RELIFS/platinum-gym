<?php

use App\Models\Package;
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
});

function gymmiReliablePayload(string $message, ?string $thread = null, ?string $messageId = null): array
{
    return [
        'message' => $message,
        'conversation_id' => $thread,
        'client_message_id' => $messageId ?? (string) Str::uuid(),
    ];
}

test('AI-disabled Gymmi still uses live Laravel facts', function () {
    Package::create([
        'name' => 'Gym AI Disabled',
        'slug' => 'gym-ai-disabled',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 234000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $this->postJson(route('gymmi.chat'), gymmiReliablePayload('harga Gym AI Disabled'))
        ->assertOk()
        ->assertJsonPath('status', 'answered')
        ->assertJsonPath('mode', 'live')
        ->assertSee('Gym AI Disabled')
        ->assertSee('Rp234.000');
});

test('idempotent retry returns the same request response without duplicate log turns', function () {
    $id = (string) Str::uuid();
    $first = $this->withCredentials()->postJson(route('gymmi.chat'), gymmiReliablePayload('halo', null, $id))
        ->assertOk();
    $sessionId = $first->getCookie((string) config('session.cookie'))->getValue();

    $retry = $this->withCookie((string) config('session.cookie'), $sessionId)
        ->postJson(route('gymmi.chat'), gymmiReliablePayload('halo', $first->json('conversation.id'), $id))
        ->assertOk();

    expect($retry->json('request_id'))->toBe($first->json('request_id'));
});
