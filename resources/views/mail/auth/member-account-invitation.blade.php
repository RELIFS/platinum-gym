<x-mail::message>
<div class="eyebrow">Undangan member</div>

# Aktivasi akun member

Halo {{ $user->name ?? 'Member Platinum Gym' }},

Admin Platinum Gym Padang sudah membuat akun member untuk alamat email ini. Untuk mulai memakai portal member, atur kata sandi Anda melalui tombol berikut:

<x-mail::button :url="$acceptUrl" color="primary">
Atur Password & Aktifkan Akun
</x-mail::button>

<x-mail::panel>
Link undangan ini berlaku sampai **{{ $expiresAt->timezone(config('app.timezone'))->translatedFormat('d M Y H:i') }}** dan hanya bisa dipakai satu kali.
</x-mail::panel>

Jika Anda tidak merasa didaftarkan sebagai member Platinum Gym, abaikan email ini atau hubungi admin.

Terima kasih,<br>
Platinum Gym Padang
</x-mail::message>
