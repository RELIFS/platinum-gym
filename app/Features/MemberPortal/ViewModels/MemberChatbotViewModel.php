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
                    'text' => 'Saya belum menemukan topik yang cocok. Pilih salah satu bantuan cepat atau buka dashboard member untuk melihat ringkasan akun.',
                    'actionLabel' => 'Buka Dashboard',
                    'actionUrl' => route('member.dashboard'),
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
