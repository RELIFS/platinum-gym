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
    $displayValue = filled($rawDisplayValue) ? (string) $rawDisplayValue : '';

    try {
        $dateValue = filled($rawValue) ? \Carbon\CarbonImmutable::parse((string) $rawValue)->format('Y-m-d') : '';

        if (blank($displayValue) && filled($dateValue)) {
            $displayValue = \Carbon\CarbonImmutable::parse($dateValue)->format('d/m/Y');
        }
    } catch (\Throwable) {
        $dateValue = '';
    }

    $minDate = '1940-01-01';
    $maxDate = now()->subDay()->format('Y-m-d');
@endphp

<fieldset
    {{ $attributes->class('min-w-0') }}
    x-data="{
        displayValue: @js($displayValue),
        isoValue: @js($dateValue),
        minDate: @js($minDate),
        maxDate: @js($maxDate),
        formatInput() {
            const digits = this.displayValue.replace(/\D/g, '').slice(0, 8);
            const day = digits.slice(0, 2);
            const month = digits.slice(2, 4);
            const year = digits.slice(4, 8);

            this.displayValue = [day, month, year].filter(Boolean).join('/');
            this.isoValue = this.toIso(this.displayValue) || '';
        },
        syncFromCalendar(event) {
            this.isoValue = event.target.value || '';
            this.displayValue = this.fromIso(this.isoValue);
        },
        openPicker() {
            const picker = this.$refs.calendar;

            if (! picker) {
                return;
            }

            picker.value = this.isoValue;

            if (typeof picker.showPicker === 'function') {
                picker.showPicker();
                return;
            }

            picker.focus();
            picker.click();
        },
        toIso(value) {
            const match = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);

            if (! match) {
                return null;
            }

            const day = Number(match[1]);
            const month = Number(match[2]);
            const year = Number(match[3]);
            const date = new Date(Date.UTC(year, month - 1, day));

            if (date.getUTCFullYear() !== year || date.getUTCMonth() + 1 !== month || date.getUTCDate() !== day) {
                return null;
            }

            const iso = `${year.toString().padStart(4, '0')}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

            if (iso < this.minDate || iso > this.maxDate) {
                return null;
            }

            return iso;
        },
        fromIso(value) {
            const match = value.match(/^(\d{4})-(\d{2})-(\d{2})$/);

            if (! match) {
                return '';
            }

            return `${match[3]}/${match[2]}/${match[1]}`;
        },
    }"
>
    <legend class="{{ $labelClass }}">{{ $label }}</legend>
    <div class="relative min-w-0">
        <input
            id="{{ $id }}"
            name="{{ $displayName }}"
            type="text"
            x-model="displayValue"
            x-on:input="formatInput"
            placeholder="dd/mm/yyyy"
            inputmode="numeric"
            maxlength="10"
            class="{{ $selectClass }} min-h-12 w-full pr-14"
            autocomplete="bday"
            @if ($required) required @endif
            @error($name) aria-invalid="true" @enderror
        >
        <input type="hidden" name="{{ $name }}" x-bind:value="isoValue">
        <input
            x-ref="calendar"
            type="date"
            min="{{ $minDate }}"
            max="{{ $maxDate }}"
            class="pointer-events-none absolute inset-y-0 right-0 h-full w-11 opacity-0"
            tabindex="-1"
            aria-hidden="true"
            x-on:change="syncFromCalendar"
        >
        <button
            type="button"
            class="absolute inset-y-1.5 right-1.5 inline-flex min-h-10 w-10 items-center justify-center rounded-md border border-transparent text-zinc-500 transition hover:border-zinc-200 hover:bg-zinc-50 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-gold-500/30 dark:text-zinc-400 dark:hover:border-white/10 dark:hover:bg-white/[0.06] dark:hover:text-white"
            aria-label="Pilih tanggal lahir"
            x-on:click="openPicker"
        >
            @include('member.partials.icon', ['name' => 'calendar', 'class' => 'h-5 w-5'])
        </button>
    </div>
    <x-input-error :messages="$errors->get($name)" class="{{ $errorClass }}" />
</fieldset>
