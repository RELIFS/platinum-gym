<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
            'session_date' => ['required', 'date', 'after_or_equal:today'],
        ];
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
            'session_date.date' => 'Tanggal kelas belum valid.',
            'session_date.after_or_equal' => 'Tanggal kelas tidak boleh lebih awal dari hari ini.',
        ];
    }
}
