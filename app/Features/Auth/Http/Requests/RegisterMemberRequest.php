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
            $payload['birth_date'] = ComposeBirthDate::fromDisplay($this->input('birth_date_display')) ?? 'invalid-date';
        }

        if ($this->hasAny(['birth_day', 'birth_month', 'birth_year'])) {
            $payload['birth_date'] = ComposeBirthDate::fromParts(
                $this->input('birth_year'),
                $this->input('birth_month'),
                $this->input('birth_day'),
            ) ?? 'invalid-date';
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

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'birth_date' => 'tanggal lahir',
            'birth_date_display' => 'tanggal lahir',
            'birth_day' => 'tanggal lahir',
            'birth_month' => 'tanggal lahir',
            'birth_year' => 'tanggal lahir',
            'gender' => 'jenis kelamin',
            'phone' => 'No. WhatsApp',
            'email' => 'alamat email',
            'password' => 'kata sandi',
            'password_confirmation' => 'konfirmasi kata sandi',
            'terms' => 'persetujuan syarat dan ketentuan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.string' => 'Nama lengkap harus berupa teks.',
            'name.max' => 'Nama lengkap maksimal :max karakter.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.date' => 'Tanggal lahir belum valid. Gunakan format dd/mm/yyyy.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'birth_date_display.string' => 'Tanggal lahir harus berupa teks.',
            'birth_date_display.max' => 'Tanggal lahir maksimal :max karakter.',
            'birth_day.integer' => 'Tanggal lahir belum valid. Gunakan format dd/mm/yyyy.',
            'birth_day.between' => 'Tanggal lahir belum valid. Gunakan format dd/mm/yyyy.',
            'birth_month.integer' => 'Tanggal lahir belum valid. Gunakan format dd/mm/yyyy.',
            'birth_month.between' => 'Tanggal lahir belum valid. Gunakan format dd/mm/yyyy.',
            'birth_year.integer' => 'Tanggal lahir belum valid. Gunakan format dd/mm/yyyy.',
            'birth_year.between' => 'Tanggal lahir belum valid. Gunakan format dd/mm/yyyy.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Pilih jenis kelamin yang tersedia.',
            'phone.required' => 'No. WhatsApp wajib diisi.',
            'phone.string' => 'No. WhatsApp harus berupa teks.',
            'phone.regex' => 'Gunakan format No. WhatsApp 08xxxxxxxxxx.',
            'phone.unique' => 'No. WhatsApp sudah terdaftar.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.string' => 'Alamat email harus berupa teks.',
            'email.email' => 'Gunakan alamat email yang valid.',
            'email.max' => 'Alamat email maksimal :max karakter.',
            'email.unique' => 'Alamat email sudah terdaftar. Silakan masuk atau gunakan email lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi belum sama.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'terms.accepted' => 'Anda perlu menyetujui Syarat & Ketentuan dan Kebijakan Privasi.',
        ];
    }
}
