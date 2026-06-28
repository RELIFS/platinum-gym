@props([
    'name' => 'circle',
    'class' => 'h-5 w-5',
])

@switch($name)
    @case('dashboard')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h4A1.5 1.5 0 0 1 11 5.5v4A1.5 1.5 0 0 1 9.5 11h-4A1.5 1.5 0 0 1 4 9.5v-4ZM13 5.5A1.5 1.5 0 0 1 14.5 4h4A1.5 1.5 0 0 1 20 5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4A1.5 1.5 0 0 1 13 9.5v-4ZM4 14.5A1.5 1.5 0 0 1 5.5 13h4a1.5 1.5 0 0 1 1.5 1.5v4A1.5 1.5 0 0 1 9.5 20h-4A1.5 1.5 0 0 1 4 18.5v-4ZM13 14.5a1.5 1.5 0 0 1 1.5-1.5h4a1.5 1.5 0 0 1 1.5 1.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a1.5 1.5 0 0 1-1.5-1.5v-4Z" stroke="currentColor" stroke-width="1.8" /></svg>
        @break

    @case('members')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9.75 11.5a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5ZM3.75 19.25c.85-3.15 2.85-4.75 6-4.75s5.15 1.6 6 4.75M16.25 11.5a2.7 2.7 0 1 0 0-5.4M16.75 14.75c1.85.35 3.1 1.75 3.5 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('user')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12.25a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4.75 20c.95-3.15 3.45-5 7.25-5s6.3 1.85 7.25 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('card')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.5 7.5A2.5 2.5 0 0 1 7 5h10a2.5 2.5 0 0 1 2.5 2.5v9A2.5 2.5 0 0 1 17 19H7a2.5 2.5 0 0 1-2.5-2.5v-9ZM5 9h14M8 14h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('membership-card')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.5 7A2.5 2.5 0 0 1 7 4.5h10A2.5 2.5 0 0 1 19.5 7v10A2.5 2.5 0 0 1 17 19.5H7A2.5 2.5 0 0 1 4.5 17V7ZM8 8h8M8 15.5h3.5M13.5 15.5H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M10.25 13a1.75 1.75 0 1 0 0-3.5 1.75 1.75 0 0 0 0 3.5Z" stroke="currentColor" stroke-width="1.8" /></svg>
        @break

    @case('calendar')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7.5 4v3M16.5 4v3M5 9h14M6.5 6h11A2.5 2.5 0 0 1 20 8.5v9A2.5 2.5 0 0 1 17.5 20h-11A2.5 2.5 0 0 1 4 17.5v-9A2.5 2.5 0 0 1 6.5 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('calendar-check')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7.5 4v3M16.5 4v3M5 9h14M6.5 6h11A2.5 2.5 0 0 1 20 8.5v9A2.5 2.5 0 0 1 17.5 20h-11A2.5 2.5 0 0 1 4 17.5v-9A2.5 2.5 0 0 1 6.5 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="m8.75 14.5 2 2 4.5-4.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('receipt')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 4.75h12v14.5l-2-1.25-2 1.25-2-1.25-2 1.25-2-1.25-2 1.25V4.75ZM9 9h6M9 12h6M9 15h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('qr')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 5h5v5H5V5ZM14 5h5v5h-5V5ZM5 14h5v5H5v-5ZM14 14h2v2h-2v-2ZM17 14h2v5h-5v-2h3v-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg>
        @break

    @case('qr-scan')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.5 8V5.75A1.25 1.25 0 0 1 5.75 4.5H8M16 4.5h2.25a1.25 1.25 0 0 1 1.25 1.25V8M19.5 16v2.25a1.25 1.25 0 0 1-1.25 1.25H16M8 19.5H5.75a1.25 1.25 0 0 1-1.25-1.25V16M7.5 7.5h3v3h-3v-3ZM13.5 7.5h3v3h-3v-3ZM7.5 13.5h3v3h-3v-3ZM13.5 13.5h1.5v1.5h-1.5v-1.5ZM16.5 13.5v3h-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M4 12h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break

    @case('bell')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.5 10.5a5.5 5.5 0 0 1 11 0v3.25l1.75 3H4.75l1.75-3V10.5ZM10 19.25a2.1 2.1 0 0 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('activity')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 13h3l2-6 4 11 2.5-7H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('box')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 4 7 3.75v8.5L12 20l-7-3.75v-8.5L12 4ZM5.5 8 12 11.5 18.5 8M12 11.5V20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('image')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5.5 5h13A1.5 1.5 0 0 1 20 6.5v11a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 17.5v-11A1.5 1.5 0 0 1 5.5 5ZM7 16l3.25-3.25L13 15.5l2-2L18 16M8.5 9.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('message')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 5.5A2.5 2.5 0 0 1 7.5 3h9A2.5 2.5 0 0 1 19 5.5v7A2.5 2.5 0 0 1 16.5 15H11l-4.5 4v-4A2.5 2.5 0 0 1 4 12.5v-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg>
        @break

    @case('quote-message')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 5.5A2.5 2.5 0 0 1 7.5 3h9A2.5 2.5 0 0 1 19 5.5v7A2.5 2.5 0 0 1 16.5 15H11l-4.5 4v-4A2.5 2.5 0 0 1 4 12.5v-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M8.5 8.25h2.25v2.25H9.5c0 1.1.48 1.75 1.45 2M13.25 8.25h2.25v2.25h-1.25c0 1.1.48 1.75 1.45 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('tag')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.75 12.25 12.25 4.75H19.25V11.75L11.75 19.25 4.75 12.25ZM16 8h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('trainer')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 10V7a3 3 0 0 1 6 0v3M12 10V7a3 3 0 0 1 6 0v3M5 10h14v4.5A5.5 5.5 0 0 1 13.5 20h-3A5.5 5.5 0 0 1 5 14.5V10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('coach')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 11.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5ZM4.75 20c.95-3.4 3.4-5.25 7.25-5.25s6.3 1.85 7.25 5.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M16.5 5.75h2.25M17.75 4.5V7M8.5 14.9l1.4 2.1 2.1-2.25 2.1 2.25 1.4-2.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('dumbbell')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6.5 8.5 9 9M4.75 10.25l5.5-5.5M13.75 19.25l5.5-5.5M3.5 8.25l2.25-2.25M18.25 18l2.25-2.25M8.25 3.5 6 5.75M15.75 20.5 18 18.25M9.5 12.5l3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('chart')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 19V5M5 19h14M9 16v-5M13 16V8M17 16v-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('shield')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4.5 18.5 7v5.25c0 3.75-2.35 6.25-6.5 7.75-4.15-1.5-6.5-4-6.5-7.75V7L12 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg>
        @break

    @case('settings')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 15.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5ZM19.5 12a7.6 7.6 0 0 0-.1-1.2l2-1.55-2-3.5-2.4.95a7.8 7.8 0 0 0-2-1.15L14.65 3h-5.3L9 5.55a7.8 7.8 0 0 0-2 1.15l-2.4-.95-2 3.5 2 1.55A7.6 7.6 0 0 0 4.5 12c0 .4.03.8.1 1.2l-2 1.55 2 3.5 2.4-.95a7.8 7.8 0 0 0 2 1.15l.35 2.55h5.3l.35-2.55a7.8 7.8 0 0 0 2-1.15l2.4.95 2-3.5-2-1.55c.07-.4.1-.8.1-1.2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('globe')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 20.5a8.5 8.5 0 1 0 0-17 8.5 8.5 0 0 0 0 17ZM4 12h16M12 3.75c2.2 2.25 3.25 5 3.25 8.25s-1.05 6-3.25 8.25M12 3.75C9.8 6 8.75 8.75 8.75 12S9.8 18 12 20.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('arrow')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10h11M11 5l5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('menu')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M3 5.5H17M3 10H17M3 14.5H17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break

    @case('close')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
        @break

    @case('empty')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 8 8-4 8 4v8l-8 4-8-4V8Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="m4 8 8 4 8-4M12 12v8" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M9 3.5 15 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-dasharray="1 3" /></svg>
        @break

    @case('search')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10.75 17.5a6.75 6.75 0 1 0 0-13.5 6.75 6.75 0 0 0 0 13.5ZM15.5 15.5 20 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('download')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4v10m0 0 3.5-3.5M12 14l-3.5-3.5M5 17.5v1A1.5 1.5 0 0 0 6.5 20h11a1.5 1.5 0 0 0 1.5-1.5v-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('check')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('x')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break

    @case('warning')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4.5 20.5 19h-17L12 4.5ZM12 10v4M12 16.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('eye')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.75 12C4.85 7.85 8.05 5.75 12 5.75c3.95 0 7.15 2.1 9.25 6.25-2.1 4.15-5.3 6.25-9.25 6.25-3.95 0-7.15-2.1-9.25-6.25Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M12 14.75a2.75 2.75 0 1 0 0-5.5 2.75 2.75 0 0 0 0 5.5Z" stroke="currentColor" stroke-width="1.8" /></svg>
        @break

    @case('eye-off')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3.5 3.5 20.5 20.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M9.4 6.18C10.23 6 11.1 5.9 12 5.9c3.95 0 7.15 2.1 9.25 6.25a13 13 0 0 1-2.32 3.2M6.6 7.7C4.98 8.74 3.66 10.2 2.75 12c2.1 4.15 5.3 6.25 9.25 6.25 1.05 0 2.05-.15 3-.45" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M9.9 9.9a2.75 2.75 0 0 0 3.86 3.86" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break

    @case('package')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3 8.5 4.5v9L12 21l-8.5-4.5v-9L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9M7.75 5.25l8.5 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @case('clipboard-list')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 4.75h6a1 1 0 0 1 1 1V7H8V5.75a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M8 5.75H6.5A1.5 1.5 0 0 0 5 7.25v11.5A1.5 1.5 0 0 0 6.5 20.25h11A1.5 1.5 0 0 0 19 18.75V7.25a1.5 1.5 0 0 0-1.5-1.5H16" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M8.5 11h7M8.5 14h7M8.5 17h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break

    @case('history')
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.5 12a7.5 7.5 0 1 0 2.25-5.36" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M4.5 4.5V8H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M12 8v4.5l3 1.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break

    @default
        <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z" stroke="currentColor" stroke-width="1.8" /></svg>
@endswitch
