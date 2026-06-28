<x-mail::message>
<div class="eyebrow">Kode verifikasi</div>

# Verifikasi email Anda

Halo {{ $user->name ?? 'Member Platinum Gym' }},

Masukkan kode berikut di halaman verifikasi email Platinum Gym Padang.

<div class="otp-wrap" style="margin: 26px 0; text-align: center;">
    <div class="otp-card" style="display: inline-block; letter-spacing: 8px; font-size: 30px; font-weight: 900; line-height: 1; color: #111827; background: #FEAC18; border-radius: 16px; padding: 18px 22px;">
        {{ $code }}
    </div>
</div>

Kode ini berlaku sampai **{{ $expiresAt->timezone(config('app.timezone'))->translatedFormat('d M Y H:i') }}** untuk {{ $maskedEmail }}.

Jangan bagikan kode ini kepada siapa pun, termasuk pihak yang mengaku sebagai Platinum Gym.

Jika email ini dibuka di browser yang masih login, gunakan tombol berikut sebagai alternatif cepat:

<x-mail::button :url="$verificationUrl" color="primary">
Verifikasi Email
</x-mail::button>

<p class="subcopy" style="color: #71717a; font-size: 13px; line-height: 1.6;">
Abaikan email ini jika Anda tidak mendaftar akun Platinum Gym Padang.
</p>

Terima kasih,<br>
Platinum Gym Padang
</x-mail::message>
