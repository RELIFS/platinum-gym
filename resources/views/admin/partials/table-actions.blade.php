@php
    $actions = collect($actions ?? []);
@endphp

<div class="flex flex-wrap gap-2">
    @foreach ($actions as $action)
        @php
            $method = strtoupper($action['method'] ?? 'GET');
            $variant = $action['variant'] ?? 'secondary';
            $variantClass = [
                'primary' => 'admin-button-primary',
                'danger' => 'admin-button-danger',
            ][$variant] ?? 'admin-button-secondary';
            $confirmMessage = $action['confirm'] ?? null;
            $ariaLabel = $action['aria_label'] ?? null;
        @endphp
        @if ($method === 'GET')
            <a href="{{ $action['url'] }}" class="{{ $variantClass }}" @if (filled($ariaLabel)) aria-label="{{ $ariaLabel }}" @endif>{{ $action['label'] }}</a>
        @else
            <x-confirm-form
                :action="$action['url']"
                :method="$method"
                :message="$confirmMessage ?? ''"
                :variant="$variant === 'danger' ? 'danger' : 'primary'"
                :confirm-label="$action['label']"
            >
                <button type="submit" class="{{ $variantClass }}" @if (filled($ariaLabel)) aria-label="{{ $ariaLabel }}" @endif>{{ $action['label'] }}</button>
            </x-confirm-form>
        @endif
    @endforeach
</div>
