<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class AcceptAccountInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return [
            'password' => 'kata sandi',
        ];
    }
}
