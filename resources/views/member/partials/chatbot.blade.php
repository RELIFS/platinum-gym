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
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gold-500 text-zinc-950" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M4 5.5C4 4.12 5.12 3 6.5 3H13.5C14.88 3 16 4.12 16 5.5V10.5C16 11.88 14.88 13 13.5 13H9L5.5 16V13H6.5C5.12 13 4 11.88 4 10.5V5.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                        </svg>
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

        <button type="button" data-chatbot-trigger class="group inline-flex h-12 w-12 touch-manipulation items-center justify-center gap-3 rounded-full bg-gold-500 p-0 text-zinc-950 shadow-[0_18px_50px_rgba(254,172,24,0.34)] ring-1 ring-zinc-950/10 transition hover:bg-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-50 dark:focus-visible:ring-offset-zinc-950 sm:h-auto sm:min-h-14 sm:w-auto sm:px-4 sm:py-3" aria-label="Buka Gymmi member">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M4 5.5C4 4.12 5.12 3 6.5 3H13.5C14.88 3 16 4.12 16 5.5V10.5C16 11.88 14.88 13 13.5 13H9L5.5 16V13H6.5C5.12 13 4 11.88 4 10.5V5.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
            </svg>
            <span class="hidden text-sm font-black leading-tight sm:block">Tanya Gymmi</span>
        </button>
    </div>
</div>
