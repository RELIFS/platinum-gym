<?php

namespace App\Http\Controllers\Admin;

use App\Features\Admin\Actions\ReviewMemberStudentProofAction;
use App\Features\Admin\Queries\AdminDashboardQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewMemberStudentProofRequest;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class AdminStudentProofReviewController extends Controller
{
    public function show(Request $request, Member $member, AdminDashboardQuery $query): View
    {
        abort_unless($request->user()?->can('manage_members'), 403);

        $member->loadMissing('user');
        $portal = $query->forUser($request->user(), [], 'members');

        return view('admin.members.student-proof-review', [
            'portal' => $portal,
            'navigation' => $query->navigation((int) ($portal['pendingApprovalCount'] ?? 0)),
            'member' => $member,
            'title' => 'Review Bukti Mahasiswa',
        ]);
    }

    public function proof(Request $request, Member $member): Response
    {
        abort_unless($request->user()?->can('manage_members'), 403);

        $path = (string) $member->student_proof_path;
        $disk = Storage::disk('local');

        abort_unless($path !== '' && str_starts_with($path, 'member/student-proofs/') && $disk->exists($path), 404);

        $mime = $disk->mimeType($path) ?: 'image/jpeg';

        return response($disk->get($path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="bukti-mahasiswa"',
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function approve(ReviewMemberStudentProofRequest $request, Member $member, ReviewMemberStudentProofAction $action): RedirectResponse
    {
        $action->approve($member, $request->user(), $request->validated('note'));

        return redirect()
            ->route('admin.members.student-proof.review', $member)
            ->with('status', 'Bukti mahasiswa disetujui.');
    }

    public function reject(ReviewMemberStudentProofRequest $request, Member $member, ReviewMemberStudentProofAction $action): RedirectResponse
    {
        $action->reject($member, $request->user(), $request->validated('note'));

        return redirect()
            ->route('admin.members.student-proof.review', $member)
            ->with('status', 'Bukti mahasiswa ditolak.');
    }
}
