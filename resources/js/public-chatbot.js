export function platinumGymChatbot(config = {}) {
    return {
        open: false,
        input: '',
        typing: false,
        messages: [{ from: 'bot', text: config.initialMessage ?? '' }],
        quickReplies: config.quickReplies ?? [],
        whatsappUrl: config.whatsappUrl ?? '#',
        close() {
            const shouldReturnFocus = this.$root?.contains(document.activeElement);

            this.open = false;

            if (shouldReturnFocus) {
                this.$nextTick(() => this.$refs.trigger?.focus({ preventScroll: true }));
            }
        },
        openChat() {
            this.open = true;

            this.$nextTick(() => {
                this.$refs.panel?.focus({ preventScroll: true });
                this.scrollToEnd();
            });
        },
        send() {
            const text = this.input.trim();

            if (!text) {
                return;
            }

            this.messages.push({ from: 'user', text });
            this.input = '';
            this.queueReply(text);
        },
        quickReply(text) {
            this.messages.push({ from: 'user', text });
            this.queueReply(text);
        },
        queueReply(text) {
            this.typing = true;
            this.$nextTick(() => this.scrollToEnd());

            window.setTimeout(() => {
                this.messages.push({ from: 'bot', text: this.resolveReply(text) });
                this.typing = false;
                this.$nextTick(() => this.scrollToEnd());
            }, 500);
        },
        resolveReply(text) {
            const normalized = text.toLowerCase();
            const replies = config.replies ?? {};

            if (text === 'Info Membership' || normalized.includes('member') || normalized.includes('paket') || normalized.includes('gym umum')) {
                return replies.membership;
            }

            if (text === 'Jadwal Kelas' || normalized.includes('jadwal') || normalized.includes('kelas') || normalized.includes('zumba') || normalized.includes('aerobic') || normalized.includes('muaythai') || normalized.includes('pound')) {
                return replies.schedule;
            }

            if (text === 'Harga Personal Trainer' || normalized.includes('personal trainer') || normalized.includes('pelatih') || normalized.includes('coach') || normalized.includes(' pt')) {
                return replies.trainer;
            }

            if (text === 'Lokasi & Jam Buka' || normalized.includes('lokasi') || normalized.includes('alamat') || normalized.includes('jam') || normalized.includes('buka')) {
                return replies.location;
            }

            if (normalized.includes('promo') || normalized.includes('diskon')) {
                return replies.promo;
            }

            return replies.fallback;
        },
        scrollToEnd() {
            this.$refs.messagesEnd?.scrollIntoView({ block: 'end' });
        },
    };
}
