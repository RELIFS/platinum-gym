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
    public function __construct(private readonly VerifyStudentStatusAction $verifyStudentStatus) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Member $member, array $data): void
    {
        $newAvatar = $this->storeAvatar($data['avatar'] ?? null);
        $oldAvatar = $user->avatar;

        try {
            DB::transaction(function () use ($user, $member, $data, $newAvatar): void {
                $oldStudentSnapshot = [
                    'name' => $user->name,
                    'birth_date' => $member->birth_date?->toDateString(),
                    'is_student' => (bool) $member->is_student,
                    'student_id_number' => (string) ($member->student_id_number ?? ''),
                ];

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

                $member->fill([
                    'gender' => $data['gender'],
                    'birth_date' => $data['birth_date'],
                    'address' => $data['address'] ?? null,
                    'emergency_contact' => $data['emergency_contact'] ?? null,
                    'is_student' => (bool) ($data['is_student'] ?? false),
                    'student_id_number' => ($data['is_student'] ?? false) ? ($data['student_id_number'] ?? null) : null,
                ]);

                if ($this->studentDataChanged($oldStudentSnapshot, $user, $member)) {
                    $result = $this->verifyStudentStatus->handle($user, $member);
                    $member->forceFill([
                        'student_verification_status' => $result->status,
                        'student_verified_at' => $result->status === 'verified' ? now() : null,
                        'student_verification_source' => $result->source,
                        'student_verification_note' => $result->note,
                    ]);
                }

                $member->save();
            });
        } catch (Throwable $exception) {
            $this->deleteLocalAvatar($newAvatar);

            throw $exception;
        }

        if ($newAvatar) {
            $this->deleteLocalAvatar($oldAvatar);
        }
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

    /** @param array{name: string|null, birth_date: string|null, is_student: bool, student_id_number: string} $old */
    private function studentDataChanged(array $old, User $user, Member $member): bool
    {
        return $old['name'] !== $user->name
            || $old['birth_date'] !== $member->birth_date?->toDateString()
            || $old['is_student'] !== (bool) $member->is_student
            || $old['student_id_number'] !== (string) ($member->student_id_number ?? '');
    }
}
