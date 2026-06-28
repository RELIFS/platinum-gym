<?php

namespace App\Http\Controllers\Admin;

use App\Features\Admin\Actions\UpdateAdminProfilePhotoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminProfilePhotoRequest;
use Illuminate\Http\RedirectResponse;

class AdminProfilePhotoController extends Controller
{
    public function __invoke(UpdateAdminProfilePhotoRequest $request, UpdateAdminProfilePhotoAction $updateAdminProfilePhoto): RedirectResponse
    {
        $updateAdminProfilePhoto->execute($request->user(), $request->file('avatar'));

        return redirect()->route('admin.profile')->with('status', 'admin-photo-updated');
    }
}
