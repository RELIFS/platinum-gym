<?php

namespace App\Features\MemberPortal\ViewModels;

class MemberChatbotViewModel
{
    /**
     * @param  array<string, mixed>  $portal
     * @return array<string, mixed>
     */
    public static function make(array $portal = []): array
    {
        return [
            'name' => 'Gymmi',
            'botInitials' => 'GY',
            'avatarLightUrl' => asset('images/gymmi/avatar-gymmi-light-96.webp'),
            'avatarDarkUrl' => asset('images/gymmi/avatar-gymmi-dark-96.webp'),
            'typingLabel' => 'Gymmi sedang mengetik',
            'context' => 'member',
            'endpoint' => route('gymmi.chat'),
            'csrfToken' => csrf_token(),
            'aiEnabled' => self::hasGeminiKeys(),
            'showEscalation' => false,
            'initialMessage' => 'Halo! Saya Gymmi dari portal member Platinum Gym Padang. Saya bisa bantu status membership, jadwal kelas, transaksi, QR member, dan bantuan akun.',
            'quickReplies' => ['Status Membership', 'Jadwal Kelas', 'Transaksi', 'QR Member', 'Bantuan Akun'],
            'replies' => [
                'greeting' => [
                    'text' => 'Halo! Saya Gymmi, asisten portal member Platinum Gym Padang. Saya bisa bantu cek status membership, booking kelas, transaksi, QR member, profil, dan info layanan.',
                ],
                'check' => [
                    'text' => 'Gymmi aktif. Saya bisa bantu cek membership, booking kelas, transaksi, QR member, profil, atau info layanan Platinum Gym.',
                ],
                'thanks' => [
                    'text' => 'Sama-sama. Kalau masih ada yang ingin dicek, sebutkan topiknya seperti membership, booking kelas, transaksi, QR member, atau profil.',
                ],
                'wellbeing' => [
                    'text' => 'Saya siap bantu member Platinum Gym. Mau cek membership, jadwal booking, transaksi, QR member, atau profil?',
                ],
                'capability' => [
                    'text' => 'Gymmi bisa bantu member mengecek ringkasan membership, paket sesi, transaksi menunggu, booking kelas, QR member, profil, serta info layanan Platinum Gym. Saya hanya memakai data akun Anda sendiri dan data resmi gym.',
                ],
                'goodbye' => [
                    'text' => 'Baik, sampai jumpa. Semoga latihan Anda lancar di Platinum Gym Padang.',
                ],
                'membership' => [
                    'text' => 'Status membership, masa aktif, dan paket sesi aktif dapat dicek di halaman Membership.',
                    'actionLabel' => 'Buka Membership',
                    'actionUrl' => route('member.membership'),
                ],
                'schedule' => [
                    'text' => 'Jadwal kelas aktif tersedia di halaman Booking Kelas. Jika memenuhi syarat paket, booking dapat dilakukan langsung dari halaman tersebut.',
                    'actionLabel' => 'Buka Jadwal Kelas',
                    'actionUrl' => route('member.booking'),
                ],
                'classPrice' => [
                    'text' => 'Muaythai dan Poundfit memakai paket sesi terpisah. Harga dan sisa sesi bisa dicek dari halaman Membership, lalu booking dilakukan dari halaman Booking Kelas.',
                    'actionLabel' => 'Buka Membership',
                    'actionUrl' => route('member.membership'),
                ],
                'transactions' => [
                    'text' => 'Riwayat pembayaran, status transaksi, dan tombol lanjut bayar tersedia di halaman Transaksi.',
                    'actionLabel' => 'Buka Transaksi',
                    'actionUrl' => route('member.transactions'),
                ],
                'qr' => [
                    'text' => 'Status QR member tersedia di halaman QR Member. QR aktif dapat ditunjukkan ke admin untuk check-in, dan token mentah tidak ditampilkan.',
                    'actionLabel' => 'Buka QR Member',
                    'actionUrl' => route('member.qr'),
                ],
                'account' => [
                    'text' => 'Data profil member tersedia di halaman Profil. Pengaturan login seperti email dan kata sandi dapat dibuka dari area akun login.',
                    'actionLabel' => 'Buka Profil',
                    'actionUrl' => route('member.profile'),
                ],
                'trainer' => [
                    'text' => 'Informasi personal trainer dan layanan latihan dapat dilihat di katalog layanan Platinum Gym Padang.',
                    'actionLabel' => 'Lihat Layanan',
                    'actionUrl' => route('public.services'),
                ],
                'location' => [
                    'text' => 'Alamat, maps, dan kontak Platinum Gym Padang tersedia di halaman lokasi.',
                    'actionLabel' => 'Lihat Lokasi',
                    'actionUrl' => route('public.location'),
                ],
                'promo' => [
                    'text' => 'Promo aktif ditampilkan di website jika sedang tersedia. Jika tidak ada promo, harga mengikuti katalog layanan yang tampil.',
                    'actionLabel' => 'Lihat Layanan',
                    'actionUrl' => route('public.services'),
                ],
                'fallback' => [
                    'text' => 'Saya belum menangkap topiknya. Coba tulis seperti status membership, booking kelas, transaksi, QR member, atau profil.',
                ],
            ],
        ];
    }

    private static function hasGeminiKeys(): bool
    {
        return (bool) config('services.gemini.enabled', true)
            && (filled(config('services.gemini.api_key')) || filled(config('services.gemini.api_keys')));
    }
}
