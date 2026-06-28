<x-mail::message>
<div class="eyebrow">Booking kelas</div>

# {{ $statusLabel }}

Halo {{ $user->name ?? 'Member Platinum Gym' }},

{{ $headline }}

<x-mail::panel>
<table class="detail-table" role="presentation" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td class="detail-label">Kelas</td>
        <td class="detail-value">{{ $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym' }}</td>
    </tr>
    <tr>
        <td class="detail-label">Tanggal</td>
        <td class="detail-value">{{ $enrollment->session_date?->translatedFormat('d M Y') ?? '-' }}</td>
    </tr>
    <tr>
        <td class="detail-label">Jam</td>
        <td class="detail-value">{{ $enrollment->schedule?->start_time ? \Illuminate\Support\Str::of((string) $enrollment->schedule->start_time)->substr(0, 5) : '-' }} - {{ $enrollment->schedule?->end_time ? \Illuminate\Support\Str::of((string) $enrollment->schedule->end_time)->substr(0, 5) : '-' }}</td>
    </tr>
    <tr>
        <td class="detail-label">Trainer</td>
        <td class="detail-value">{{ $enrollment->schedule?->trainer?->name ?? 'Menyesuaikan jadwal' }}</td>
    </tr>
    <tr>
        <td class="detail-label">Status</td>
        <td class="detail-value">{{ $statusLabel }}</td>
    </tr>
</table>
</x-mail::panel>

<x-mail::button :url="$actionUrl" color="primary">
Lihat Riwayat Booking
</x-mail::button>

Terima kasih,<br>
Platinum Gym Padang
</x-mail::message>
