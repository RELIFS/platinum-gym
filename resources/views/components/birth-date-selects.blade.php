@props([
    'id' => 'birth_date',
    'name' => 'birth_date',
    'value' => null,
    'label' => 'Tanggal Lahir',
    'required' => true,
    'labelClass' => 'auth-label',
    'selectClass' => 'auth-input',
    'errorClass' => 'auth-error',
])

@php
    $displayName = $name.'_display';
    $rawValue = old($name, $value);
    $rawDisplayValue = old($displayName);

    if ($rawValue instanceof \DateTimeInterface) {
        $rawValue = $rawValue->format('Y-m-d');
    }

    if (blank($rawValue) && filled($rawDisplayValue)) {
        $rawValue = \App\Features\Shared\Support\ComposeBirthDate::fromDisplay($rawDisplayValue);
    }

    if (blank($rawValue) && old('birth_day') && old('birth_month') && old('birth_year')) {
        $rawValue = \App\Features\Shared\Support\ComposeBirthDate::fromParts(old('birth_year'), old('birth_month'), old('birth_day'));
    }

    $dateValue = '';

    try {
        $dateValue = filled($rawValue) ? \Carbon\CarbonImmutable::parse((string) $rawValue)->format('Y-m-d') : '';
    } catch (\Throwable) {
        $dateValue = '';
    }

    $minDate = '1940-01-01';
    $maxDate = now()->subDay()->format('Y-m-d');
    $legendId = $id.'-legend';
@endphp

<fieldset {{ $attributes->class('min-w-0') }}>
    <legend id="{{ $legendId }}" class="{{ $labelClass }}">{{ $label }}</legend>
    <x-local-date-input
        :id="$id"
        :name="$name"
        :value="$dateValue"
        :min="$minDate"
        :max="$maxDate"
        :class="$selectClass.' min-h-12'"
        :required="$required"
        :labelled-by="$legendId"
        autocomplete="bday"
        button-label="Pilih tanggal lahir"
    />
    <x-input-error id="{{ $id }}-error" :messages="$errors->get($name)" class="{{ $errorClass }}" />
</fieldset>
