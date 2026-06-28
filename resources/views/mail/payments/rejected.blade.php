<x-mail::message>
<div class="eyebrow">Transaksi member</div>

# Pembayaran ditolak

Halo {{ $user->name ?? 'Member Platinum Gym' }},

Pembayaran Anda belum bisa disetujui.

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
        <td class="detail-label">Status</td>
        <td class="detail-value">Ditolak</td>
    </tr>
    <tr>
        <td class="detail-label">Catatan</td>
        <td class="detail-value">{{ $reason }}</td>
    </tr>
</table>
</x-mail::panel>

Silakan periksa transaksi Anda atau hubungi admin Platinum Gym Padang jika membutuhkan bantuan.

<x-mail::button :url="$actionUrl" color="primary">
Lihat Transaksi
</x-mail::button>

Terima kasih,<br>
Platinum Gym Padang
</x-mail::message>
