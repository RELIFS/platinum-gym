@php($actions = collect($actions ?? []))

<div class="flex flex-wrap gap-2">
    @foreach ($actions as $action)
        @php($method = strtoupper($action['method'] ?? 'PATCH'))
        @if ($method === 'GET')
            <a href="{{ $action['url'] }}" class="{{ ($action['variant'] ?? 'secondary') === 'primary' ? 'admin-button-primary' : 'admin-button-secondary' }}">{{ $action['label'] }}</a>
        @else
            <form method="POST" action="{{ $action['url'] }}">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif
                <button type="submit" class="{{ ($action['variant'] ?? 'secondary') === 'primary' ? 'admin-button-primary' : 'admin-button-secondary' }}">{{ $action['label'] }}</button>
            </form>
        @endif
    @endforeach
</div>
