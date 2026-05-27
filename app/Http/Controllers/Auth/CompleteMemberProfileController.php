<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompleteMemberProfileController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->member()->exists()) {
            return redirect()->route('member.dashboard');
        }

        return view('auth.complete-profile');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->member()->exists()) {
            return redirect()->route('member.dashboard');
        }

        $phone = $this->normalizePhone((string) $request->input('phone'));
        $request->merge(['phone' => $phone]);

        $validated = $request->validate([
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'phone' => ['required', 'string', 'regex:/^08\d{8,12}$/', Rule::unique(User::class, 'phone')->ignore($user->id)],
            'terms' => ['accepted'],
        ]);

        $user->forceFill(['phone' => $validated['phone']])->save();

        Member::create([
            'user_id' => $user->id,
            'member_code' => Member::generateMemberCode(),
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'joined_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        return redirect()->route('member.dashboard')->with('status', __('Profil member berhasil dilengkapi.'));
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($phone, '62')) {
            return '0'.substr($phone, 2);
        }

        return $phone;
    }
}
