export function platinumGymChatbot(config = {}) {
    return {
        open: false,
        input: '',
        typing: false,
        lastFocusedElement: null,
        messages: [{ from: 'bot', ...normalizeBotReply(config.initialMessage ?? '') }],
        quickReplies: config.quickReplies ?? [],
        whatsappUrl: config.whatsappUrl ?? '#',
        showEscalation: config.showEscalation ?? true,
        focusablePanelElements() {
            if (!this.$refs.panel) {
                return [];
            }

            return Array.from(
                this.$refs.panel.querySelectorAll(
                    'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])',
                ),
            ).filter((element) => element.offsetParent !== null);
        },
        trapFocus(event) {
            if (!this.open || !this.$refs.panel) {
                return;
            }

            const focusable = this.focusablePanelElements();

            if (!focusable.length) {
                event.preventDefault();
                this.$refs.panel.focus({ preventScroll: true });

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
        },
        close() {
            const fallbackFocus = this.lastFocusedElement ?? this.$refs.trigger;

            this.open = false;

            this.$nextTick(() => fallbackFocus?.focus?.({ preventScroll: true }));
        },
        handleEscape(event) {
            if (!this.open) {
                return;
            }

            event.preventDefault();
            this.close();
        },
        openChat() {
            this.lastFocusedElement = document.activeElement;
            this.open = true;

            this.$nextTick(() => {
                this.$refs.panel?.focus({ preventScroll: true });
                this.scrollToEnd();
            });
        },
        send() {
            const text = this.input.trim();

            if (!text || this.typing) {
                return;
            }

            this.messages.push({ from: 'user', text, quickReply: false });
            this.input = '';
            this.queueReply(text);
        },
        quickReply(text) {
            if (this.typing) {
                return;
            }

            this.messages.push({ from: 'user', text, quickReply: true });
            this.queueReply(text);
        },
        queueReply(text) {
            this.typing = true;
            this.$nextTick(() => this.scrollToEnd());

            window.setTimeout(() => {
                this.messages.push({ from: 'bot', ...normalizeBotReply(this.resolveReply(text)) });
                this.typing = false;
                this.$nextTick(() => this.scrollToEnd());
            }, 500);
        },
        resolveReply(text) {
            return resolveChatbotReply(text, config);
        },
        scrollToEnd() {
            this.$refs.messagesEnd?.scrollIntoView({ block: 'end' });
        },
    };
}

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
    let open = false;
    let typing = false;
    let lastFocusedElement = null;

    if (!panel || !trigger || !messages || !input || !send) {
        return;
    }

    renderMessage(messages, 'bot', normalizeBotReply(config.initialMessage ?? ''), root.dataset.chatbotVariant, config);
    renderQuickReplies(quickReplies, config.quickReplies ?? [], (reply) => {
        if (typing) {
            return;
        }

        addUserMessage(reply, true);
        queueReply(reply);
    });

    if (escalation) {
        escalation.hidden = config.showEscalation === false;
        escalation.href = config.whatsappUrl ?? '#';
    }

    const setTypingState = (nextTyping) => {
        typing = nextTyping;
        setQuickRepliesDisabled(quickReplies, typing);
        syncSendState(input, send, typing);
    };

    const setOpen = (nextOpen, returnFocus = true) => {
        open = nextOpen;
        panel.hidden = !open;
        panel.style.display = open ? 'flex' : 'none';
        panel.setAttribute('aria-hidden', (!open).toString());
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
                (lastFocusedElement ?? trigger).focus?.({ preventScroll: true });
            }
        });
    };

    const close = () => setOpen(false);

    const submit = () => {
        const text = input.value.trim();

        if (!text || typing) {
            return;
        }

        addUserMessage(text, false);
        input.value = '';
        syncSendState(input, send, typing);
        queueReply(text);
    };

    function addUserMessage(text, quickReply = false) {
        renderMessage(messages, 'user', { text }, root.dataset.chatbotVariant, config, { quickReply });
        scrollToEnd(messagesEnd);
    }

    function queueReply(text) {
        if (typing) {
            return;
        }

        setTypingState(true);
        const typingEl = renderTyping(messages, config);
        scrollToEnd(messagesEnd);

        window.setTimeout(() => {
            typingEl.remove();
            renderMessage(messages, 'bot', normalizeBotReply(resolveChatbotReply(text, config)), root.dataset.chatbotVariant, config);
            setTypingState(false);
            scrollToEnd(messagesEnd);
            input.focus({ preventScroll: true });
        }, 500);
    }

    trigger.addEventListener('click', () => {
        lastFocusedElement = document.activeElement;
        setOpen(true, false);
    });

    overlay?.addEventListener('click', close);
    closeButtons.forEach((button) => button.addEventListener('click', close));
    send.addEventListener('click', submit);
    input.addEventListener('input', () => syncSendState(input, send, typing));
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
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
}

function resolveChatbotReply(text, config = {}) {
    const normalized = text.toLowerCase();
    const replies = config.replies ?? {};

    if (text === 'QR Member' || normalized.includes('qr') || normalized.includes('check-in') || normalized.includes('check in')) {
        return replies.qr ?? replies.fallback;
    }

    if (text === 'Status Membership') {
        return replies.membership;
    }

    if (text === 'Info Membership' || normalized.includes('member') || normalized.includes('paket') || normalized.includes('gym umum')) {
        return replies.membership;
    }

    if (text === 'Jadwal Kelas' || normalized.includes('jadwal') || normalized.includes('kelas') || normalized.includes('zumba') || normalized.includes('aerobic') || normalized.includes('muaythai') || normalized.includes('pound')) {
        return replies.schedule;
    }

    if (text === 'Transaksi' || normalized.includes('transaksi') || normalized.includes('pembayaran') || normalized.includes('invoice')) {
        return replies.transactions ?? replies.fallback;
    }

    if (text === 'Bantuan Akun' || normalized.includes('profil') || normalized.includes('akun') || normalized.includes('password') || normalized.includes('sandi')) {
        return replies.account ?? replies.fallback;
    }

    if (text === 'Harga Personal Trainer' || normalized.includes('personal trainer') || normalized.includes('pelatih') || normalized.includes('coach') || normalized.includes(' pt')) {
        return replies.trainer ?? replies.fallback;
    }

    if (text === 'Lokasi & Jam Buka' || normalized.includes('lokasi') || normalized.includes('alamat') || normalized.includes('jam') || normalized.includes('buka')) {
        return replies.location ?? replies.fallback;
    }

    if (normalized.includes('promo') || normalized.includes('diskon')) {
        return replies.promo ?? replies.fallback;
    }

    return replies.fallback;
}

function renderQuickReplies(container, replies, onClick) {
    if (!container) {
        return;
    }

    container.innerHTML = '';
    replies.forEach((reply) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.chatbotQuickReply = 'true';
        button.className = 'inline-flex min-h-10 min-w-0 max-w-full touch-manipulation items-center whitespace-normal break-words rounded-full border border-zinc-700 bg-zinc-900 px-3 py-2 text-left text-xs font-bold leading-4 text-zinc-300 transition hover:border-gold-500/60 hover:text-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/30 disabled:cursor-not-allowed disabled:opacity-45';
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

    const bubbleWrap = document.createElement('div');
    bubbleWrap.className = isUser
        ? 'flex min-w-0 max-w-[82%] flex-col items-end'
        : `${variant === 'member' ? 'max-w-[84%]' : 'max-w-[82%]'} flex min-w-0 flex-col items-start`;

    const bubble = document.createElement('p');
    bubble.className = messageBubbleClass(isUser, options.quickReply === true);
    bubble.textContent = reply.text ?? '';
    bubbleWrap.append(bubble);

    if (reply.actionUrl && reply.actionLabel) {
        const action = document.createElement('a');
        action.href = reply.actionUrl;
        action.className = 'mt-2 inline-flex min-h-10 max-w-full items-center justify-center whitespace-normal break-words rounded-lg border border-gold-500/40 px-3 py-2 text-center text-xs font-black leading-5 text-gold-400 transition hover:border-gold-500 hover:text-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40';
        action.textContent = reply.actionLabel;
        bubbleWrap.append(action);
    }

    if (isUser) {
        item.append(bubbleWrap);
    } else {
        const avatar = document.createElement('span');
        avatar.className = 'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-800 text-xs font-black text-zinc-200';
        avatar.textContent = config.botInitials ?? 'GY';
        avatar.setAttribute('aria-hidden', 'true');
        item.append(avatar, bubbleWrap);
    }

    container.append(item);
}

function renderTyping(container, config = {}) {
    const item = document.createElement('div');
    item.className = 'flex min-w-0 items-start gap-2';
    item.setAttribute('aria-label', config.typingLabel ?? 'Gymmi sedang mengetik');

    const avatar = document.createElement('span');
    avatar.className = 'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-800 text-xs font-black text-zinc-200';
    avatar.textContent = config.botInitials ?? 'GY';
    avatar.setAttribute('aria-hidden', 'true');

    const bubble = document.createElement('div');
    bubble.className = 'flex items-center gap-1 rounded-2xl rounded-tl-sm bg-zinc-800 px-4 py-3';
    bubble.setAttribute('role', 'status');
    bubble.setAttribute('aria-label', config.typingLabel ?? 'Gymmi sedang mengetik');

    [0, 150, 300].forEach((delay) => {
        const dot = document.createElement('span');
        dot.className = 'h-2 w-2 animate-bounce rounded-full bg-zinc-400';
        dot.style.animationDelay = `${delay}ms`;
        dot.setAttribute('aria-hidden', 'true');
        bubble.append(dot);
    });

    item.append(avatar, bubble);
    container.append(item);

    return item;
}

function messageBubbleClass(isUser, quickReply) {
    if (quickReply) {
        return 'max-w-full break-words rounded-full bg-gold-500 px-3 py-2 text-xs font-black leading-5 text-zinc-950 shadow-sm';
    }

    if (isUser) {
        return 'max-w-full break-words rounded-2xl rounded-tr-sm bg-gold-500 px-3.5 py-2.5 text-sm font-semibold leading-6 text-zinc-950 shadow-sm';
    }

    return 'max-w-full break-words rounded-2xl rounded-tl-sm bg-zinc-800 px-3.5 py-2.5 text-sm leading-6 text-zinc-200';
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
        };
    }

    return {
        text: reply ?? '',
        actionLabel: null,
        actionUrl: null,
    };
}