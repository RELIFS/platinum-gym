<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $phone = preg_replace('/\D+/', '', (string) $request->input('phone'));

        if (str_starts_with($phone, '62')) {
            $phone = '0'.substr($phone, 2);
        }

        $request->merge(['phone' => $phone]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'phone' => ['required', 'string', 'regex:/^08\d{8,12}$/', Rule::unique(User::class, 'phone')],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            $user->assignRole(Role::findOrCreate('member', 'web'));

            Member::create([
                'user_id' => $user->id,
                'member_code' => Member::generateMemberCode(),
                'gender' => $request->gender,
                'birth_date' => $request->birth_date,
                'joined_at' => now()->toDateString(),
                'status' => 'active',
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('verification.notice', absolute: false));
    }
}
