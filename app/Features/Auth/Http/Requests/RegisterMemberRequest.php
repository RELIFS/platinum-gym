<?php

namespace App\Features\Auth\Http\Requests;

use App\Features\Shared\Support\ComposeBirthDate;
use App\Features\Shared\Support\NormalizeIndonesianPhone;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisterMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $payload = [
            'phone' => NormalizeIndonesianPhone::toLocalMobile($this->input('phone')),
        ];

        if (blank($this->input('birth_date')) && filled($this->input('birth_date_display'))) {
            $payload['birth_date'] = ComposeBirthDate::fromDisplay($this->input('birth_date_display'));
        }

        if ($this->hasAny(['birth_day', 'birth_month', 'birth_year'])) {
            $payload['birth_date'] = ComposeBirthDate::fromParts(
                $this->input('birth_year'),
                $this->input('birth_month'),
                $this->input('birth_day'),
            );
        }

        $this->merge($payload);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'birth_date_display' => ['nullable', 'string', 'max:10'],
            'birth_day' => ['nullable', 'integer', 'between:1,31'],
            'birth_month' => ['nullable', 'integer', 'between:1,12'],
            'birth_year' => ['nullable', 'integer', 'between:1940,'.now()->year],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'phone' => ['required', 'string', 'regex:/^08\d{8,12}$/', Rule::unique(User::class, 'phone')],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ];
    }
}
