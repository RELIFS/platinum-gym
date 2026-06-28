<x-mail::message>
<div class="eyebrow">Transaksi member</div>

# Pembayaran berhasil

Halo {{ $user->name ?? 'Member Platinum Gym' }},

Pembayaran Anda sudah berhasil diproses.

<x-mail::panel>
<table class="detail-table" role="presentation" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td class="detail-label">Kode pembayaran</td>
        <td class="detail-value">{{ $payment->payment_code }}</td>
    </tr>
    <tr>
        <td class="detail-label">Layanan</td>
        <td class="detail-value">{{ $serviceName }}</td>
    </tr>
    <tr>
        <td class="detail-label">Nominal</td>
        <td class="detail-value">{{ $amount }}</td>
    </tr>
    <tr>
        <td class="detail-label">Metode</td>
        <td class="detail-value">{{ $method }}</td>
    </tr>
    <tr>
        <td class="detail-label">Status</td>
        <td class="detail-value">Lunas</td>
    </tr>
</table>
</x-mail::panel>

@if ($startsOnFirstCheckIn)
<p class="subcopy" style="color: #71717a; font-size: 13px; line-height: 1.6;">
Catatan: masa aktif mulai saat check-in pertama. Membership Anda sudah aktif untuk akses dan QR, lalu durasinya dihitung setelah check-in gym pertama yang valid.
</p>
@endif

<x-mail::button :url="$actionUrl" color="primary">
Lihat Transaksi
</x-mail::button>

Terima kasih telah menggunakan layanan Platinum Gym Padang.
</x-mail::message>
