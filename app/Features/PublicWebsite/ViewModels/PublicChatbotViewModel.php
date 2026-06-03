<?php

namespace App\Features\PublicWebsite\ViewModels;

class PublicChatbotViewModel
{
    public static function make(array $settings): array
    {
        $whatsappUrl = $settings['whatsapp_url'] ?? 'https://wa.me/6282174777761';
        $whatsappChatUrl = $whatsappUrl.(str_contains($whatsappUrl, '?') ? '&' : '?').http_build_query([
            'text' => 'Halo Platinum Gym Padang, saya ingin konsultasi dari chatbot website.',
        ]);

        return [
            'whatsappUrl' => $whatsappChatUrl,
            'initialMessage' => 'Halo! Saya chatbot Platinum Gym Padang. Saya bisa bantu info membership, jadwal kelas, personal trainer, promo, lokasi, dan jam buka.',
            'quickReplies' => ['Info Membership', 'Jadwal Kelas', 'Harga Personal Trainer', 'Lokasi & Jam Buka'],
            'replies' => [
                'membership' => 'Paket membership tersedia untuk umum dan mahasiswa. Mulai dari Gym Umum, Gym Mahasiswa, serta paket khusus sesuai promo aktif. Untuk daftar, gunakan tombol Daftar Member.',
                'schedule' => 'Jadwal kelas meliputi Aerobic, Zumba, Poundfit, dan Muaythai. Buka halaman Kelas untuk melihat hari, jam, pelatih, dan filter jadwal terbaru.',
                'trainer' => 'Personal Trainer tersedia dalam paket 5x, 10x, dan 24x sesi untuk member aktif. Program latihan dapat disesuaikan dengan target dan kondisi tubuh.',
                'location' => 'Platinum Gym Padang berlokasi di Jl. H. Agus Salim No.3A, Sawahan, Padang. Jam operasional reguler: Senin-Jumat 06.00-22.00 dan akhir pekan mengikuti jadwal operasional terbaru.',
                'promo' => 'Promo aktif ditampilkan di halaman Beranda dan Layanan. Jika promo berubah, informasi di website mengikuti data terbaru dari admin.',
                'fallback' => 'Saya belum menemukan jawaban yang cocok. Coba pilih topik cepat di bawah, atau hubungi admin untuk bantuan lanjutan.',
            ],
        ];
    }
}
