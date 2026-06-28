<?php

namespace App\Features\MemberPortal\Contracts;

use App\Features\MemberPortal\Support\StudentVerificationResult;
use App\Models\Member;

interface StudentVerificationGateway
{
    public function verify(Member $member, string $memberName, string $studentIdNumber): StudentVerificationResult;
}
