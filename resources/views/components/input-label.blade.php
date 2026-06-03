@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-bold text-zinc-700 dark:text-zinc-300']) }}>
    {{ $value ?? $slot }}
</label>
