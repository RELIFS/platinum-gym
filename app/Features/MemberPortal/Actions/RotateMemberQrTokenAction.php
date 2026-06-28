<?php

namespace App\Features\MemberPortal\Actions;

use App\Models\Member;
use App\Models\QrToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RotateMemberQrTokenAction
{
    public function handle(Member $member, ?int $createdBy = null): QrToken
    {
        return DB::transaction(function () use ($member, $createdBy): QrToken {
            QrToken::query()
                ->where('tokenable_type', Member::class)
                ->where('tokenable_id', $member->id)
                ->where('purpose', 'member')
                ->where('is_revoked', false)
                ->lockForUpdate()
                ->update(['is_revoked' => true]);

            return QrToken::create([
                'tokenable_type' => Member::class,
                'tokenable_id' => $member->id,
                'token' => hash('sha256', $member->member_code.'|'.Str::random(48).'|'.microtime(true)),
                'purpose' => 'member',
                'created_by' => $createdBy,
            ]);
        });
    }
}
