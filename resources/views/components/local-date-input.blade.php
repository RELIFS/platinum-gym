@props([
    'id',
    'name',
    'value' => null,
    'mode' => 'date',
    'class' => '',
    'required' => false,
    'min' => null,
    'max' => null,
    'describedBy' => null,
    'labelledBy' => null,
    'disabled' => false,
    'autocomplete' => 'off',
    'buttonLabel' => 'Pilih tanggal',
    'picker' => 'native',
    'allowedWeekdays' => [],
    'alpineDisabled' => null,
    'alpineAllowedWeekdays' => null,
])

@php
    $isDateTime = $mode === 'datetime';
    $displayName = $name.'_display';
    $rawValue = old($name, $value);
    $rawDisplayValue = old($displayName);

    if ($rawValue instanceof \DateTimeInterface) {
        $rawValue = $isDateTime ? $rawValue->format('Y-m-d\TH:i') : $rawValue->format('Y-m-d');
    }

    $isoValue = '';
    $displayValue = filled($rawDisplayValue) ? (string) $rawDisplayValue : '';

    try {
        if (filled($rawValue)) {
            $isoValue = $isDateTime
                ? \Carbon\CarbonImmutable::parse((string) $rawValue)->format('Y-m-d\TH:i')
                : \Carbon\CarbonImmutable::parse((string) $rawValue)->format('Y-m-d');

            if (blank($displayValue)) {
                $displayValue = $isDateTime
                    ? \Carbon\CarbonImmutable::parse((string) $rawValue)->format('d/m/Y H:i')
                    : \Carbon\CarbonImmutable::parse((string) $rawValue)->format('d/m/Y');
            }
        }
    } catch (\Throwable) {
        $isoValue = '';
    }

    $pickerType = $isDateTime ? 'datetime-local' : 'date';
    $placeholder = $isDateTime ? 'dd/mm/yyyy HH:mm' : 'dd/mm/yyyy';
    $inputMode = $isDateTime ? 'text' : 'numeric';
    $maxLength = $isDateTime ? 16 : 10;
    $errorId = $id.'-error';
    $ariaDescribedBy = trim((string) $describedBy.' '.($errors->has($name) ? $errorId : ''));
    $classTokens = collect(preg_split('/\s+/', trim((string) $class), -1, PREG_SPLIT_NO_EMPTY));
    $controlSpacingClass = $classTokens->contains('mt-2') ? 'mt-2' : '';
    $inputClass = $classTokens->reject(fn (string $token): bool => $token === 'mt-2')->implode(' ');
    $iconPartial = str_contains((string) $class, 'admin-form-input') ? 'admin.partials.icon' : 'member.partials.icon';
    $iconAttributes = new \Illuminate\View\ComponentAttributeBag();
    $allowedWeekdays = collect($allowedWeekdays)
        ->map(fn ($day) => (int) $day)
        ->filter(fn (int $day): bool => $day >= 1 && $day <= 7)
        ->unique()
        ->values()
        ->all();
    $alpineEffects = collect([
        $alpineDisabled ? 'setDisabled('.$alpineDisabled.')' : null,
        $alpineAllowedWeekdays ? 'setAllowedWeekdays('.$alpineAllowedWeekdays.')' : null,
    ])->filter()->implode('; ');
@endphp

<span
    {{ $attributes->class('relative block min-w-0') }}
    x-modelable="isoValue"
    x-data="localDateInput({
        displayValue: @js($displayValue),
        isoValue: @js($isoValue),
        mode: @js($mode),
        minDate: @js($min),
        maxDate: @js($max),
        picker: @js($picker),
        allowedWeekdays: @js($allowedWeekdays),
        disabled: @js((bool) $disabled),
    })"
    @if ($alpineEffects !== '') x-effect="{!! $alpineEffects !!}" @endif
    data-local-date-input
    data-local-date-picker="{{ $picker }}"
    data-allowed-weekdays='@json($allowedWeekdays)'
>
    <input type="hidden" name="{{ $name }}" x-bind:value="isoValue" x-bind:disabled="isDisabled">
    <span class="relative {{ $controlSpacingClass }} block">
        <input
            id="{{ $id }}"
            name="{{ $displayName }}"
            type="text"
            x-ref="displayInput"
            x-model="displayValue"
            x-on:input="formatInput"
            x-on:change="commitTypedInput"
            x-on:blur="commitTypedInput"
            placeholder="{{ $placeholder }}"
            inputmode="{{ $inputMode }}"
            maxlength="{{ $maxLength }}"
            class="{{ trim($inputClass.' pr-14') }}"
            autocomplete="{{ $autocomplete }}"
            x-bind:disabled="isDisabled"
            @required($required)
            @disabled($disabled)
            @if ($labelledBy) aria-labelledby="{{ $labelledBy }}" @endif
            @if ($ariaDescribedBy !== '') aria-describedby="{{ $ariaDescribedBy }}" @endif
            @error($name) aria-invalid="true" @enderror
        >
        <input
            x-ref="picker"
            type="{{ $pickerType }}"
            value="{{ $isoValue }}"
            @if ($min) min="{{ $min }}" @endif
            @if ($max) max="{{ $max }}" @endif
            tabindex="-1"
            aria-hidden="true"
            class="pointer-events-none absolute inset-y-0 right-0 h-full w-12 opacity-0"
            x-on:change="syncFromPicker"
            x-bind:disabled="isDisabled"
            @disabled($disabled)
        >
        <button
            type="button"
            class="absolute inset-y-1 right-1 inline-flex w-10 touch-manipulation items-center justify-center rounded-md text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-700/40 disabled:cursor-not-allowed disabled:opacity-50 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-zinc-100 dark:focus-visible:ring-gold-400/40"
            aria-label="{{ $buttonLabel }}"
            x-on:click="openPicker"
            x-bind:disabled="isDisabled"
            @disabled($disabled)
        >
            @include($iconPartial, ['name' => 'calendar', 'class' => 'h-4 w-4', 'attributes' => $iconAttributes])
        </button>
    </span>
</span>
