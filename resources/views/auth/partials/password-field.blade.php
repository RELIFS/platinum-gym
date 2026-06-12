@props([
    'id',
    'name',
    'label',
    'autocomplete' => 'current-password',
    'placeholder' => '',
    'strength' => false,
    'value' => null,
])

<div>
    <label for="{{ $id }}" class="auth-label">{{ $label }}</label>
    <div class="relative">
        <input
            id="{{ $id }}"
            class="auth-input pr-12"
            type="password"
            name="{{ $name }}"
            @if (! is_null($value)) value="{{ $value }}" @endif
            required
            autocomplete="{{ $autocomplete }}"
            placeholder="{{ $placeholder }}"
            @if ($strength) data-password-feedback-input aria-describedby="{{ $id }}-feedback" @endif
            @error($name) aria-invalid="true" @enderror
        >
        <button type="button" class="auth-password-toggle" data-password-toggle="{{ $id }}" aria-label="Tampilkan kata sandi" aria-pressed="false">
            <svg data-eye-open class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M2.75 12C4.85 7.85 8.05 5.75 12 5.75C15.95 5.75 19.15 7.85 21.25 12C19.15 16.15 15.95 18.25 12 18.25C8.05 18.25 4.85 16.15 2.75 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                <path d="M12 14.75C13.52 14.75 14.75 13.52 14.75 12C14.75 10.48 13.52 9.25 12 9.25C10.48 9.25 9.25 10.48 9.25 12C9.25 13.52 10.48 14.75 12 14.75Z" stroke="currentColor" stroke-width="1.8" />
            </svg>
            <svg data-eye-closed class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3.25 3.25L20.75 20.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                <path d="M9.2 5.98C10.08 5.83 11.01 5.75 12 5.75C15.95 5.75 19.15 7.85 21.25 12C20.52 13.44 19.66 14.63 18.68 15.56" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M14.12 14.12C13.58 14.62 12.84 14.9 12 14.9C10.4 14.9 9.1 13.6 9.1 12C9.1 11.16 9.38 10.42 9.88 9.88" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                <path d="M6.52 7.55C5.08 8.57 3.83 10.05 2.75 12C4.85 16.15 8.05 18.25 12 18.25C13.32 18.25 14.55 18.01 15.68 17.52" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    </div>

    @if ($strength)
        <p id="{{ $id }}-feedback" class="mt-1.5 hidden text-xs font-medium text-red-600 dark:text-red-400" data-password-feedback>
            Kata sandi minimal 8 karakter.
        </p>
    @endif

    <x-input-error :messages="$errors->get($name)" class="auth-error" />
</div>
