<?php

namespace App\Features\PublicWebsite\Queries;

use App\Models\Package;
use App\Models\Promo;
use Illuminate\Database\Eloquent\Builder;

class PublicServicesQuery
{
    public function __construct(private readonly PublicSettingsQuery $settings) {}

    public function get(): array
    {
        return [
            'settings' => $this->settings->get(),
            'packagesByKind' => Package::query()
                ->where('is_active', true)
                ->orderBy('package_kind')
                ->orderBy('price')
                ->get()
                ->groupBy('package_kind'),
            'promos' => $this->publishedPromos()->get(),
        ];
    }

    private function publishedPromos(): Builder
    {
        return Promo::query()
            ->where('is_published', true)
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }
}
