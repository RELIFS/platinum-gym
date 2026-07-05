<?php

namespace App\Features\Admin\Actions;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewMemberStudentProofAction
{
    public function approve(Member $member, User $admin, ?string $note = null): void
    {
        $this->review($member, $admin, 'verified', $note ?: 'Bukti mahasiswa disetujui admin.');
    }

    public function reject(Member $member, User $admin, ?string $note = null): void
    {
        $this->review($member, $admin, 'failed', $note ?: 'Bukti mahasiswa ditolak admin.');
    }

    private function review(Member $member, User $admin, string $status, string $note): void
    {
        DB::transaction(function () use ($member, $admin, $status, $note): void {
            $member = Member::query()
                ->whereKey($member->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($member->is_student && filled($member->student_proof_path), 422, 'Bukti mahasiswa belum tersedia untuk ditinjau.');

            $member->forceFill([
                'student_verification_status' => $status,
                'student_verified_at' => $status === 'verified' ? now() : null,
                'student_verification_source' => 'admin',
                'student_verification_note' => $note,
            ])->save();

            activity()
                ->causedBy($admin)
                ->performedOn($member)
                ->event('student-proof-reviewed')
                ->withProperties(['status' => $status])
                ->log('Bukti mahasiswa ditinjau admin.');
        });
    }
}
