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
    'disabled' => false,
    'autocomplete' => 'off',
    'buttonLabel' => 'Pilih tanggal',
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
@endphp

<span
    {{ $attributes->class('relative block min-w-0') }}
    x-modelable="isoValue"
    x-data="{
        displayValue: @js($displayValue),
        isoValue: @js($isoValue),
        mode: @js($mode),
        minDate: @js($min),
        maxDate: @js($max),
        isFormatting: false,
        init() {
            this.displayValue = this.fromIso(this.isoValue) || this.displayValue;
            this.$watch('isoValue', (value) => {
                if (this.isFormatting) return;
                this.displayValue = this.fromIso(value);
            });
        },
        formatInput() {
            const digits = this.displayValue.replace(/\D/g, '').slice(0, this.mode === 'datetime' ? 12 : 8);
            const date = [digits.slice(0, 2), digits.slice(2, 4), digits.slice(4, 8)].filter(Boolean).join('/');
            const time = this.mode === 'datetime' && digits.length > 8 ? ` ${digits.slice(8, 10)}${digits.length > 10 ? ':' + digits.slice(10, 12) : ''}` : '';
            this.isFormatting = true;
            this.displayValue = date + time;
            const nextIsoValue = this.toIso(this.displayValue) || '';
            this.isoValue = nextIsoValue;
            if (nextIsoValue) {
                this.isFormatting = false;
                this.emitModelValue();
                return;
            }
            this.emitModelValue();
            this.$nextTick(() => {
                this.isFormatting = false;
            });
        },
        syncFromPicker(event) {
            this.isFormatting = true;
            this.isoValue = event.target.value || '';
            this.displayValue = this.fromIso(this.isoValue);
            this.isFormatting = false;
            this.emitModelValue();
        },
        emitModelValue() {
            this.$root.dispatchEvent(new CustomEvent('input', { detail: this.isoValue, bubbles: true }));
        },
        openPicker() {
            const picker = this.$refs.picker;
            if (! picker) return;
            picker.value = this.isoValue;
            if (typeof picker.showPicker === 'function') {
                try {
                    picker.showPicker();
                    return;
                } catch (error) {
                    // Browser automation and some WebViews can reject showPicker() even after a click.
                }
            }
            picker.focus();
            picker.click();
        },
        toIso(value) {
            const source = String(value || '');
            const match = this.mode === 'datetime'
                ? source.match(/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?$/)
                : source.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            if (! match) return null;
            const day = Number(match[1]);
            const month = Number(match[2]);
            const year = Number(match[3]);
            const hour = Number(match[4] || 0);
            const minute = Number(match[5] || 0);
            const parsed = new Date(Date.UTC(year, month - 1, day, hour, minute));
            if (parsed.getUTCFullYear() !== year || parsed.getUTCMonth() + 1 !== month || parsed.getUTCDate() !== day || hour > 23 || minute > 59) return null;
            const date = `${year.toString().padStart(4, '0')}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            if (this.minDate && date < this.minDate) return null;
            if (this.maxDate && date > this.maxDate) return null;
            if (this.mode !== 'datetime') return date;
            return `${date}T${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
        },
        fromIso(value) {
            const source = String(value || '');
            const match = this.mode === 'datetime'
                ? source.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/)
                : source.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (! match) return '';
            const date = `${match[3]}/${match[2]}/${match[1]}`;
            return this.mode === 'datetime' ? `${date} ${match[4]}:${match[5]}` : date;
        },
    }"
>
    <input type="hidden" name="{{ $name }}" x-bind:value="isoValue">
    <span class="relative {{ $controlSpacingClass }} block">
        <input
            id="{{ $id }}"
            name="{{ $displayName }}"
            type="text"
            x-model="displayValue"
            x-on:input="formatInput"
            placeholder="{{ $placeholder }}"
            inputmode="{{ $inputMode }}"
            maxlength="{{ $maxLength }}"
            class="{{ trim($inputClass.' pr-14') }}"
            autocomplete="{{ $autocomplete }}"
            @required($required)
            @disabled($disabled)
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
            @disabled($disabled)
        >
        <button
            type="button"
            class="absolute inset-y-1 right-1 inline-flex w-10 touch-manipulation items-center justify-center rounded-md text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 disabled:cursor-not-allowed disabled:opacity-50 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white"
            aria-label="{{ $buttonLabel }}"
            x-on:click="openPicker"
            @disabled($disabled)
        >
            @include($iconPartial, ['name' => 'calendar', 'class' => 'h-4 w-4', 'attributes' => $iconAttributes])
        </button>
    </span>
</span>
