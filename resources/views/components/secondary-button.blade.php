<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex min-h-11 items-center justify-center rounded-lg border border-zinc-300 bg-white px-5 py-2.5 text-sm font-bold text-zinc-800 shadow-sm transition hover:border-gold-500/60 hover:text-gold-600 focus:outline-none focus:ring-2 focus:ring-gold-500/40 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-200 dark:hover:text-gold-400 dark:focus:ring-offset-zinc-950']) }}>
    {{ $slot }}
</button>
