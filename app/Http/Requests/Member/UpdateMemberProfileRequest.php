<?php

namespace App\Http\Requests\Member;

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
        $this->merge([
            'email' => str($this->input('email'))->lower()->trim()->toString(),
            'phone' => NormalizeIndonesianPhone::toLocalMobile($this->input('phone')),
            'emergency_contact' => NormalizeIndonesianPhone::toLocalMobile($this->input('emergency_contact')),
            'is_student' => $this->boolean('is_student'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()?->id)],
            'phone' => ['required', 'string', 'regex:/^08\d{8,12}$/', Rule::unique(User::class, 'phone')->ignore($this->user()?->id)],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'birth_date' => ['required', 'date', 'before:today', 'after_or_equal:1940-01-01'],
            'address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact' => ['nullable', 'string', 'regex:/^08\d{8,12}$/'],
            'is_student' => ['boolean'],
            'student_id_number' => ['nullable', Rule::requiredIf($this->boolean('is_student')), 'string', 'max:50'],
            'height_cm' => ['nullable', 'integer', 'between:100,250'],
            'weight_kg' => ['nullable', 'numeric', 'between:30,250'],
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
            'address' => 'alamat',
            'emergency_contact' => 'kontak darurat',
            'student_id_number' => 'nomor identitas mahasiswa',
            'height_cm' => 'tinggi badan',
            'weight_kg' => 'berat badan',
        ];
    }
}
