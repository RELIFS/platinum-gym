<?php

namespace App\Features\PublicWebsite\Queries;

use App\Models\GymClass;
use App\Models\Package;
use App\Models\Trainer;

class PublicAboutQuery
{
    public function __construct(private readonly PublicSettingsQuery $settings) {}

    public function get(): array
    {
        return [
            'settings' => $this->settings->get(),
            'trainers' => Trainer::query()
                ->where('is_active', true)
                ->orderBy('specialization')
                ->orderBy('name')
                ->get(),
            'stats' => [
                'packages' => Package::query()->where('is_active', true)->count(),
                'classes' => GymClass::query()->where('is_active', true)->count(),
                'trainers' => Trainer::query()->where('is_active', true)->count(),
            ],
        ];
    }
}
