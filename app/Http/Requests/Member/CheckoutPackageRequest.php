<?php

namespace App\Http\Requests\Member;

use App\Features\MemberPortal\ViewModels\MemberPortalStatusViewModel;
use App\Models\Package;
use App\Models\Trainer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('member') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'trainer_id' => ['nullable', 'integer', 'exists:trainers,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $package = $this->route('package');

            if (! $package instanceof Package) {
                return;
            }

            $trainerId = $this->integer('trainer_id') ?: null;
            $expectedSpecialization = MemberPortalStatusViewModel::trainerSpecializationFor(
                (string) $package->package_kind,
                (string) $package->type,
            );

            // Membership/gym packages: ignore trainer_id silently (no validation needed).
            if ($expectedSpecialization === null) {
                return;
            }

            // Package needs trainer pick.
            if ($trainerId === null) {
                $validator->errors()->add('trainer_id', 'Pilih trainer untuk paket sesi ini.');

                return;
            }

            $trainer = Trainer::query()
                ->where('id', $trainerId)
                ->where('is_active', true)
                ->first();

            if (! $trainer) {
                $validator->errors()->add('trainer_id', 'Trainer tidak tersedia.');

                return;
            }

            if ((string) $trainer->specialization !== $expectedSpecialization) {
                $validator->errors()->add('trainer_id', 'Trainer yang dipilih tidak sesuai dengan paket ini.');
            }
        });
    }
}
