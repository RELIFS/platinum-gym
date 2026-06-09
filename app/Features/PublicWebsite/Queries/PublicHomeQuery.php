<?php

namespace App\Features\PublicWebsite\Queries;

use App\Models\Gallery;
use App\Models\GymClass;
use App\Models\Package;
use App\Models\Product;
use App\Models\Promo;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;

class PublicHomeQuery
{
    public function __construct(
        private readonly PublicSettingsQuery $settings,
        private readonly PublicClassScheduleQuery $classSchedules,
        private readonly PublicProductQuery $products,
    ) {}

    public function get(): array
    {
        return [
            'settings' => $this->settings->get(),
            'promos' => $this->publishedPromos()->limit(2)->get(),
            'packages' => Package::query()
                ->where('is_active', true)
                ->orderBy('package_kind')
                ->orderBy('price')
                ->limit(6)
                ->get(),
            'classSchedules' => $this->classSchedules->preview(8),
            'dayLabels' => $this->classSchedules->dayLabels(),
            'products' => $this->products->preview(6),
            'stats' => [
                'packages' => Package::query()->where('is_active', true)->count(),
                'classes' => GymClass::query()->where('is_active', true)->count(),
                'products' => Product::query()->where('is_active', true)->count(),
            ],
            'galleries' => Gallery::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(6)
                ->get(),
            'testimonials' => Testimonial::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(3)
                ->get(),
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
