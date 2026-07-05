<?php

namespace App\Features\Gymmi\Support;

class GymmiIntentDetector
{
    public function __construct(
        private readonly GymmiTextNormalizer $normalizer,
    ) {}

    /**
     * @return array{intent: string, subject: string|null, normalized: string}
     */
    public function detect(string $message): array
    {
        $normalized = $this->normalize($message);
        $subject = $this->classSubject($normalized);
        $intent = 'general';

        if ($subject && $this->hasAny($normalized, ['sendiri', 'privat', 'private', 'hanya saya', 'cuma saya', 'ada orang lain', 'bareng', 'peserta lain', 'satu coach', 'satu member'])) {
            $intent = 'private_or_group';
        } elseif ($subject && $this->hasAny($normalized, ['kapasitas', 'kuota', 'berapa orang', 'peserta'])) {
            $intent = 'class_capacity';
        } elseif ($subject && $this->hasAny($normalized, ['harga', 'biaya', 'tarif', 'bayar'])) {
            $intent = 'class_price';
        } elseif ($subject && $this->hasAny($normalized, ['coach', 'trainer', 'pelatih', 'dilatih'])) {
            $intent = 'class_coach';
        } elseif ($subject || $this->hasAny($normalized, ['jadwal', 'kelas', 'senam'])) {
            $intent = 'class_schedule';
        } elseif ($this->hasAny($normalized, ['alamat', 'lokasi', 'dimana', 'di mana', 'arah', 'rute', 'maps', 'google maps', 'wa', 'whatsapp', 'kontak', 'instagram', 'ig', 'jam buka', 'operasional'])) {
            $intent = 'location_contact';
        } elseif ($this->hasAny($normalized, ['harga', 'biaya', 'paket', 'membership', 'member', 'gym', 'pt', 'personal trainer', 'sesi'])) {
            $intent = 'membership_price';
        } elseif ($this->hasAny($normalized, ['produk', 'stok', 'minuman', 'makanan', 'suplemen', 'protein', 'jual', 'beli', 'wrap', 'sarung'])) {
            $intent = 'product_stock';
        }

        return compact('intent', 'subject', 'normalized');
    }

    /**
     * @return array<int, string>
     */
    public function subjectAliases(?string $subject): array
    {
        return match ($subject) {
            'muaythai' => ['muaythai', 'muay thai', 'muaytai', 'muay tay', 'muay tai', 'muaytay', 'muay'],
            'aerobic' => ['aerobic', 'aerobik'],
            'poundfit' => ['poundfit', 'pound fit'],
            'zumba' => ['zumba'],
            default => [],
        };
    }

    private function classSubject(string $message): ?string
    {
        foreach (['muaythai', 'aerobic', 'poundfit', 'zumba'] as $subject) {
            if ($this->hasAny($message, $this->subjectAliases($subject))) {
                return $subject;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $needles
     */
    private function hasAny(string $message, array $needles): bool
    {
        foreach ($needles as $needle) {
            $needle = $this->normalize($needle);

            if ($needle !== '' && str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return $this->normalizer->normalize($value);
    }
}
