<?php

namespace App\Features\Gymmi\Support;

class GymmiFallbackResponder
{
    public function __construct(
        private readonly GymmiKnowledgeRepository $knowledge,
        private readonly GymmiResponseFormatter $formatter,
    ) {}

    public function ambiguous(string $context = 'public'): string
    {
        if ($context === 'member') {
            return 'Boleh diperjelas topiknya? Contoh: status membership, jadwal booking, transaksi, QR member, profil, atau bantuan akun.';
        }

        return 'Boleh diperjelas topiknya? Contoh: harga Gym Umum, jadwal Muaythai, lokasi gym, metode pembayaran, atau fasilitas yang tersedia.';
    }

    public function outOfScope(string $context = 'public'): string
    {
        if ($context === 'member') {
            return 'Saya belum menangkap topiknya. Coba tulis seperti status membership, booking kelas, transaksi, QR member, atau profil.';
        }

        return 'Saya belum menangkap topiknya. Coba tulis seperti harga Gym Umum, jadwal Muaythai, lokasi gym, atau metode pembayaran.';
    }

    /**
     * @param  array{type: string, answer: string|null, snippets: array<int, string>, topic: string|null, confidence: int}  $match
     */
    public function fromMatch(array $match): string
    {
        return $this->formatter->snippetReply(
            $match['snippets'] ?? [],
            $this->escalation()
        );
    }

    public function providerUnavailable(): string
    {
        return $this->escalation('Saya belum bisa merangkai jawaban otomatis saat ini.');
    }

    private function escalation(string $prefix = 'Saya belum menemukan jawaban yang cukup akurat.'): string
    {
        $whatsapp = $this->knowledge->configValue('whatsapp');

        return $prefix.' Silakan tanyakan topik yang lebih spesifik, buka halaman layanan/kelas/produk/lokasi, atau hubungi admin'.($whatsapp ? ' di WhatsApp '.$whatsapp : '').'.';
    }
}
