<?php

namespace App\Features\MemberPortal\Support;

class StudentVerificationResult
{
    private function __construct(
        public readonly string $status,
        public readonly string $source,
        public readonly ?string $note = null,
    ) {}

    public static function verified(string $source = 'pddikti', ?string $note = null): self
    {
        return new self('verified', $source, $note ?? 'Data mahasiswa terverifikasi.');
    }

    public static function pending(string $source = 'manual', ?string $note = null): self
    {
        return new self('pending_review', $source, $note ?? 'Menunggu review admin.');
    }

    public static function failed(string $source = 'pddikti', ?string $note = null): self
    {
        return new self('failed', $source, $note ?? 'Data mahasiswa tidak cocok.');
    }

    public static function unverified(?string $note = null): self
    {
        return new self('unverified', 'profile', $note ?? 'Belum diverifikasi.');
    }
}
