<?php

namespace App\Features\MemberPortal\Actions;

use App\Models\Member;
use App\Models\QrToken;
use App\Support\QrPngRenderer;
use RuntimeException;

class DownloadMemberQrAction
{
    public function __construct(private readonly QrPngRenderer $renderer) {}

    /**
     * @return array{filename: string, content: string, mime: string}
     */
    public function handle(Member $member): array
    {
        $member->loadMissing('user');

        $qrToken = QrToken::query()
            ->where('tokenable_type', Member::class)
            ->where('tokenable_id', $member->id)
            ->where('purpose', 'member')
            ->latest('created_at')
            ->first();

        if (! $qrToken || $qrToken->is_revoked || $qrToken->expires_at?->isPast()) {
            throw new RuntimeException('QR member belum aktif untuk diunduh.');
        }

        $hasActiveMembership = $member->memberships()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->exists();

        if (! $hasActiveMembership) {
            throw new RuntimeException('Membership aktif diperlukan sebelum mengunduh QR.');
        }

        $png = $this->renderer->render($qrToken->token, 640);

        if (blank($png)) {
            throw new RuntimeException('QR member belum dapat dibuat. Coba lagi nanti.');
        }

        $filenameBase = filled($member->user?->name)
            ? (string) $member->user->name
            : (string) ($member->member_code ?: 'member');

        $filename = str($filenameBase)
            ->slug('-')
            ->prepend('qr-member-')
            ->append('.png')
            ->toString();

        return ['filename' => $filename, 'content' => $png, 'mime' => 'image/png'];
    }
}
