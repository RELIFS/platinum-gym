<?php

namespace App\Features\Gymmi\Support;

use Illuminate\Support\Str;

class GymmiInputGuard
{
    /**
     * @return array{allowed: bool, reason: string|null, reply: string|null}
     */
    public function inspect(string $message): array
    {
        $normalized = $this->normalize($message);

        if ($normalized === '' || mb_strlen($normalized) < 2) {
            return $this->blocked('empty', 'Tulis pertanyaan singkat tentang Platinum Gym agar Gymmi bisa membantu.');
        }

        if (mb_strlen($message) > 700) {
            return $this->blocked('too_long', 'Pesan terlalu panjang. Ringkas pertanyaan Anda dalam satu topik utama.');
        }

        if (preg_match('/(.)\1{24,}/u', $normalized) || preg_match('/[^\pL\pN\s.,?!:;@\/+\-()&%#]{8,}/u', $message)) {
            return $this->blocked('spam', 'Saya belum bisa membaca pesan itu. Tulis ulang dengan kata-kata biasa tentang Platinum Gym.');
        }

        if ($this->containsAny($normalized, [
            'api key', 'apikey', 'gemini key', 'resend key', 'token rahasia', 'secret', '.env',
            'app key', 'midtrans key', 'qr token', 'raw qr token', 'payment response',
            'raw payment response', 'payload midtrans', 'password admin', 'kata sandi admin',
        ])) {
            return $this->blocked('secret_request', 'Saya tidak bisa membantu membuka API key, token, password, atau data rahasia. Untuk bantuan akun, gunakan menu resmi atau hubungi admin.');
        }

        if ($this->containsAny($normalized, [
            'abaikan instruksi', 'ignore instruction', 'ignore previous', 'lupakan aturan',
            'jawab bebas', 'karang jawaban', 'buat data palsu', 'bypass role', 'akses database',
            'lihat database', 'sql dump', 'role admin', 'jadikan saya admin', 'data member lain',
            'member lain', 'transaksi orang lain', 'booking orang lain',
        ])) {
            return $this->blocked('prompt_injection', 'Saya hanya bisa menjawab berdasarkan data resmi Platinum Gym dan tidak bisa mengikuti instruksi untuk melewati aturan sistem.');
        }

        if ($this->containsAny($normalized, [
            'prediksi saham', 'harga bitcoin', 'togel', 'politik', 'pilpres', 'diagnosis penyakit',
            'obat apa', 'resep obat', 'pinjaman online',
        ])) {
            return $this->blocked('out_of_scope', 'Saya fokus membantu informasi Platinum Gym. Untuk topik di luar layanan gym, silakan gunakan sumber resmi yang sesuai.');
        }

        return ['allowed' => true, 'reason' => null, 'reply' => null];
    }

    private function normalize(string $message): string
    {
        return Str::of($message)
            ->lower()
            ->ascii()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }

    /**
     * @param  array<int, string>  $needles
     */
    private function containsAny(string $message, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{allowed: bool, reason: string, reply: string}
     */
    private function blocked(string $reason, string $reply): array
    {
        return ['allowed' => false, 'reason' => $reason, 'reply' => $reply];
    }
}
