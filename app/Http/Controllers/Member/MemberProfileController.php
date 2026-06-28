<?php

namespace App\Http\Controllers\Member;

use App\Features\Auth\Actions\SendEmailVerificationCodeAction;
use App\Features\MemberPortal\Actions\UpdateMemberProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\UpdateMemberProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class MemberProfileController extends Controller
{
    public function studentProof(Request $request): Response
    {
        $member = $request->user()->member()->firstOrFail();
        $path = (string) $member->student_proof_path;
        $disk = Storage::disk('local');

        abort_unless($path !== '' && $disk->exists($path), 404);

        $mime = $disk->mimeType($path) ?: 'image/jpeg';

        return response($disk->get($path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="bukti-mahasiswa"',
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function update(UpdateMemberProfileRequest $request, UpdateMemberProfileAction $updateMemberProfile, SendEmailVerificationCodeAction $sendVerificationCode): RedirectResponse
    {
        $emailChanged = $request->user()->email !== $request->validated('email');

        $updateMemberProfile->execute($request->user(), $request->user()->member()->firstOrFail(), $request->validated());

        if ($emailChanged) {
            $sendVerificationCode->handle($request->user());

            return redirect()->route('verification.notice')->with('status', 'verification-code-sent');
        }

        return redirect()->route('member.profile')->with('status', 'Profil member berhasil diperbarui.');
    }
}
