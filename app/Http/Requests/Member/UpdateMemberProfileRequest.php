<?php

namespace App\Http\Requests\Member;

use App\Features\Shared\Support\ComposeBirthDate;
use App\Features\Shared\Support\NormalizeIndonesianPhone;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasRole('member') ?? false)
            && $this->user()?->member()->exists();
    }

    protected function prepareForValidation(): void
    {
        $payload = [
            'email' => str($this->input('email'))->lower()->trim()->toString(),
            'phone' => NormalizeIndonesianPhone::toLocalMobile($this->input('phone')),
            'emergency_contact' => NormalizeIndonesianPhone::toLocalMobile($this->input('emergency_contact')),
            'is_student' => $this->boolean('is_student'),
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
        $hasStudentProof = filled($this->user()?->member()->value('student_proof_path'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()?->id)],
            'phone' => ['required', 'string', 'regex:/^08\d{8,12}$/', Rule::unique(User::class, 'phone')->ignore($this->user()?->id)],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'birth_date' => ['required', 'date', 'before:today', 'after_or_equal:1940-01-01'],
            'birth_date_display' => ['nullable', 'string', 'max:10'],
            'birth_day' => ['nullable', 'integer', 'between:1,31'],
            'birth_month' => ['nullable', 'integer', 'between:1,12'],
            'birth_year' => ['nullable', 'integer', 'between:1940,'.now()->year],
            'address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact' => ['nullable', 'string', 'regex:/^08\d{8,12}$/'],
            'is_student' => ['boolean'],
            'student_proof' => ['nullable', Rule::requiredIf($this->boolean('is_student') && ! $hasStudentProof), 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'nomor WhatsApp',
            'gender' => 'gender',
            'birth_date' => 'tanggal lahir',
            'birth_date_display' => 'tanggal lahir',
            'birth_day' => 'hari lahir',
            'birth_month' => 'bulan lahir',
            'birth_year' => 'tahun lahir',
            'address' => 'alamat',
            'emergency_contact' => 'kontak darurat',
            'student_proof' => 'bukti mahasiswa',
            'avatar' => 'foto profil',
        ];
    }
}
