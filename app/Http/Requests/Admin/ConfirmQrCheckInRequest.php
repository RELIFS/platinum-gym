<?php

namespace App\Http\Requests\Admin;

use App\Features\CheckIns\Actions\ConfirmMemberQrCheckInAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmQrCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('scan_qr') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'preview_key' => ['required', 'string', 'max:80'],
            'action' => ['required', Rule::in([
                ConfirmMemberQrCheckInAction::CHECK_IN_MEMBERSHIP,
                ConfirmMemberQrCheckInAction::USE_PACKAGE_SESSION,
                ConfirmMemberQrCheckInAction::CHECK_IN_AND_USE_SESSION,
            ])],
            'member_package_session_id' => ['nullable', 'integer', 'exists:member_package_sessions,id'],
            'class_enrollment_id' => ['nullable', 'integer', 'exists:class_enrollments,id'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function ($validator): void {
                if (in_array($this->input('action'), [ConfirmMemberQrCheckInAction::USE_PACKAGE_SESSION, ConfirmMemberQrCheckInAction::CHECK_IN_AND_USE_SESSION], true)
                    && blank($this->input('member_package_session_id'))) {
                    $validator->errors()->add('member_package_session_id', 'Pilih paket sesi yang akan digunakan.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'preview_key' => 'pratinjau check-in',
            'action' => 'aksi check-in',
            'member_package_session_id' => 'paket sesi',
            'class_enrollment_id' => 'booking kelas',
        ];
    }
}
