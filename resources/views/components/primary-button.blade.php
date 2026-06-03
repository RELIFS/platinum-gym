<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-11 items-center justify-center rounded-lg bg-gold-500 px-5 py-2.5 text-sm font-black text-zinc-950 transition hover:bg-gold-400 focus:outline-none focus:ring-2 focus:ring-gold-500/50 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-zinc-950']) }}>
    {{ $slot }}
</button>
