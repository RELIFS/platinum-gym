<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:700'],
            'conversation_id' => ['nullable', 'string', 'size:64', 'regex:/^[A-Za-z0-9]+$/'],
            'client_message_id' => ['required', 'uuid'],
            'context' => ['prohibited'],
            'history' => ['prohibited'],
        ];
    }
}
