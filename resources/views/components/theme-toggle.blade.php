<button
    type="button"
    data-theme-toggle
    aria-label="Aktifkan mode gelap"
    title="Aktifkan mode gelap"
    {{ $attributes->class(['theme-toggle']) }}
>
    <svg class="h-5 w-5 dark:hidden" viewBox="0 0 20 20" fill="none" aria-hidden="true">
        <path d="M16.5 12.3C15.47 12.82 14.3 13.12 13.06 13.12C8.8 13.12 5.35 9.68 5.35 5.43C5.35 4.36 5.57 3.34 5.97 2.41C3.42 3.61 1.65 6.2 1.65 9.2C1.65 13.34 5.01 16.7 9.15 16.7C12.41 16.7 15.19 14.62 16.23 11.71L16.5 12.3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
    </svg>
    <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 20 20" fill="none" aria-hidden="true">
        <path d="M10 2.5V1M10 19V17.5M17.5 10H19M1 10H2.5M15.3 4.7L16.36 3.64M3.64 16.36L4.7 15.3M15.3 15.3L16.36 16.36M3.64 3.64L4.7 4.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
        <circle cx="10" cy="10" r="3.75" stroke="currentColor" stroke-width="1.7" />
    </svg>
</button>
