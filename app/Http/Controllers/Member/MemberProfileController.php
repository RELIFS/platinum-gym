<?php

namespace App\Http\Controllers\Member;

use App\Features\MemberPortal\Actions\UpdateMemberProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\UpdateMemberProfileRequest;
use Illuminate\Http\RedirectResponse;

class MemberProfileController extends Controller
{
    public function update(UpdateMemberProfileRequest $request, UpdateMemberProfileAction $updateMemberProfile): RedirectResponse
    {
        $emailChanged = $request->user()->email !== $request->validated('email');

        $updateMemberProfile->execute($request->user(), $request->user()->member()->firstOrFail(), $request->validated());

        if ($emailChanged) {
            return redirect()->route('verification.notice')->with('status', 'Profil member berhasil diperbarui. Verifikasi email baru untuk membuka kembali akses penuh.');
        }

        return redirect()->route('member.profile')->with('status', 'Profil member berhasil diperbarui.');
    }
}
