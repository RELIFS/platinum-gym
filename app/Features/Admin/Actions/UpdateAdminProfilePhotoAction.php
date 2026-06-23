<?php

namespace App\Features\Admin\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class UpdateAdminProfilePhotoAction
{
    public function execute(User $user, UploadedFile $avatar): void
    {
        $newAvatar = $this->storeAvatar($avatar);
        $oldAvatar = $user->avatar;

        try {
            $user->forceFill([
                'avatar' => $newAvatar,
            ])->save();
        } catch (Throwable $exception) {
            $this->deleteLocalAvatar($newAvatar);

            throw $exception;
        }

        $this->deleteLocalAvatar($oldAvatar);
    }

    private function storeAvatar(UploadedFile $avatar): string
    {
        $path = Storage::disk('public')->putFile('admin/avatars', $avatar);

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Foto profil admin belum dapat disimpan.');
        }

        return 'storage/'.$path;
    }

    private function deleteLocalAvatar(?string $avatar): void
    {
        if (! $avatar || ! str_starts_with($avatar, 'storage/admin/avatars/')) {
            return;
        }

        Storage::disk('public')->delete(substr($avatar, strlen('storage/')));
    }
}
