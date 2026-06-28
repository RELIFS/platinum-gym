@php($memberChatbotConfig = $memberChatbotConfig ?? \App\Features\MemberPortal\ViewModels\MemberChatbotViewModel::make($portal ?? []))

<script>
    window.platinumGymMemberChatbotConfig = @js($memberChatbotConfig);
</script>

<div data-chatbot-root data-chatbot-config="platinumGymMemberChatbotConfig" data-chatbot-variant="member">
    <div data-chatbot-overlay hidden class="fixed inset-0 z-[35] bg-zinc-900/25 backdrop-blur-sm dark:bg-zinc-950/60 sm:hidden" aria-hidden="true"></div>

    <div class="fixed inset-x-3 bottom-3 z-[45] flex flex-col items-end sm:bottom-6 sm:left-auto sm:right-6 sm:w-fit sm:max-w-[calc(100vw-3rem)]" style="padding-bottom: env(safe-area-inset-bottom);">
        <section data-chatbot-panel hidden tabindex="-1" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="member-chatbot-title" class="mx-auto h-[min(620px,calc(100dvh-1.5rem))] w-full flex-col overflow-hidden overscroll-contain rounded-2xl border border-zinc-200 bg-white shadow-[0_18px_60px_rgba(24,24,27,0.14)] focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 dark:border-zinc-800 dark:bg-zinc-950 dark:shadow-2xl sm:h-[min(560px,calc(100dvh-6rem))] sm:w-[25rem]">
            <header class="flex shrink-0 items-center justify-between gap-3 border-b border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-950 dark:border-zinc-800 dark:bg-white/[0.04] dark:text-white">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="gymmi-panel-avatar" data-gymmi-panel-avatar aria-hidden="true">
                        <img
                            src="{{ asset('images/gymmi/avatar-gymmi-light-96.webp') }}"
                            srcset="{{ asset('images/gymmi/avatar-gymmi-light-96.webp') }} 96w, {{ asset('images/gymmi/avatar-gymmi-light-192.webp') }} 192w"
                            sizes="40px"
                            width="40"
                            height="40"
                            alt=""
                            decoding="async"
                            loading="eager"
                            class="gymmi-panel-avatar-image block dark:hidden"
                            data-gymmi-panel-avatar-image
                        >
                        <img
                            src="{{ asset('images/gymmi/avatar-gymmi-dark-96.webp') }}"
                            srcset="{{ asset('images/gymmi/avatar-gymmi-dark-96.webp') }} 96w, {{ asset('images/gymmi/avatar-gymmi-dark-192.webp') }} 192w"
                            sizes="40px"
                            width="40"
                            height="40"
                            alt=""
                            decoding="async"
                            loading="eager"
                            class="gymmi-panel-avatar-image hidden dark:block"
                            data-gymmi-panel-avatar-image
                        >
                        <span class="gymmi-panel-avatar-fallback" hidden>GY</span>
                    </span>
                    <div class="min-w-0">
                        <h2 id="member-chatbot-title" class="truncate text-sm font-black">Gymmi</h2>
                        <p class="flex items-center gap-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-700" aria-hidden="true"></span>
                            Siap bantu member
                        </p>
                    </div>
                </div>
                <button type="button" data-chatbot-close class="inline-flex h-11 w-11 shrink-0 touch-manipulation items-center justify-center rounded-full text-zinc-700 transition hover:bg-zinc-200/80 hover:text-zinc-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:text-zinc-300 dark:hover:bg-white/[0.08] dark:hover:text-white" aria-label="Tutup Gymmi">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </button>
            </header>

            <div data-chatbot-messages class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain px-4 py-4" role="log" aria-live="polite" aria-relevant="additions text" aria-label="Percakapan Gymmi"></div>

            <div data-chatbot-messages-end></div>

            <div class="shrink-0 border-t border-zinc-200 px-3 py-3 dark:border-zinc-800">
                <div data-chatbot-quick-replies class="gymmi-quick-reply-rail mb-3" aria-label="Pertanyaan cepat Gymmi" tabindex="0"></div>
                <div class="flex gap-2">
                    <input type="text" name="gymmi_member_message" data-chatbot-input class="member-form-input h-11 min-w-0 flex-1 text-base" placeholder="Ketik pertanyaan..." aria-label="Ketik pertanyaan untuk Gymmi" autocomplete="off" spellcheck="true">
                    <button type="button" data-chatbot-send class="inline-flex h-11 w-11 shrink-0 touch-manipulation items-center justify-center rounded-full bg-gold-500 text-zinc-950 transition hover:bg-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/60 disabled:cursor-not-allowed disabled:opacity-50" aria-label="Kirim pesan Gymmi" disabled>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M3 10L17 3L12 17L9.5 10.5L3 10Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>
        </section>

        <button type="button" data-chatbot-trigger class="gymmi-trigger" aria-label="Buka Gymmi member">
            <img
                src="{{ asset('images/gymmi/gymmi-chat-trigger-160.webp') }}"
                srcset="{{ asset('images/gymmi/gymmi-chat-trigger-160.webp') }} 160w, {{ asset('images/gymmi/gymmi-chat-trigger-320.webp') }} 320w, {{ asset('images/gymmi/gymmi-chat-trigger-480.webp') }} 480w"
                sizes="(min-width: 768px) 156px, (min-width: 480px) 136px, 112px"
                width="160"
                height="160"
                alt=""
                decoding="async"
                class="gymmi-trigger-image"
                data-gymmi-trigger-image
            >
            <span class="gymmi-trigger-fallback" hidden aria-hidden="true">GY</span>
        </button>
    </div>
</div>
