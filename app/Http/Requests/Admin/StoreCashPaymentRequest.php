<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('input_cash_payments') ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'exists:members,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'trainer_id' => ['nullable', 'exists:trainers,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'member_id' => 'member',
            'package_id' => 'paket',
            'trainer_id' => 'trainer',
            'note' => 'catatan',
        ];
    }
}
