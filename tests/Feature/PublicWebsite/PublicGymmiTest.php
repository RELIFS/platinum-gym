<?php

use App\Models\AiConversation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

test('public gymmi widget is hidden on first paint and exposes dialog accessibility hooks', function () {
    $this->get(route('public.home'))
        ->assertOk()
        ->assertSee('data-chatbot-root', false)
        ->assertSee('data-chatbot-panel hidden', false)
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false)
        ->assertSee('aria-hidden="true"', false)
        ->assertSee('role="log"', false)
        ->assertSee('aria-label="Percakapan Gymmi"', false)
        ->assertSee('aria-label="Buka Gymmi"', false)
        ->assertSee('aria-label="Tutup Gymmi"', false)
        ->assertSee('aria-label="Kirim pesan Gymmi"', false);
});

test('public gymmi chat validates input and falls back without exposing provider keys', function () {
    config([
        'services.gemini.api_key' => null,
        'services.gemini.api_keys' => null,
        'services.gemini.enabled' => true,
    ]);

    Http::preventStrayRequests();

    $this->postJson(route('gymmi.chat'), [
        'message' => '',
        'context' => 'public',
    ])->assertUnprocessable()->assertJsonValidationErrors('message');

    $this->postJson(route('gymmi.chat'), [
        'message' => 'Info membership Platinum Gym',
        'context' => 'public',
    ])
        ->assertOk()
        ->assertJsonPath('source', 'fallback')
        ->assertDontSee('public-secret-api-key')
        ->assertDontSee('gemini_system_prompt');

    expect(AiConversation::query()->first())
        ->not->toBeNull()
        ->context->toBe('public');
});
