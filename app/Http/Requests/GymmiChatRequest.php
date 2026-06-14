<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GymmiChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'message' => trim((string) $this->input('message')),
            'context' => $this->input('context', 'public'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:700'],
            'context' => ['nullable', Rule::in(['public', 'member'])],
            'history' => ['nullable', 'array', 'max:8'],
            'history.*.from' => ['nullable', Rule::in(['user', 'bot'])],
            'history.*.text' => ['nullable', 'string', 'max:700'],
        ];
    }
}
