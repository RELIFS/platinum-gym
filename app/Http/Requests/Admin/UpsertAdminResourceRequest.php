<?php

namespace App\Http\Requests\Admin;

use App\Features\Admin\Support\AdminResourceRegistry;
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
        $booleans = collect($registry->booleanFields($resource))
            ->mapWithKeys(fn (string $field): array => [$field => $this->boolean($field)])
            ->all();

        $this->merge($booleans);
    }

    public function attributes(): array
    {
        return collect(app(AdminResourceRegistry::class)->definition((string) $this->route('resource'))['fields'])
            ->mapWithKeys(fn (array $field): array => [$field['name'] => $field['label']])
            ->all();
    }
}
