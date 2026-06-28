<?php

namespace App\Http\Requests\Member;

use App\Features\Bookings\Support\BookingTimePolicy;
use App\Features\Shared\Support\IndonesianDateFormat;
use Illuminate\Foundation\Http\FormRequest;

class BookClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('book_class') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'session_date' => ['required', 'date', 'after:today'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (blank($this->input('session_date')) && filled($this->input('session_date_display'))) {
            $this->merge([
                'session_date' => IndonesianDateFormat::dateFromDisplay($this->input('session_date_display')) ?? 'invalid-date',
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['session_date' => 'tanggal kelas'];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'session_date.after' => BookingTimePolicy::bookingDateMessage(),
        ];
    }
}
