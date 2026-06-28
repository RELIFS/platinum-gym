<?php

namespace App\Http\Controllers\Admin;

use App\Features\Auth\Actions\SendAccountInvitationAction;
use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminMemberInvitationController extends Controller
{
    public function __invoke(Request $request, Member $member, SendAccountInvitationAction $sendInvitation): RedirectResponse
    {
        abort_unless($request->user()?->can('manage_members'), 403);

        $user = $member->user()->firstOrFail();
        $sendInvitation->handle($user, $request->user());

        return back()->with('status', 'Undangan aktivasi akun berhasil dikirim ulang.');
    }
}
