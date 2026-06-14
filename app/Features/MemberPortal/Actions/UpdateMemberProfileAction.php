<?php

namespace App\Features\MemberPortal\Actions;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateMemberProfileAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Member $member, array $data): void
    {
        DB::transaction(function () use ($user, $member, $data): void {
            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            $member->fill([
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
                'address' => $data['address'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'is_student' => (bool) ($data['is_student'] ?? false),
                'student_id_number' => ($data['is_student'] ?? false) ? ($data['student_id_number'] ?? null) : null,
                'height_cm' => $data['height_cm'] ?? null,
                'weight_kg' => $data['weight_kg'] ?? null,
            ])->save();
        });
    }
}
