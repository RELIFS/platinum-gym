<?php

namespace App\Features\Gymmi\Support;

class GymmiFollowUpResolver
{
    public function __construct(
        private readonly GymmiTextNormalizer $normalizer,
    ) {}

    /**
     * @param  array<string, mixed>|null  $focus
     * @return array{message: string, follow_up: bool}
     */
    public function resolve(string $message, ?array $focus): array
    {
        $normalized = $this->normalizer->normalize($message);

        if (! is_array($focus) || ! $this->isElliptical($normalized)) {
            return ['message' => $normalized, 'follow_up' => false];
        }

        $subject = trim((string) ($focus['subject'] ?? ''));
        $intent = trim((string) ($focus['intent'] ?? ''));
        $entities = is_array($focus['entities'] ?? null) ? $focus['entities'] : [];
        $entityTopic = collect([
            $entities['topic'] ?? null,
        ])->filter()->implode(' ');
        $topic = trim(collect([$subject, $entityTopic, $this->intentTopic($intent)])->filter()->unique()->implode(' '));

        if ($topic === '') {
            return ['message' => $normalized, 'follow_up' => false];
        }

        return [
            'message' => trim($normalized.' '.$topic),
            'follow_up' => true,
        ];
    }

    private function isElliptical(string $message): bool
    {
        if ($message === '' || str_word_count($message) > 7) {
            return false;
        }

        return preg_match('/\b(yang|kalau|coachnya|trainernya|jadwalnya|harganya|berapa|kapan|siapa|3 bulan|tiga bulan|4x|5x|10x|24x)\b/u', $message) === 1;
    }

    private function intentTopic(string $intent): string
    {
        return match ($intent) {
            'membership_price', 'class_price' => 'harga paket',
            'class_schedule' => 'jadwal kelas',
            'class_coach' => 'coach kelas',
            'class_capacity', 'private_or_group' => 'kelas',
            'product_stock' => 'produk',
            'member_membership' => 'membership saya',
            'member_session' => 'paket sesi saya',
            'member_payment' => 'transaksi saya',
            'member_booking' => 'booking saya',
            'member_qr' => 'qr saya',
            default => '',
        };
    }
}
