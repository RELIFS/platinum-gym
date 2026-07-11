@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-zinc-300 bg-white text-zinc-950 shadow-sm transition placeholder:text-zinc-500 focus:border-gold-600 focus:ring-gold-700/30 disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/10 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-400 dark:focus:border-gold-400 dark:focus:ring-gold-400/30']) }}>
