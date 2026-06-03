@if (! is_null($amount))
    Rp {{ number_format((float) $amount, 0, ',', '.') }}
@else
    Hubungi admin
@endif
