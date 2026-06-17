@php
    $filters = collect($filters ?? []);
    $selects = collect($selects ?? []);
    $showSearch = $showSearch ?? true;
    $searchName = $searchName ?? 'q';
    $searchPlaceholder = $searchPlaceholder ?? 'Cari data...';
    $submitLabel = $submitLabel ?? 'Terapkan';
    $resetLabel = $resetLabel ?? 'Reset';
@endphp

<form method="GET" action="{{ url()->current() }}" class="mt-5 flex flex-wrap items-end gap-3">
    @if ($showSearch)
        <label class="min-w-0 flex-1 basis-72">
            <span class="sr-only">{{ $searchLabel ?? 'Cari data member' }}</span>
            <input
                type="search"
                name="{{ $searchName }}"
                value="{{ $filters->get($searchName) }}"
                class="min-h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-900 shadow-sm transition placeholder:text-zinc-400 focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white"
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
            <select name="{{ $name }}" class="min-h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-900 shadow-sm transition focus:border-gold-500 focus:outline-none focus:ring-2 focus:ring-gold-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white" aria-label="{{ $select['label'] ?? 'Filter' }}">
                <option value="">{{ $select['placeholder'] ?? 'Semua' }}</option>
                @foreach ($options as $value => $label)
                    <option value="{{ $value }}" @selected((string) $filters->get($name) === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    @endforeach

    <button type="submit" class="member-button-primary min-h-11">{{ $submitLabel }}</button>
    <a href="{{ url()->current() }}" class="member-button-secondary min-h-11">{{ $resetLabel }}</a>
</form>
