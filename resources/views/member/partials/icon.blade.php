@props([
    'name' => 'circle',
    'class' => 'h-5 w-5',
])

@switch($name)
    @case('dashboard')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h4A1.5 1.5 0 0 1 11 5.5v4A1.5 1.5 0 0 1 9.5 11h-4A1.5 1.5 0 0 1 4 9.5v-4ZM13 5.5A1.5 1.5 0 0 1 14.5 4h4A1.5 1.5 0 0 1 20 5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4A1.5 1.5 0 0 1 13 9.5v-4ZM4 14.5A1.5 1.5 0 0 1 5.5 13h4a1.5 1.5 0 0 1 1.5 1.5v4A1.5 1.5 0 0 1 9.5 20h-4A1.5 1.5 0 0 1 4 18.5v-4ZM13 14.5a1.5 1.5 0 0 1 1.5-1.5h4a1.5 1.5 0 0 1 1.5 1.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a1.5 1.5 0 0 1-1.5-1.5v-4Z" stroke="currentColor" stroke-width="1.8" />
        </svg>
        @break

    @case('user')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 12.25a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4.75 20c.95-3.15 3.45-5 7.25-5s6.3 1.85 7.25 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        @break

    @case('card')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4.5 7.5A2.5 2.5 0 0 1 7 5h10a2.5 2.5 0 0 1 2.5 2.5v9A2.5 2.5 0 0 1 17 19H7a2.5 2.5 0 0 1-2.5-2.5v-9ZM5 9h14M8 14h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        @break

    @case('calendar')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M7.5 4v3M16.5 4v3M5 9h14M6.5 6h11A2.5 2.5 0 0 1 20 8.5v9A2.5 2.5 0 0 1 17.5 20h-11A2.5 2.5 0 0 1 4 17.5v-9A2.5 2.5 0 0 1 6.5 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        @break

    @case('receipt')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 4.75h12v14.5l-2-1.25-2 1.25-2-1.25-2 1.25-2-1.25-2 1.25V4.75ZM9 9h6M9 12h6M9 15h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        @break

    @case('qr')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 5h5v5H5V5ZM14 5h5v5h-5V5ZM5 14h5v5H5v-5ZM14 14h2v2h-2v-2ZM17 14h2v5h-5v-2h3v-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
        </svg>
        @break

    @case('bell')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6.5 10.5a5.5 5.5 0 0 1 11 0v3.25l1.75 3H4.75l1.75-3V10.5ZM10 19.25a2.1 2.1 0 0 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        @break

    @case('spark')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 3.75 13.9 9 19.25 11 13.9 13 12 18.25 10.1 13 4.75 11 10.1 9 12 3.75ZM18 15.5l.8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8.8-2.2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
        </svg>
        @break

    @case('arrow')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M4 10h11M11 5l5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        @break

    @case('empty')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 7.5h12M6 12h12M6 16.5h7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
        </svg>
        @break

    @case('history')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4.5 12a7.5 7.5 0 1 0 2.25-5.36" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
            <path d="M4.5 4.5V8H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M12 8v4.5l3 1.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        @break

    @default
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z" stroke="currentColor" stroke-width="1.8" />
        </svg>
@endswitch
