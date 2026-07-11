@props([
    'action',
    'method' => 'POST',
    'message' => '',
    'title' => 'Konfirmasi Tindakan',
    'confirmLabel' => 'Lanjutkan',
    'cancelLabel' => 'Batal',
    'variant' => 'primary',
])

@php
    $methodUpper = strtoupper($method);
    $formMethod = $methodUpper === 'GET' ? 'GET' : 'POST';
    $needsSpoof = ! in_array($methodUpper, ['GET', 'POST'], true);
    $hasMessage = filled($message);
    $confirmClass = match ($variant) {
        'danger' => 'inline-flex min-h-11 items-center justify-center rounded-lg bg-red-600 px-5 text-sm type-control text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/40',
        default => 'inline-flex min-h-11 items-center justify-center rounded-lg bg-gold-500 px-5 text-sm type-emphasis text-zinc-950 shadow-sm transition hover:bg-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:focus-visible:ring-gold-400/40',
    };
    $cancelClass = 'inline-flex min-h-11 items-center justify-center rounded-lg border border-zinc-200 bg-white px-5 text-sm type-control text-zinc-700 shadow-sm transition hover:bg-zinc-50 hover:text-gold-text focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 dark:border-white/10 dark:bg-zinc-950 dark:text-zinc-200 dark:hover:bg-zinc-900 dark:focus-visible:ring-gold-400/40';
    $dialogId = 'confirm-form-' . uniqid();
@endphp

<div x-data="{ open: false, submitting: false }" class="contents">
    <form
        method="{{ $formMethod }}"
        action="{{ $action }}"
        @if ($hasMessage) x-on:submit.prevent="if (submitting) return; if (! $event.target.checkValidity()) { $event.target.reportValidity(); return; } open = true" @else x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }" @endif
        {{ $attributes }}
    >
        @if ($formMethod === 'POST')
            @csrf
        @endif
        @if ($needsSpoof)
            @method($methodUpper)
        @endif
        {{ $slot }}
    </form>

    @if ($hasMessage)
        <template x-teleport="body">
            <div
                x-show="open"
                x-cloak
                class="fixed inset-0 z-[100] flex items-center justify-center px-4 py-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="{{ $dialogId }}-title"
                aria-describedby="{{ $dialogId }}-desc"
                x-on:keydown.escape.window="if (! submitting) open = false"
                x-trap.noscroll="open"
                x-transition.opacity
            >
                <div class="absolute inset-0 bg-zinc-950/70 backdrop-blur-sm" x-on:click="if (! submitting) open = false" aria-hidden="true"></div>

                <div
                    class="relative w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-zinc-950"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                >
                    <h2 id="{{ $dialogId }}-title" class="text-lg type-title tracking-tight text-zinc-950 dark:text-zinc-100">{{ $title }}</h2>
                    <p id="{{ $dialogId }}-desc" class="mt-3 text-sm type-compact leading-6 text-zinc-600 dark:text-zinc-300">{{ $message }}</p>

                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button" x-on:click="open = false" x-bind:disabled="submitting" class="{{ $cancelClass }} disabled:cursor-not-allowed disabled:opacity-60">{{ $cancelLabel }}</button>
                        <button
                            type="button"
                            x-bind:disabled="submitting"
                            x-on:click="if (submitting) return; submitting = true; open = false; $root.querySelector('form').submit()"
                            class="{{ $confirmClass }} disabled:cursor-not-allowed disabled:opacity-60"
                        ><span x-show="! submitting">{{ $confirmLabel }}</span><span x-show="submitting">Memproses...</span></button>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
