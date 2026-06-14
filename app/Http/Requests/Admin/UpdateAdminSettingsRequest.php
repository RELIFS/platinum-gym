<?php

namespace App\Http\Requests\Admin;

use App\Features\Admin\Support\AdminEditableSettingRegistry;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_settings') ?? false;
    }

    public function rules(): array
    {
        return app(AdminEditableSettingRegistry::class)->rules();
    }

    public function attributes(): array
    {
        return collect(app(AdminEditableSettingRegistry::class)->fields())
            ->mapWithKeys(fn (array $field): array => [$field['name'] => $field['label']])
            ->all();
    }
}
