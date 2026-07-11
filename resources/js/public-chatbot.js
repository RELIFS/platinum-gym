export function initPlatinumGymChatbots() {
    document.querySelectorAll('[data-chatbot-root]').forEach((root) => initChatbotRoot(root));
}

function initChatbotRoot(root) {
    if (root.dataset.chatbotReady === 'true') {
        return;
    }

    root.dataset.chatbotReady = 'true';

    const config = window[root.dataset.chatbotConfig] ?? {};
    const panel = root.querySelector('[data-chatbot-panel]');
    const overlay = root.querySelector('[data-chatbot-overlay]');
    const trigger = root.querySelector('[data-chatbot-trigger]');
    const closeButtons = root.querySelectorAll('[data-chatbot-close]');
    const messages = root.querySelector('[data-chatbot-messages]');
    const messagesEnd = root.querySelector('[data-chatbot-messages-end]');
    const quickReplies = root.querySelector('[data-chatbot-quick-replies]');
    const input = root.querySelector('[data-chatbot-input]');
    const send = root.querySelector('[data-chatbot-send]');
    const escalation = root.querySelector('[data-chatbot-escalation]');
    const triggerImages = root.querySelectorAll('[data-gymmi-trigger-image]');
    const panelAvatars = root.querySelectorAll('[data-gymmi-panel-avatar]');
    let open = false;
    let typing = false;
    let lastFocusedElement = null;
    let menuSuppressed = false;
    const storageKey = gymmiStorageKey(config);
    const storedState = readGymmiState(storageKey);
    let conversationId = storedState?.conversationId ?? null;

    if (!panel || !trigger || !messages || !input || !send) {
        return;
    }

    const restoredMessages = Array.isArray(storedState?.messages) ? storedState.messages.slice(-12) : [];

    if (restoredMessages.length > 0) {
        restoredMessages.forEach((message) => renderMessage(messages, message.from, normalizeBotReply(message), root.dataset.chatbotVariant, config, { quickReply: message.quickReply === true }));
    } else {
        renderMessage(messages, 'bot', normalizeBotReply(config.initialMessage ?? ''), root.dataset.chatbotVariant, config);
    }

    const persist = () => writeGymmiState(storageKey, conversationId, collectDisplayMessages(messages));

    renderQuickReplies(quickReplies, config.quickReplies ?? [], root.dataset.chatbotVariant, (reply) => {
        if (typing) {
            return;
        }

        const clientMessageId = createClientMessageId();
        addUserMessage(reply, true, clientMessageId);
        queueReply(reply, clientMessageId);
    });

    if (escalation) {
        escalation.hidden = config.showEscalation === false;
        escalation.href = config.whatsappUrl ?? '#';
    }

    triggerImages.forEach((image) => {
        image.addEventListener('error', () => {
            image.hidden = true;
            image.nextElementSibling?.removeAttribute('hidden');
        });
    });
    panelAvatars.forEach(bindPanelAvatarFallback);

    const setTypingState = (nextTyping) => {
        typing = nextTyping;
        root.setAttribute('aria-busy', typing.toString());
        panel.setAttribute('aria-busy', typing.toString());
        setQuickRepliesDisabled(quickReplies, typing);
        syncSendState(input, send, typing);
    };

    const setOpen = (nextOpen, returnFocus = true) => {
        open = nextOpen;
        document.body.classList.toggle('overflow-hidden', open && window.matchMedia('(max-width: 639px)').matches);
        panel.hidden = !open;
        panel.style.display = open ? 'flex' : 'none';
        panel.setAttribute('aria-hidden', (!open).toString());
        trigger.setAttribute('aria-expanded', open.toString());
        root.setAttribute('aria-busy', typing.toString());
        trigger.hidden = open;
        trigger.style.display = open ? 'none' : '';

        if (overlay) {
            overlay.hidden = !open;
            overlay.style.display = open ? 'block' : 'none';
        }

        window.requestAnimationFrame(() => {
            if (open) {
                panel.focus({ preventScroll: true });
                scrollToEnd(messagesEnd);

                return;
            }

            if (returnFocus) {
                const restoreFocus = () => (lastFocusedElement ?? trigger).focus?.({ preventScroll: true });

                restoreFocus();
                window.setTimeout(restoreFocus, 50);
            }
        });
    };

    const close = () => {
        setOpen(false);
        window.setTimeout(() => (lastFocusedElement ?? trigger).focus?.({ preventScroll: true }), 150);
    };

    const syncMenuSuppression = () => {
        menuSuppressed = document.documentElement.hasAttribute('data-mobile-menu-open');

        if (menuSuppressed) {
            root.setAttribute('data-chatbot-suppressed', 'true');

            if (open) {
                setOpen(false, false);
            }

            return;
        }

        root.removeAttribute('data-chatbot-suppressed');
    };

    new MutationObserver(syncMenuSuppression).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-mobile-menu-open'],
    });

    window.addEventListener('platinum-gym:mobile-menu-change', syncMenuSuppression);

    const submit = () => {
        const text = input.value.trim();

        if (!text || typing) {
            return;
        }

        const clientMessageId = createClientMessageId();
        addUserMessage(text, false, clientMessageId);
        input.value = '';
        syncSendState(input, send, typing);
        queueReply(text, clientMessageId);
    };

    function addUserMessage(text, quickReply = false, clientMessageId = null) {
        renderMessage(messages, 'user', { text, clientMessageId }, root.dataset.chatbotVariant, config, { quickReply });
        persist();
        scrollToEnd(messagesEnd);
    }

    async function queueReply(text, clientMessageId) {
        if (typing) {
            return;
        }

        setTypingState(true);
        const typingEl = renderTyping(messages, config, root.dataset.chatbotVariant);
        scrollToEnd(messagesEnd);
        const result = await requestAssistantReply(text, config, conversationId, clientMessageId);

        typingEl.remove();
        if (result.conversationId) {
            conversationId = result.conversationId;
        }

        if (result.ok) {
            renderMessage(messages, 'bot', normalizeBotReply(result.reply), root.dataset.chatbotVariant, config);
        } else {
            let errorItem = null;
            const retryHandler = result.retryable ? () => {
                errorItem?.remove();
                persist();
                queueReply(text, clientMessageId);
            } : null;
            errorItem = renderMessage(messages, 'bot', normalizeBotReply({
                text: result.message,
            }), root.dataset.chatbotVariant, config, {
                onRetry: retryHandler,
                transient: true,
            });
            errorItem.dataset.chatbotErrorFor = clientMessageId;
        }

        persist();
        setTypingState(false);
        scrollToEnd(messagesEnd);
        input.focus({ preventScroll: true });
    }

    trigger.addEventListener('click', () => {
        if (menuSuppressed) {
            return;
        }

        lastFocusedElement = trigger;
        setOpen(true);
    });

    overlay?.addEventListener('click', close);
    closeButtons.forEach((button) => button.addEventListener('click', close));
    send.addEventListener('click', submit);
    input.addEventListener('input', () => syncSendState(input, send, typing));
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.isComposing) {
            event.preventDefault();
            submit();
        }
    });

    panel.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            close();

            return;
        }

        if (event.key === 'Tab') {
            trapFocus(event, panel);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && open) {
            event.preventDefault();
            close();
        }
    });

    syncSendState(input, send, typing);
    setQuickRepliesDisabled(quickReplies, false);
    setOpen(false, false);
    syncMenuSuppression();
}

export async function requestAssistantReply(text, config = {}, conversationId = null, clientMessageId = createClientMessageId()) {
    if (!config.chatEnabled || !config.endpoint) {
        return {
            ok: false,
            message: 'Layanan Gymmi sedang tidak tersedia. Silakan coba lagi nanti.',
            retryable: false,
            conversationId,
        };
    }

    const controller = new AbortController();
    const timeout = globalThis.setTimeout(() => controller.abort(), Number(config.timeoutMs ?? 9000));

    try {
        const response = await fetch(config.endpoint, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken ?? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
            body: JSON.stringify({
                message: text,
                conversation_id: conversationId,
                client_message_id: clientMessageId,
            }),
            signal: controller.signal,
        });
        const payload = await response.json().catch(() => ({}));
        const nextConversationId = payload?.conversation?.id ?? conversationId;

        if (!response.ok || !payload?.reply?.text) {
            return {
                ok: false,
                message: payload?.message ?? failureMessage(response.status),
                retryable: payload?.retryable ?? [429, 500, 502, 503, 504].includes(response.status),
                conversationId: nextConversationId,
            };
        }

        return {
            ok: true,
            reply: {
                text: payload.reply.text,
                actionLabel: payload.reply.action?.label ?? null,
                actionUrl: safeActionUrl(payload.reply.action?.url),
            },
            retryable: false,
            conversationId: nextConversationId,
        };
    } catch (error) {
        const offline = typeof navigator !== 'undefined' && navigator.onLine === false;

        return {
            ok: false,
            message: offline
                ? 'Koneksi internet terputus. Sambungkan kembali lalu coba lagi.'
                : (error?.name === 'AbortError' ? 'Respons Gymmi terlalu lama. Silakan coba lagi.' : 'Gymmi belum dapat terhubung ke server. Silakan coba lagi.'),
            retryable: true,
            conversationId,
        };
    } finally {
        globalThis.clearTimeout(timeout);
    }
}

function failureMessage(status) {
    if (status === 419) return 'Sesi halaman berakhir. Muat ulang halaman lalu coba lagi.';
    if (status === 422) return 'Pesan belum dapat diproses. Periksa isi pesan lalu coba lagi.';
    if (status === 429) return 'Permintaan terlalu sering. Tunggu sebentar lalu coba lagi.';

    return 'Gymmi sedang mengalami gangguan sementara. Silakan coba lagi.';
}

function safeActionUrl(url) {
    if (!url) return null;

    try {
        const parsed = new URL(url, window.location.origin);
        const whatsappHost = parsed.hostname === 'wa.me' || parsed.hostname.endsWith('.whatsapp.com');

        if (parsed.origin === window.location.origin || whatsappHost) {
            return parsed.href;
        }
    } catch (_error) {
        return null;
    }

    return null;
}

function renderQuickReplies(container, replies, variant = 'public', onClick) {
    if (!container) {
        return;
    }

    container.innerHTML = '';
    replies.forEach((reply) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.chatbotQuickReply = 'true';
        button.className = 'inline-flex min-h-10 shrink-0 snap-start touch-manipulation items-center whitespace-nowrap rounded-full border border-zinc-200 bg-zinc-50 px-3 py-2 text-left text-xs type-control leading-4 text-zinc-700 transition hover:border-gold-600/60 hover:text-gold-text-strong focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/30 disabled:cursor-not-allowed disabled:opacity-45 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:text-gold-400 dark:focus-visible:ring-gold-400/30';
        button.textContent = reply;
        button.addEventListener('click', () => {
            if (button.disabled) {
                return;
            }

            onClick(reply);
        });
        container.append(button);
    });
}

function renderMessage(container, from, reply, variant = 'public', config = {}, options = {}) {
    const isUser = from === 'user';
    const item = document.createElement('div');
    item.className = isUser ? 'flex min-w-0 justify-end' : 'flex min-w-0 items-start gap-2';
    item.setAttribute('aria-label', isUser ? 'Pesan Anda' : 'Pesan Gymmi');
    item.dataset.chatbotMessage = 'true';
    item.dataset.chatbotFrom = isUser ? 'user' : 'bot';
    item.dataset.chatbotText = reply.text ?? '';
    item.dataset.chatbotQuickReply = options.quickReply === true ? 'true' : 'false';
    item.dataset.chatbotTransient = options.transient === true ? 'true' : 'false';
    if (reply.clientMessageId) item.dataset.chatbotClientMessageId = reply.clientMessageId;

    const bubbleWrap = document.createElement('div');
    bubbleWrap.className = isUser
        ? 'flex min-w-0 max-w-[82%] flex-col items-end'
        : `${variant === 'member' ? 'max-w-[84%]' : 'max-w-[82%]'} flex min-w-0 flex-col items-start`;

    const bubble = document.createElement('p');
    bubble.className = messageBubbleClass(isUser, options.quickReply === true, variant);
    bubble.textContent = reply.text ?? '';
    bubbleWrap.append(bubble);

    if (reply.actionUrl && reply.actionLabel) {
        const action = document.createElement('a');
        action.href = reply.actionUrl;
        action.className = 'mt-2 inline-flex min-h-10 max-w-full items-center justify-center whitespace-normal break-words rounded-lg border border-gold-600/35 bg-gold-500/10 px-3 py-2 text-center text-xs type-control leading-5 text-gold-text transition hover:border-gold-600 hover:text-gold-text-strong focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:border-gold-400/30 dark:text-gold-400 dark:hover:text-gold-400 dark:focus-visible:ring-gold-400/40';
        action.textContent = reply.actionLabel;
        bubbleWrap.append(action);
        item.dataset.chatbotActionLabel = reply.actionLabel;
        item.dataset.chatbotActionUrl = reply.actionUrl;
    }

    if (typeof options.onRetry === 'function') {
        const retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'mt-2 inline-flex min-h-10 items-center justify-center rounded-lg border border-zinc-300 px-3 py-2 text-xs type-control text-zinc-700 hover:border-gold-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:border-zinc-600 dark:text-zinc-200';
        retry.textContent = 'Coba Lagi';
        retry.addEventListener('click', options.onRetry, { once: true });
        bubbleWrap.append(retry);
    }

    if (isUser) {
        item.append(bubbleWrap);
    } else {
        item.append(renderBotAvatar(config), bubbleWrap);
    }

    const end = container.querySelector('[data-chatbot-messages-end]');

    if (end) {
        container.insertBefore(item, end);
    } else {
        container.append(item);
    }

    return item;
}

function renderTyping(container, config = {}, variant = 'public') {
    const item = document.createElement('div');
    item.className = 'flex min-w-0 items-start gap-2';
    item.setAttribute('aria-label', config.typingLabel ?? 'Gymmi sedang mengetik');

    const bubble = document.createElement('div');
    bubble.className = 'flex items-center gap-1 rounded-2xl rounded-tl-sm border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-transparent dark:bg-zinc-800';
    bubble.setAttribute('role', 'status');
    bubble.setAttribute('aria-label', config.typingLabel ?? 'Gymmi sedang mengetik');

    [0, 150, 300].forEach((delay) => {
        const dot = document.createElement('span');
        dot.className = 'h-2 w-2 animate-bounce rounded-full bg-zinc-400';
        dot.style.animationDelay = `${delay}ms`;
        dot.setAttribute('aria-hidden', 'true');
        bubble.append(dot);
    });

    item.append(renderBotAvatar(config), bubble);
    const end = container.querySelector('[data-chatbot-messages-end]');

    if (end) {
        container.insertBefore(item, end);
    } else {
        container.append(item);
    }

    return item;
}

function renderBotAvatar(config = {}) {
    const avatar = document.createElement('span');
    avatar.className = 'gymmi-avatar';
    avatar.setAttribute('aria-hidden', 'true');

    const lightUrl = config.avatarLightUrl ?? null;
    const darkUrl = config.avatarDarkUrl ?? lightUrl;

    if (!lightUrl && !darkUrl) {
        avatar.textContent = config.botInitials ?? 'GY';

        return avatar;
    }

    const showFallback = () => {
        if (!avatar.querySelector('img')) {
            avatar.textContent = config.botInitials ?? 'GY';
        }
    };

    if (lightUrl) {
        avatar.append(createAvatarImage(lightUrl, darkUrl && darkUrl !== lightUrl ? 'block h-full w-full object-cover dark:hidden' : 'h-full w-full object-cover', showFallback));
    }

    if (darkUrl && darkUrl !== lightUrl) {
        avatar.append(createAvatarImage(darkUrl, 'hidden h-full w-full object-cover dark:block', showFallback));
    }

    return avatar;
}

function createAvatarImage(src, className, onError) {
    const image = document.createElement('img');
    image.src = src;
    image.alt = '';
    image.loading = 'lazy';
    image.decoding = 'async';
    image.className = className;
    image.addEventListener('error', () => {
        image.remove();
        onError();
    });

    return image;
}

function bindPanelAvatarFallback(avatar) {
    const images = Array.from(avatar.querySelectorAll('[data-gymmi-panel-avatar-image]'));
    const fallback = avatar.querySelector('.gymmi-panel-avatar-fallback');

    if (!fallback || images.length === 0) {
        return;
    }

    const syncFallback = () => {
        const isDark = document.documentElement.classList.contains('dark');
        const activeImages = images.filter((image) => (
            isDark ? image.classList.contains('dark:block') : !image.classList.contains('dark:block')
        ));

        fallback.hidden = activeImages.some((image) => !image.hidden);
    };

    images.forEach((image) => {
        image.addEventListener('error', () => {
            image.hidden = true;
            syncFallback();
        });
    });

    syncFallback();

    new MutationObserver(syncFallback).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
}

function messageBubbleClass(isUser, quickReply, variant = 'public') {
    if (quickReply) {
        return 'max-w-full break-words rounded-full bg-gold-500 px-3 py-2 text-xs type-control leading-5 text-zinc-950 shadow-sm';
    }

    if (isUser) {
        return 'max-w-full break-words rounded-2xl rounded-tr-sm bg-gold-500 px-3.5 py-2.5 text-sm type-body leading-6 text-zinc-950 shadow-sm';
    }

    return 'max-w-full whitespace-pre-line break-words rounded-2xl rounded-tl-sm border border-zinc-200 bg-zinc-50 px-3.5 py-2.5 text-sm leading-6 text-zinc-700 dark:border-transparent dark:bg-zinc-800 dark:text-zinc-200';
}

function syncSendState(input, send, typing = false) {
    send.disabled = typing || !input.value.trim();
}

function setQuickRepliesDisabled(container, disabled) {
    container?.querySelectorAll('[data-chatbot-quick-reply]').forEach((button) => {
        button.disabled = disabled;
        button.setAttribute('aria-disabled', disabled.toString());
    });
}

function scrollToEnd(target) {
    target?.scrollIntoView({ block: 'end' });
}

function trapFocus(event, panel) {
    const focusable = Array.from(
        panel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'),
    ).filter((element) => element.offsetParent !== null);

    if (!focusable.length) {
        event.preventDefault();
        panel.focus({ preventScroll: true });

        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus({ preventScroll: true });
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus({ preventScroll: true });
    }
}

function normalizeBotReply(reply) {
    if (typeof reply === 'object' && reply !== null) {
        return {
            text: reply.text ?? '',
            actionLabel: reply.actionLabel ?? null,
            actionUrl: reply.actionUrl ?? null,
            clientMessageId: reply.clientMessageId ?? null,
        };
    }

    return {
        text: reply ?? '',
        actionLabel: null,
        actionUrl: null,
        clientMessageId: null,
    };
}

export function createClientMessageId() {
    if (globalThis.crypto?.randomUUID) {
        return globalThis.crypto.randomUUID();
    }

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
        const value = Math.floor(Math.random() * 16);
        const digit = character === 'x' ? value : (value & 0x3) | 0x8;

        return digit.toString(16);
    });
}

export function gymmiStorageKey(config = {}) {
    if (config.memoryEnabled === false || !config.storageNamespace) {
        return null;
    }

    return `platinum-gym:gymmi:${config.storageScope ?? 'public'}:${config.storageNamespace}`;
}

export function readGymmiState(storageKey) {
    if (!storageKey || typeof sessionStorage === 'undefined') {
        return null;
    }

    try {
        const state = JSON.parse(sessionStorage.getItem(storageKey) ?? 'null');

        return state && typeof state === 'object' ? state : null;
    } catch (_error) {
        sessionStorage.removeItem(storageKey);

        return null;
    }
}

export function writeGymmiState(storageKey, conversationId, messages) {
    if (!storageKey || typeof sessionStorage === 'undefined') {
        return;
    }

    try {
        sessionStorage.setItem(storageKey, JSON.stringify({
            conversationId: conversationId ?? null,
            messages: Array.isArray(messages) ? messages.slice(-12) : [],
        }));
    } catch (_error) {
        // Storage can be unavailable in private browsing; chat remains usable.
    }
}

function collectDisplayMessages(container) {
    return Array.from(container.querySelectorAll('[data-chatbot-message]'))
        .filter((item) => item.dataset.chatbotTransient !== 'true')
        .slice(-12)
        .map((item) => ({
            from: item.dataset.chatbotFrom === 'user' ? 'user' : 'bot',
            text: item.dataset.chatbotText ?? '',
            quickReply: item.dataset.chatbotQuickReply === 'true',
            clientMessageId: item.dataset.chatbotClientMessageId ?? null,
            actionLabel: item.dataset.chatbotActionLabel ?? null,
            actionUrl: item.dataset.chatbotActionUrl ?? null,
        }))
        .filter((item) => item.text.length > 0);
}
