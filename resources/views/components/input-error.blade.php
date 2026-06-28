@props(['messages', 'id' => null])

@if ($messages)
    <ul @if ($id) id="{{ $id }}" @endif {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1', 'role' => 'alert', 'aria-live' => 'assertive']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
