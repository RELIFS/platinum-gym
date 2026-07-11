import test from 'node:test';
import assert from 'node:assert/strict';

import {
    createClientMessageId,
    gymmiStorageKey,
    readGymmiState,
    requestAssistantReply,
    writeGymmiState,
} from '../../resources/js/public-chatbot.js';

test('creates UUID client message IDs', () => {
    assert.match(createClientMessageId(), /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i);
});

test('scopes session storage to surface and server namespace', () => {
    assert.equal(gymmiStorageKey({ memoryEnabled: true, storageScope: 'member', storageNamespace: 'scope-token' }), 'platinum-gym:gymmi:member:scope-token');
    assert.equal(gymmiStorageKey({ memoryEnabled: false, storageNamespace: 'scope-token' }), null);
});

test('stores at most twelve display messages', () => {
    const values = new Map();
    globalThis.sessionStorage = {
        getItem: (key) => values.get(key) ?? null,
        setItem: (key, value) => values.set(key, value),
        removeItem: (key) => values.delete(key),
    };
    const messages = Array.from({ length: 15 }, (_, index) => ({ from: 'user', text: `Pesan ${index}` }));

    writeGymmiState('gymmi-test', 'conversation-token', messages);
    const state = readGymmiState('gymmi-test');

    assert.equal(state.conversationId, 'conversation-token');
    assert.equal(state.messages.length, 12);
    assert.equal(state.messages[0].text, 'Pesan 3');
});

test('request contract sends no trusted surface or history', async () => {
    const originalFetch = globalThis.fetch;
    let request;
    globalThis.fetch = async (_url, options) => {
        request = JSON.parse(options.body);

        return {
            ok: true,
            status: 200,
            json: async () => ({
                reply: { text: 'Jawaban aman.', action: null },
                conversation: { id: 'a'.repeat(64), reset: false },
            }),
        };
    };

    const result = await requestAssistantReply('Halo', {
        chatEnabled: true,
        endpoint: '/gymmi/chat',
        timeoutMs: 1000,
        csrfToken: 'token',
    }, null, '123e4567-e89b-42d3-a456-426614174000');

    globalThis.fetch = originalFetch;
    assert.equal(result.ok, true);
    assert.deepEqual(Object.keys(request).sort(), ['client_message_id', 'conversation_id', 'message']);
});

test('offline request returns retryable nonfactual copy', async () => {
    const originalFetch = globalThis.fetch;
    const originalNavigator = globalThis.navigator;
    globalThis.fetch = async () => { throw new TypeError('Network error'); };
    Object.defineProperty(globalThis, 'navigator', { value: { onLine: false }, configurable: true, writable: true });

    const result = await requestAssistantReply('Halo', {
        chatEnabled: true,
        endpoint: '/gymmi/chat',
        timeoutMs: 1000,
    });

    globalThis.fetch = originalFetch;
    Object.defineProperty(globalThis, 'navigator', { value: originalNavigator, configurable: true, writable: true });
    assert.equal(result.ok, false);
    assert.equal(result.retryable, true);
    assert.match(result.message, /Koneksi internet terputus/);
});
