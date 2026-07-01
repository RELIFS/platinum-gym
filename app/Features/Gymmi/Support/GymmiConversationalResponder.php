<?php

namespace App\Features\Gymmi\Support;

use Illuminate\Support\Str;

class GymmiConversationalResponder
{
    public function replyFor(string $message, string $context): ?string
    {
        $normalized = $this->normalize($message);

        if ($normalized === '') {
            return null;
        }

        if ($this->isCapabilityQuestion($normalized)) {
            return $this->capabilityReply($context);
        }

        if ($this->hasDomainIntent($normalized)) {
            return null;
        }

        if ($this->isGreeting($normalized)) {
            return $this->greetingReply($context);
        }

        if ($this->isThanks($normalized)) {
            return $this->thanksReply($context);
        }

        if ($this->isWellbeing($normalized)) {
            return $this->wellbeingReply($context);
        }

        if ($this->isGoodbye($normalized)) {
            return 'Baik, sampai jumpa. Semoga latihan Anda lancar di Platinum Gym Padang.';
        }

        return null;
    }

    private function greetingReply(string $context): string
    {
        if ($context === 'member') {
            return 'Halo! Saya Gymmi, asisten portal member Platinum Gym Padang. Saya bisa bantu cek status membership, booking kelas, transaksi, QR member, profil, dan info layanan.';
        }

        return 'Halo! Saya Gymmi, asisten Platinum Gym Padang. Saya bisa bantu info membership, jadwal kelas, personal trainer, promo, produk katalog, lokasi, dan jam buka.';
    }

    private function thanksReply(string $context): string
    {
        if ($context === 'member') {
            return 'Sama-sama. Kalau masih ada yang ingin dicek, sebutkan topiknya seperti membership, booking kelas, transaksi, QR member, atau profil.';
        }

        return 'Sama-sama. Kalau masih ada yang ingin dicek, sebutkan topiknya seperti membership, jadwal kelas, lokasi, promo, atau produk.';
    }

    private function wellbeingReply(string $context): string
    {
        if ($context === 'member') {
            return 'Saya siap bantu member Platinum Gym. Mau cek membership, jadwal booking, transaksi, QR member, atau profil?';
        }

        return 'Saya siap bantu info Platinum Gym. Mau cek harga membership, jadwal kelas, lokasi, promo, atau personal trainer?';
    }

    private function capabilityReply(string $context): string
    {
        if ($context === 'member') {
            return 'Gymmi bisa bantu member mengecek ringkasan membership, paket sesi, transaksi menunggu, booking kelas, QR member, profil, serta info layanan Platinum Gym. Saya hanya memakai data akun Anda sendiri dan data resmi gym.';
        }

        return 'Gymmi bisa bantu menjawab info resmi Platinum Gym seperti membership, jadwal kelas, personal trainer, fasilitas, produk katalog, promo, lokasi, jam operasional, dan kontak admin.';
    }

    private function isGreeting(string $message): bool
    {
        return preg_match('/^(halo|hai|hi|hello|pagi|siang|sore|malam|selamat pagi|selamat siang|selamat sore|selamat malam|assalamualaikum)( gymmi| kak| admin)?$/u', $message) === 1;
    }

    private function isThanks(string $message): bool
    {
        return preg_match('/\b(makasih|terima kasih|terimakasih|thanks|thank you|tengkyu)\b/u', $message) === 1;
    }

    private function isWellbeing(string $message): bool
    {
        return $this->containsAny($message, [
            'apa kabar',
            'gimana kabarnya',
            'bagaimana kabarnya',
            'kabar gymmi',
            'sehat',
        ]);
    }

    private function isGoodbye(string $message): bool
    {
        return preg_match('/^(bye|dadah|sampai jumpa|sampai nanti|see you|selamat tinggal)( gymmi)?$/u', $message) === 1;
    }

    private function isCapabilityQuestion(string $message): bool
    {
        return $this->containsAny($message, [
            'siapa kamu',
            'kamu siapa',
            'gymmi siapa',
            'gymmi itu apa',
            'apa itu gymmi',
            'bisa bantu apa',
            'kamu bisa apa',
            'fitur gymmi',
            'bantuan gymmi',
        ]);
    }

    private function hasDomainIntent(string $message): bool
    {
        return $this->containsAny($message, [
            'harga',
            'biaya',
            'paket',
            'membership',
            'member',
            'jadwal',
            'kelas',
            'lokasi',
            'alamat',
            'maps',
            'produk',
            'promo',
            'trainer',
            'coach',
            'personal trainer',
            'qr',
            'transaksi',
            'invoice',
            'booking',
            'profil',
            'akun',
            'jam buka',
            'whatsapp',
            'instagram',
            'daftar',
            'bayar',
        ]);
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

    private function normalize(string $message): string
    {
        return Str::of($message)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();
    }
}
