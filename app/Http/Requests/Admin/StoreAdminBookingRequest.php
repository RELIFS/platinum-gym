<?php

namespace App\Http\Requests\Admin;

use App\Features\Admin\Support\AdminOperationalRules;
use App\Features\Bookings\Support\BookingTimePolicy;
use App\Features\Shared\Support\IndonesianDateFormat;
use App\Models\ClassSchedule;
use App\Models\Member;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAdminBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_bookings') ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'exists:members,id'],
            'schedule_id' => ['required', 'exists:class_schedules,id'],
            'session_date' => ['required', 'date', 'after:today'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('member_id') || $validator->errors()->has('schedule_id')) {
                    return;
                }

                $member = Member::query()->find($this->input('member_id'));
                $schedule = ClassSchedule::query()->with('gymClass')->find($this->input('schedule_id'));

                if (! $member || ! $schedule) {
                    return;
                }

                if (! AdminOperationalRules::memberCanBookSchedule($member, $schedule)) {
                    $validator->errors()->add('schedule_id', 'Jadwal kelas tidak sesuai dengan paket aktif member.');
                }

                if ($validator->errors()->has('session_date')) {
                    return;
                }

                try {
                    $sessionDate = CarbonImmutable::parse((string) $this->input('session_date'));
                } catch (\Throwable) {
                    return;
                }

                if ($sessionDate->dayOfWeekIso !== (int) $schedule->day_of_week) {
                    $validator->errors()->add('session_date', 'Tanggal yang dipilih tidak sesuai hari jadwal kelas.');
                }
            },
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

    public function attributes(): array
    {
        return [
            'member_id' => 'member',
            'schedule_id' => 'jadwal kelas',
            'session_date' => 'tanggal kelas',
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Pilih member terlebih dahulu.',
            'member_id.exists' => 'Member yang dipilih tidak ditemukan.',
            'schedule_id.required' => 'Pilih jadwal kelas terlebih dahulu.',
            'schedule_id.exists' => 'Jadwal kelas yang dipilih tidak ditemukan.',
            'session_date.required' => 'Tanggal kelas wajib diisi.',
            'session_date.date' => 'Tanggal kelas belum valid. Gunakan format dd/mm/yyyy.',
            'session_date.after' => BookingTimePolicy::bookingDateMessage(),
        ];
    }
}
