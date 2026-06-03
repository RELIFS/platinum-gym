<?php

namespace App\Features\PublicWebsite\ViewModels;

use Illuminate\Support\Str;

class PublicLayoutViewModel
{
    public static function make(array $settings, string $title, string $description): array
    {
        $siteName = $settings['site_name'] ?? 'Platinum Gym Padang';
        $pageTitle = Str::contains($title, $siteName) ? $title : $title.' | '.$siteName;
        $socialImageUrl = asset('images/public/og/platinum-gym-padang-social.jpg');
        $logoUrl = asset('images/brand/platinum-gym-wordmark-1200.jpg');
        $hours = $settings['operational_hours'] ?? ['weekday' => '06:00-22:00', 'weekend' => '06:00-20:00'];

        return [
            'siteName' => $siteName,
            'pageTitle' => $pageTitle,
            'description' => $description,
            'canonicalUrl' => url()->current(),
            'logoUrl' => $logoUrl,
            'socialImageUrl' => $socialImageUrl,
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@type' => 'HealthClub',
                'name' => $siteName,
                'url' => url('/'),
                'image' => $socialImageUrl,
                'logo' => $logoUrl,
                'telephone' => $settings['phone_display'] ?? '+62 821-7477-7761',
                'email' => $settings['public_email'] ?? 'info@platinumgympadang.com',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => 'Jl. H. Agus Salim No.3A, Sawahan',
                    'addressLocality' => 'Padang',
                    'addressRegion' => 'Sumatera Barat',
                    'postalCode' => '25121',
                    'addressCountry' => 'ID',
                ],
                'openingHours' => [
                    'Mo-Fr '.($hours['weekday'] ?? '06:00-22:00'),
                    'Sa-Su '.($hours['weekend'] ?? '06:00-20:00'),
                ],
                'sameAs' => array_values(array_filter([
                    $settings['instagram_url'] ?? null,
                    $settings['maps_url'] ?? null,
                ])),
            ],
        ];
    }
}
