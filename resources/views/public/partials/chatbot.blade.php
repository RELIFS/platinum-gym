@php($chatbotConfig = $chatbotConfig ?? \App\Features\PublicWebsite\ViewModels\PublicChatbotViewModel::make($settings ?? []))

<script>
    window.platinumGymChatbotConfig = @js($chatbotConfig);
</script>

<div x-data="platinumGymChatbot(window.platinumGymChatbotConfig)" x-on:keydown.escape.window="close()">
    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-zinc-950/60 backdrop-blur-sm sm:hidden" aria-hidden="true" x-on:click="close()"></div>

    <div x-bind:class="open ? 'fixed inset-x-3 bottom-3 z-50 sm:inset-auto sm:bottom-6 sm:right-6' : 'fixed bottom-4 right-4 z-50 sm:bottom-6 sm:right-6'" style="padding-bottom: env(safe-area-inset-bottom);">
        <section x-cloak x-show="open" x-transition x-ref="panel" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="chatbot-title" class="mx-auto flex h-[min(620px,calc(100dvh-1.5rem))] w-full flex-col overflow-hidden overscroll-contain rounded-2xl border border-zinc-800 bg-zinc-950 shadow-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/50 sm:h-[min(560px,calc(100dvh-6rem))] sm:w-96">
            <header class="flex shrink-0 items-center justify-between gap-3 bg-gold-500 px-4 py-3 text-zinc-950">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-zinc-950/10">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M4 5.5C4 4.12 5.12 3 6.5 3H13.5C14.88 3 16 4.12 16 5.5V10.5C16 11.88 14.88 13 13.5 13H9L5.5 16V13H6.5C5.12 13 4 11.88 4 10.5V5.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h2 id="chatbot-title" class="truncate text-sm font-black">Chatbot Platinum Gym</h2>
                        <p class="flex items-center gap-1.5 text-xs font-semibold text-zinc-950/75">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-700"></span>
                            Online, balas cepat
                        </p>
                    </div>
                </div>
                <button type="button" class="inline-flex h-11 w-11 shrink-0 touch-manipulation items-center justify-center rounded-full text-zinc-950 transition hover:bg-zinc-950/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-950/30" aria-label="Tutup chatbot" x-on:click="close()">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </button>
            </header>

            <div class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain px-4 py-4" aria-live="polite" aria-relevant="additions text">
                <template x-for="(message, index) in messages" x-bind:key="index">
                    <div class="flex min-w-0 gap-2" x-bind:class="message.from === 'user' ? 'flex-row-reverse' : ''">
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-black" x-bind:class="message.from === 'user' ? 'bg-gold-500 text-zinc-950' : 'bg-zinc-800 text-zinc-200'" x-text="message.from === 'user' ? 'AN' : 'PG'"></span>
                        <p class="min-w-0 max-w-[80%] break-words rounded-2xl px-3.5 py-2.5 text-sm leading-6" x-bind:class="message.from === 'user' ? 'rounded-tr-none bg-gold-500 text-zinc-950 font-semibold' : 'rounded-tl-none bg-zinc-800 text-zinc-200'" x-text="message.text"></p>
                    </div>
                </template>

                <div x-show="typing" class="flex gap-2">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-800 text-xs font-black text-zinc-200">PG</span>
                    <div class="flex items-center gap-1 rounded-2xl rounded-tl-none bg-zinc-800 px-4 py-3" aria-label="Chatbot sedang mengetik">
                        <span class="h-2 w-2 animate-bounce rounded-full bg-zinc-400"></span>
                        <span class="h-2 w-2 animate-bounce rounded-full bg-zinc-400 [animation-delay:150ms]"></span>
                        <span class="h-2 w-2 animate-bounce rounded-full bg-zinc-400 [animation-delay:300ms]"></span>
                    </div>
                </div>

                <div x-ref="messagesEnd"></div>
            </div>

            <div class="shrink-0 border-t border-zinc-800 px-3 py-3">
                <div class="mb-3 flex flex-wrap gap-2 pb-1">
                    <template x-for="reply in quickReplies" x-bind:key="reply">
                        <button type="button" class="inline-flex min-h-11 min-w-0 max-w-full touch-manipulation items-center whitespace-normal break-words rounded-full border border-zinc-700 bg-zinc-900 px-3 py-2 text-left text-xs font-bold leading-4 text-zinc-300 transition hover:border-gold-500/60 hover:text-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/30" x-on:click="quickReply(reply)" x-text="reply"></button>
                    </template>
                </div>
                <div class="flex gap-2">
                    <input type="text" class="public-input h-11 min-w-0 flex-1 border-zinc-700 bg-zinc-900 text-base text-zinc-100 placeholder:text-zinc-500" placeholder="Ketik pertanyaan..." aria-label="Ketik pertanyaan chatbot" x-model="input" x-on:keydown.enter.prevent="send()">
                    <button type="button" class="inline-flex h-11 w-11 shrink-0 touch-manipulation items-center justify-center rounded-full bg-gold-500 text-zinc-950 transition hover:bg-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/60 disabled:cursor-not-allowed disabled:opacity-50" aria-label="Kirim pesan chatbot" x-bind:disabled="!input.trim()" x-on:click="send()">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M3 10L17 3L12 17L9.5 10.5L3 10Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
                <a href="{{ $chatbotConfig['whatsappUrl'] }}" x-bind:href="whatsappUrl" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex min-h-11 w-full touch-manipulation items-center justify-center break-words rounded-full border border-zinc-700 px-4 py-2 text-center text-xs font-bold text-zinc-300 transition hover:border-gold-500/60 hover:text-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/30">
                    Butuh admin? Lanjut via WhatsApp
                </a>
            </div>
        </section>

        <button type="button" x-show="!open" x-transition x-ref="trigger" class="group inline-flex h-12 w-12 touch-manipulation items-center justify-center gap-3 rounded-full bg-gold-500 p-0 text-zinc-950 shadow-[0_18px_50px_rgba(254,172,24,0.34)] ring-1 ring-zinc-950/10 transition hover:bg-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-50 dark:focus-visible:ring-offset-zinc-950 sm:h-auto sm:min-h-14 sm:w-auto sm:px-4 sm:py-3" aria-label="Buka chatbot Platinum Gym" x-on:click="openChat()">
            <span class="relative inline-flex h-8 w-8 items-center justify-center rounded-full bg-zinc-950/10">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M4 5.5C4 4.12 5.12 3 6.5 3H13.5C14.88 3 16 4.12 16 5.5V10.5C16 11.88 14.88 13 13.5 13H9L5.5 16V13H6.5C5.12 13 4 11.88 4 10.5V5.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                </svg>
                <span class="absolute -right-1 -top-1 h-3.5 min-w-3.5 rounded-full bg-red-600 px-1 text-[0.55rem] font-black leading-3.5 text-white">1</span>
            </span>
            <span class="hidden leading-tight 2xl:block">
                <span class="block text-[0.65rem] font-black uppercase tracking-[0.18em]">Chatbot</span>
                <span class="block text-sm font-black">Tanya Cepat</span>
            </span>
        </button>
    </div>
</div>
