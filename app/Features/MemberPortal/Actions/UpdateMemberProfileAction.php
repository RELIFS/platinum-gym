<?php

namespace App\Features\MemberPortal\Actions;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class UpdateMemberProfileAction
{
    private const STUDENT_PROOF_DIRECTORY = 'member/student-proofs';

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Member $member, array $data): void
    {
        $newAvatar = $this->storeAvatar($data['avatar'] ?? null);
        $newStudentProof = $this->storeStudentProof(($data['is_student'] ?? false) ? ($data['student_proof'] ?? null) : null);
        $oldAvatar = $user->avatar;
        $oldStudentProof = $member->student_proof_path;
        $studentProofToDelete = null;

        try {
            DB::transaction(function () use ($user, $member, $data, $newAvatar, $newStudentProof, $oldStudentProof, &$studentProofToDelete): void {
                $payload = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                ];

                if ($newAvatar) {
                    $payload['avatar'] = $newAvatar;
                }

                $user->fill($payload);

                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }

                $user->save();

                $isStudent = (bool) ($data['is_student'] ?? false);

                $member->fill([
                    'gender' => $data['gender'],
                    'birth_date' => $data['birth_date'],
                    'address' => $data['address'] ?? null,
                    'emergency_contact' => $data['emergency_contact'] ?? null,
                    'is_student' => $isStudent,
                    'student_id_number' => $isStudent ? $member->student_id_number : null,
                ]);

                if ($newStudentProof) {
                    $member->forceFill([
                        'student_proof_path' => $newStudentProof,
                        'student_proof_uploaded_at' => now(),
                        'student_verification_status' => 'pending_review',
                        'student_verified_at' => null,
                        'student_verification_source' => 'member_upload',
                        'student_verification_note' => 'Bukti mahasiswa telah diunggah dan siap ditinjau admin.',
                    ]);

                    $studentProofToDelete = $oldStudentProof;
                }

                if (! $isStudent) {
                    $member->forceFill([
                        'student_proof_path' => null,
                        'student_proof_uploaded_at' => null,
                        'student_verification_status' => 'unverified',
                        'student_verified_at' => null,
                        'student_verification_source' => 'profile',
                        'student_verification_note' => 'Member bukan mahasiswa.',
                    ]);

                    $studentProofToDelete = $oldStudentProof;
                }

                $member->save();
            });
        } catch (Throwable $exception) {
            $this->deleteLocalAvatar($newAvatar);
            $this->deleteStudentProof($newStudentProof);

            throw $exception;
        }

        if ($newAvatar) {
            $this->deleteLocalAvatar($oldAvatar);
        }

        $this->deleteStudentProof($studentProofToDelete);
    }

    private function storeAvatar(mixed $avatar): ?string
    {
        if (! $avatar instanceof UploadedFile) {
            return null;
        }

        $path = Storage::disk('public')->putFile('member/avatars', $avatar);

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Foto profil belum dapat disimpan.');
        }

        return 'storage/'.$path;
    }

    private function deleteLocalAvatar(?string $avatar): void
    {
        if (! $avatar || ! str_starts_with($avatar, 'storage/member/avatars/')) {
            return;
        }

        Storage::disk('public')->delete(substr($avatar, strlen('storage/')));
    }

    private function storeStudentProof(mixed $proof): ?string
    {
        if (! $proof instanceof UploadedFile) {
            return null;
        }

        $path = Storage::disk('local')->putFile(self::STUDENT_PROOF_DIRECTORY, $proof);

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Bukti mahasiswa belum dapat disimpan.');
        }

        return $path;
    }

    private function deleteStudentProof(?string $path): void
    {
        if (! $path || ! str_starts_with($path, self::STUDENT_PROOF_DIRECTORY.'/')) {
            return;
        }

        Storage::disk('local')->delete($path);
    }
}
