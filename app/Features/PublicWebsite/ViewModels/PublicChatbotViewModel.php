<?php

namespace App\Features\PublicWebsite\ViewModels;

class PublicChatbotViewModel
{
    public static function make(array $settings): array
    {
        $whatsappUrl = $settings['whatsapp_url'] ?? 'https://wa.me/6282174777761';
        $whatsappChatUrl = $whatsappUrl.(str_contains($whatsappUrl, '?') ? '&' : '?').http_build_query([
            'text' => 'Halo Platinum Gym Padang, saya ingin konsultasi dari Gymmi di website.',
        ]);

        return [
            'name' => 'Gymmi',
            'botInitials' => 'GY',
            'avatarLightUrl' => asset('images/gymmi/avatar-gymmi-light-96.webp'),
            'avatarDarkUrl' => asset('images/gymmi/avatar-gymmi-dark-96.webp'),
            'typingLabel' => 'Gymmi sedang mengetik',
            'context' => 'public',
            'endpoint' => route('gymmi.chat'),
            'csrfToken' => csrf_token(),
            'aiEnabled' => self::hasGeminiKeys(),
            'whatsappUrl' => $whatsappChatUrl,
            'initialMessage' => 'Halo! Saya Gymmi dari Platinum Gym Padang. Saya bisa bantu info membership, jadwal kelas, personal trainer, promo, lokasi, dan jam buka.',
            'quickReplies' => ['Info Membership', 'Jadwal Kelas', 'Harga Personal Trainer', 'Lokasi & Jam Buka'],
            'replies' => [
                'greeting' => 'Halo! Saya Gymmi, asisten Platinum Gym Padang. Saya bisa bantu info membership, jadwal kelas, personal trainer, promo, produk katalog, lokasi, dan jam buka.',
                'check' => 'Gymmi aktif. Silakan tanyakan membership, jadwal kelas, harga, lokasi, atau kontak admin Platinum Gym.',
                'thanks' => 'Sama-sama. Kalau masih ada yang ingin dicek, sebutkan topiknya seperti membership, jadwal kelas, lokasi, promo, atau produk.',
                'wellbeing' => 'Saya siap bantu info Platinum Gym. Mau cek harga membership, jadwal kelas, lokasi, promo, atau personal trainer?',
                'capability' => 'Gymmi bisa bantu menjawab info resmi Platinum Gym seperti membership, jadwal kelas, personal trainer, fasilitas, produk katalog, promo, lokasi, jam operasional, dan kontak admin.',
                'goodbye' => 'Baik, sampai jumpa. Semoga latihan Anda lancar di Platinum Gym Padang.',
                'membership' => 'Paket membership tersedia untuk umum dan mahasiswa. Mulai dari Gym Umum, Gym Mahasiswa, serta paket khusus sesuai promo aktif. Untuk daftar, gunakan tombol Daftar Member.',
                'schedule' => 'Jadwal kelas meliputi Aerobic, Zumba, Poundfit, dan Muaythai. Buka halaman Kelas untuk melihat hari, jam, pelatih, dan filter jadwal terbaru.',
                'classPrice' => 'Muaythai dan Poundfit memakai paket sesi terpisah. Untuk harga terbaru, cek halaman Layanan atau tanyakan langsung nama kelasnya, misalnya harga Muaythai atau harga Poundfit.',
                'trainer' => 'Personal Trainer tersedia dalam paket 5x, 10x, dan 24x sesi untuk member aktif. Program latihan dapat disesuaikan dengan target dan kondisi tubuh.',
                'location' => 'Platinum Gym Padang berlokasi di Jl. H. Agus Salim No.3A, Sawahan, Padang. Jam operasional: Senin-Sabtu 08:00-22:00, Minggu tutup.',
                'promo' => 'Promo aktif ditampilkan di halaman Beranda dan Layanan. Jika promo berubah, informasi di website mengikuti data terbaru dari admin.',
                'fallback' => 'Saya belum menangkap topiknya. Coba tulis seperti harga Gym Umum, jadwal Muaythai, lokasi gym, atau metode pembayaran.',
            ],
        ];
    }

    private static function hasGeminiKeys(): bool
    {
        return (bool) config('services.gemini.enabled', true)
            && (filled(config('services.gemini.api_key')) || filled(config('services.gemini.api_keys')));
    }
}
