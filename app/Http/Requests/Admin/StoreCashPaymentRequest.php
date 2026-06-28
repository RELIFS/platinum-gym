<?php

namespace App\Http\Requests\Admin;

use App\Features\Admin\Support\AdminOperationalRules;
use App\Models\Package;
use App\Models\Trainer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('package_id') || $validator->errors()->has('trainer_id')) {
                    return;
                }

                $package = Package::query()->find($this->input('package_id'));
                if (! $package) {
                    return;
                }

                $trainerId = $this->input('trainer_id');
                $requiresTrainer = AdminOperationalRules::packageRequiresTrainer($package);

                if (! $requiresTrainer && filled($trainerId)) {
                    $validator->errors()->add('trainer_id', 'Trainer hanya dapat dipilih untuk paket Personal Trainer atau Muaythai.');

                    return;
                }

                if ($requiresTrainer && blank($trainerId)) {
                    $validator->errors()->add('trainer_id', 'Pilih trainer yang sesuai dengan paket.');

                    return;
                }

                if (blank($trainerId)) {
                    return;
                }

                $trainer = Trainer::query()->find($trainerId);
                if ($trainer && ! AdminOperationalRules::trainerMatchesPackage($package, $trainer)) {
                    $validator->errors()->add('trainer_id', 'Trainer yang dipilih tidak sesuai dengan paket.');
                }
            },
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
