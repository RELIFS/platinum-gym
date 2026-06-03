@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-zinc-300 bg-white text-zinc-950 shadow-sm transition placeholder:text-zinc-400 focus:border-gold-500 focus:ring-gold-500/30 disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/10 dark:bg-zinc-950 dark:text-white dark:placeholder:text-zinc-500']) }}>
