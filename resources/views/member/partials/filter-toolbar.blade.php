@php
    $filters = collect($filters ?? []);
    $selects = collect($selects ?? []);
    $showSearch = $showSearch ?? true;
    $searchName = $searchName ?? 'q';
    $searchPlaceholder = $searchPlaceholder ?? 'Cari data...';
    $submitLabel = $submitLabel ?? 'Terapkan';
    $resetLabel = $resetLabel ?? 'Reset';
@endphp

<form method="GET" action="{{ url()->current() }}" class="mt-5 rounded-lg border border-zinc-200 bg-zinc-50/70 p-3 dark:border-white/10 dark:bg-white/[0.035] sm:p-4">
    <div class="flex flex-wrap items-end gap-3">
    @if ($showSearch)
        <label class="min-w-0 flex-1 basis-72">
            <span class="sr-only">{{ $searchLabel ?? 'Cari data member' }}</span>
            <input
                type="search"
                name="{{ $searchName }}"
                value="{{ $filters->get($searchName) }}"
                class="member-form-input"
                placeholder="{{ $searchPlaceholder }}"
                autocomplete="off"
                spellcheck="false"
            >
        </label>
    @endif

    @foreach ($selects as $select)
        @php
            $name = $select['name'];
            $options = collect($select['options'] ?? []);
        @endphp
        <label class="min-w-0 flex-1 basis-56">
            <span class="sr-only">{{ $select['label'] ?? 'Filter' }}</span>
            <select name="{{ $name }}" class="member-form-input" aria-label="{{ $select['label'] ?? 'Filter' }}">
                <option value="">{{ $select['placeholder'] ?? 'Semua' }}</option>
                @foreach ($options as $value => $label)
                    <option value="{{ $value }}" @selected((string) $filters->get($name) === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    @endforeach

        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            <button type="submit" class="member-button-primary min-h-11 w-full sm:w-auto">{{ $submitLabel }}</button>
            <a href="{{ url()->current() }}" class="member-button-secondary min-h-11 w-full sm:w-auto">{{ $resetLabel }}</a>
        </div>
    </div>
</form>
