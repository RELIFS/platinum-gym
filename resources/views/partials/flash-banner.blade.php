@php
    $message = trim((string) ($message ?? ''));
    $errorMessage = trim((string) ($errorMessage ?? ''));
    $kind = (string) ($kind ?? session('status_kind', 'success'));

    $palette = [
        'success' => [
            'classes' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200',
            'role' => 'status',
            'live' => 'polite',
        ],
        'error' => [
            'classes' => 'border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-200',
            'role' => 'alert',
            'live' => 'assertive',
        ],
        'warning' => [
            'classes' => 'border-amber-500/30 bg-amber-500/10 text-amber-800 dark:text-amber-200',
            'role' => 'status',
            'live' => 'polite',
        ],
        'info' => [
            'classes' => 'border-sky-500/30 bg-sky-500/10 text-sky-800 dark:text-sky-200',
            'role' => 'status',
            'live' => 'polite',
        ],
    ];

    $tone = $palette[$kind] ?? $palette['success'];
    $errorTone = $palette['error'];
@endphp

@if ($message !== '')
    <div class="mb-5 rounded-lg border px-4 py-3 text-sm type-compact {{ $tone['classes'] }}" role="{{ $tone['role'] }}" aria-live="{{ $tone['live'] }}">
        {{ $message }}
    </div>
@endif

@if ($errorMessage !== '')
    <div class="mb-5 rounded-lg border px-4 py-3 text-sm type-compact {{ $errorTone['classes'] }}" role="{{ $errorTone['role'] }}" aria-live="{{ $errorTone['live'] }}">
        {{ $errorMessage }}
    </div>
@endif
