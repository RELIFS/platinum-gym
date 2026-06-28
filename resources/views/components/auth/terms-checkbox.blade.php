@props([
    'id' => 'terms',
    'name' => 'terms',
    'errorId' => 'terms-error',
    'errorClass' => 'auth-error',
])

<div>
    <label for="{{ $id }}" class="auth-terms-row">
        <input
            id="{{ $id }}"
            type="checkbox"
            name="{{ $name }}"
            value="1"
            @checked(old($name))
            class="auth-terms-checkbox"
            required
            @error($name) aria-invalid="true" aria-describedby="{{ $errorId }}" @enderror
        >
        <span class="auth-terms-copy">
            Saya menyetujui <a href="{{ route('legal.terms') }}" class="auth-inline-link">Syarat &amp; Ketentuan</a> dan <a href="{{ route('legal.privacy') }}" class="auth-inline-link">Kebijakan Privasi</a> Platinum Gym Padang.
        </span>
    </label>
    <x-input-error id="{{ $errorId }}" :messages="$errors->get($name)" class="{{ $errorClass }} auth-terms-error" />
</div>
