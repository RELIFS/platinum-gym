<?php

namespace App\Features\Auth\Actions;

use App\Models\AccountInvitation;
use App\Models\User;
use App\Notifications\Auth\MemberAccountInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendAccountInvitationAction
{
    public function handle(User $user, ?User $createdBy = null): AccountInvitation
    {
        return DB::transaction(function () use ($user, $createdBy): AccountInvitation {
            AccountInvitation::query()
                ->where('user_id', $user->id)
                ->whereNull('accepted_at')
                ->delete();

            $plainToken = Str::random(64);

            $invitation = AccountInvitation::create([
                'user_id' => $user->id,
                'created_by' => $createdBy?->id,
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => now()->addHours(72),
                'sent_at' => now(),
            ]);

            $user->notify((new MemberAccountInvitationNotification(
                acceptUrl: route('account-invitations.accept', $plainToken),
                expiresAt: $invitation->expires_at,
            ))->afterCommit());

            return $invitation;
        });
    }
}
