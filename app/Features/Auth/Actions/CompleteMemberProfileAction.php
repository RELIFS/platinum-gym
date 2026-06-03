<?php

namespace App\Features\Auth\Actions;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompleteMemberProfileAction
{
    /**
     * @param  array{phone:string,gender:string,birth_date:string}  $data
     */
    public function execute(User $user, array $data): Member
    {
        return DB::transaction(function () use ($user, $data) {
            $user->forceFill(['phone' => $data['phone']])->save();

            return Member::create([
                'user_id' => $user->id,
                'member_code' => Member::generateMemberCode(),
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
                'joined_at' => now()->toDateString(),
                'status' => 'active',
            ]);
        });
    }
}
