@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'type-compact text-sm text-green-600']) }}>
        {{ $status }}
    </div>
@endif
