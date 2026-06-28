<?php

namespace App\Http\Requests\Admin;

use App\Features\Admin\Support\AdminResourceRegistry;
use App\Features\Shared\Support\IndonesianDateFormat;
use Illuminate\Foundation\Http\FormRequest;

class UpsertAdminResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $definition = app(AdminResourceRegistry::class)->definition((string) $this->route('resource'));

        return $this->user()?->can($definition['permission']) ?? false;
    }

    public function rules(): array
    {
        $registry = app(AdminResourceRegistry::class);
        $resource = (string) $this->route('resource');
        $model = $this->route('id') ? $registry->model($resource, (int) $this->route('id')) : null;

        return $registry->rules($resource, $model);
    }

    protected function prepareForValidation(): void
    {
        $registry = app(AdminResourceRegistry::class);
        $resource = (string) $this->route('resource');
        $dates = collect($registry->definition($resource)['fields'])
            ->filter(fn (array $field): bool => in_array($field['type'], ['date', 'datetime-local'], true))
            ->mapWithKeys(function (array $field): array {
                $name = $field['name'];

                if (blank($this->input($name)) && filled($this->input($name.'_display'))) {
                    $value = $field['type'] === 'datetime-local'
                        ? IndonesianDateFormat::dateTimeFromDisplay($this->input($name.'_display'))
                        : IndonesianDateFormat::dateFromDisplay($this->input($name.'_display'));

                    return [$name => $value ?? 'invalid-date'];
                }

                return [];
            })
            ->all();
        $booleans = collect($registry->booleanFields($resource))
            ->mapWithKeys(fn (string $field): array => [$field => $this->boolean($field)])
            ->all();

        $this->merge($dates + $booleans);
    }

    public function attributes(): array
    {
        return collect(app(AdminResourceRegistry::class)->definition((string) $this->route('resource'))['fields'])
            ->mapWithKeys(fn (array $field): array => [$field['name'] => $field['label']])
            ->all();
    }
}
