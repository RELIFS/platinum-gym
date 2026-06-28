<x-mail::message>
<div class="eyebrow">Keamanan akun</div>

# Atur ulang kata sandi

Halo {{ $user->name ?? 'Member Platinum Gym' }},

Kami menerima permintaan untuk mengatur ulang kata sandi akun Platinum Gym Padang Anda.

<x-mail::button :url="$resetUrl" color="primary">
Atur Ulang Kata Sandi
</x-mail::button>

<x-mail::panel>
Link ini berlaku selama **{{ $expiresMinutes }} menit** dan hanya dipakai untuk akun Anda.
</x-mail::panel>

Jika Anda tidak meminta reset kata sandi, abaikan email ini dan kata sandi Anda tidak akan berubah.

Terima kasih,<br>
Platinum Gym Padang
</x-mail::message>
