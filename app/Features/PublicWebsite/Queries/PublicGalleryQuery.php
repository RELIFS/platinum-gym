<?php

namespace App\Features\PublicWebsite\Queries;

use App\Models\Gallery;

class PublicGalleryQuery
{
    public function __construct(private readonly PublicSettingsQuery $settings) {}

    public function get(): array
    {
        return [
            'settings' => $this->settings->get(),
            'galleries' => Gallery::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ];
    }
}
