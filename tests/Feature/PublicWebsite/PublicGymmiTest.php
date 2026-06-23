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
        ->assertSee('aria-label="Pertanyaan cepat Gymmi"', false)
        ->assertSee('aria-label="Buka Gymmi"', false)
        ->assertSee('aria-label="Tutup Gymmi"', false)
        ->assertSee('aria-label="Kirim pesan Gymmi"', false)
        ->assertSee('avatar-gymmi-light.png', false)
        ->assertSee('avatar-gymmi-dark.png', false)
        ->assertSee('gymmi-quick-reply-rail', false)
        ->assertSee('border border-zinc-200 bg-white', false)
        ->assertSee('dark:border-zinc-800 dark:bg-zinc-950', false)
        ->assertSee('border-b border-zinc-200 bg-zinc-50', false)
        ->assertSee('dark:border-zinc-800 dark:bg-white/[0.04] dark:text-white', false)
        ->assertSee('border-t border-zinc-200 px-3 py-3 dark:border-zinc-800', false)
        ->assertDontSee('border-zinc-700 bg-zinc-900 text-base text-zinc-100', false);
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
