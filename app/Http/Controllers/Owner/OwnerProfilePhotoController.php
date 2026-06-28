<?php

namespace App\Http\Controllers\Owner;

use App\Features\OwnerPortal\Actions\UpdateOwnerProfilePhotoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdateOwnerProfilePhotoRequest;
use Illuminate\Http\RedirectResponse;

class OwnerProfilePhotoController extends Controller
{
    public function __invoke(UpdateOwnerProfilePhotoRequest $request, UpdateOwnerProfilePhotoAction $updateOwnerProfilePhoto): RedirectResponse
    {
        $updateOwnerProfilePhoto->execute($request->user(), $request->file('avatar'));

        return redirect()->route('profile.edit')->with('status', 'owner-photo-updated');
    }
}
