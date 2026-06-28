<?php

namespace App\Features\MemberPortal\Actions;

use App\Features\MemberPortal\Contracts\StudentVerificationGateway;
use App\Features\MemberPortal\Support\StudentVerificationResult;
use App\Models\Member;
use App\Models\User;

class VerifyStudentStatusAction
{
    public function __construct(private readonly StudentVerificationGateway $gateway) {}

    public function handle(User $user, Member $member): StudentVerificationResult
    {
        if (! $member->is_student) {
            return StudentVerificationResult::unverified('Member bukan mahasiswa.');
        }

        if (blank($member->student_id_number)) {
            return StudentVerificationResult::unverified('NIM belum diisi.');
        }

        return $this->gateway->verify($member, (string) $user->name, (string) $member->student_id_number);
    }
}
