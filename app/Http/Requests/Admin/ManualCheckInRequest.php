<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ManualCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('scan_qr') ?? false;
    }

    public function rules(): array
    {
        return ['member_id' => ['required', 'exists:members,id']];
    }
}
