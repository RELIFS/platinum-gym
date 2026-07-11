<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
        ->assertSee('data-chatbot-trigger', false)
        ->assertSee('aria-controls="gymmi-public-panel"', false)
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('maxlength="700"', false)
        ->assertSee('gymmi-trigger', false)
        ->assertSee('gymmi-chat-trigger-160.webp', false)
        ->assertSee('gymmi-chat-trigger-320.webp', false)
        ->assertSee('gymmi-chat-trigger-480.webp', false)
        ->assertSee('srcset=', false)
        ->assertSee('sizes="(min-width: 768px) 128px, (min-width: 480px) 112px, 92px"', false)
        ->assertSee('data-gymmi-trigger-image', false)
        ->assertSee('gymmi-trigger-fallback', false)
        ->assertDontSee('gymmi-chat-trigger-96.webp', false)
        ->assertDontSee('gymmi-chat-trigger-256.webp', false)
        ->assertSee('data-gymmi-panel-avatar', false)
        ->assertSee('data-gymmi-panel-avatar-image', false)
        ->assertSee('gymmi-panel-avatar-fallback', false)
        ->assertSee('avatar-gymmi-light-96.webp', false)
        ->assertSee('avatar-gymmi-light-192.webp', false)
        ->assertSee('avatar-gymmi-dark-96.webp', false)
        ->assertSee('avatar-gymmi-dark-192.webp', false)
        ->assertDontSee('avatar-gymmi-light.png', false)
        ->assertDontSee('avatar-gymmi-dark.png', false)
        ->assertSee('gymmi-quick-reply-rail', false)
        ->assertSee('border border-zinc-200 bg-white', false)
        ->assertSee('dark:border-zinc-800 dark:bg-zinc-950', false)
        ->assertSee('border-b border-zinc-200 bg-zinc-50', false)
        ->assertSee('dark:border-zinc-800 dark:bg-white/[0.04] dark:text-zinc-100', false)
        ->assertSee('border-t border-zinc-200 px-3 py-3 dark:border-zinc-800', false)
        ->assertDontSee('border-zinc-700 bg-zinc-900 text-base text-zinc-100', false);
});

test('public gymmi chat validates the server-owned contract', function () {
    $this->postJson(route('gymmi.chat'), [
        'message' => '',
        'client_message_id' => (string) Str::uuid(),
    ])->assertUnprocessable()->assertJsonValidationErrors('message');

    $this->postJson(route('gymmi.chat'), [
        'message' => 'Info membership Platinum Gym',
        'client_message_id' => (string) Str::uuid(),
        'context' => 'public',
    ])->assertUnprocessable()->assertJsonValidationErrors('context');
});
