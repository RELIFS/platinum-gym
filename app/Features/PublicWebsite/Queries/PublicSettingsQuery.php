<?php

namespace App\Features\PublicWebsite\Queries;

use App\Models\Setting;

class PublicSettingsQuery
{
    private const PUBLIC_SETTING_KEYS = [
        'site_name',
        'address',
        'phone_number',
        'phone_display',
        'whatsapp_number',
        'public_email',
        'instagram_handle',
        'instagram_url',
        'maps_url',
        'maps_search_url',
        'maps_shared_url',
        'maps_embed_url',
        'operational_hours',
    ];

    public function get(): array
    {
        $defaults = $this->defaults();

        $settings = Setting::query()
            ->whereIn('key', self::PUBLIC_SETTING_KEYS)
            ->get(['key', 'value', 'type']);

        foreach ($settings as $setting) {
            $value = $setting->type === 'json'
                ? json_decode((string) $setting->value, true)
                : $setting->value;

            if ($setting->key === 'maps_embed_url' && blank($value)) {
                $defaults[$setting->key] = '';

                continue;
            }

            if ($this->isEmptyPublicValue($value)) {
                continue;
            }

            $defaults[$setting->key] = $value;
        }

        $defaults['whatsapp_url'] = 'https://wa.me/'.preg_replace('/\D+/', '', (string) $defaults['whatsapp_number']);

        return $defaults;
    }

    private function defaults(): array
    {
        return [
            'site_name' => 'Platinum Gym Padang',
            'address' => 'Jl. H. Agus Salim No.3A, Sawahan, Kec. Padang Timur, Kota Padang, Sumatera Barat 25121.',
            'phone_number' => '082174777761',
            'phone_display' => '+62 821-7477-7761',
            'whatsapp_number' => '6282174777761',
            'public_email' => 'info@platinumgympadang.com',
            'instagram_handle' => '@platinumgym.padang_new',
            'instagram_url' => 'https://www.instagram.com/platinumgym.padang_new',
            'maps_url' => 'https://www.google.com/maps?cid=12649542093238337913',
            'maps_search_url' => 'https://www.google.com/maps/search/?api=1&query=Platinum%20Gym%20Jl%20H%20Agus%20Salim%20No%203A%20Sawahan%20Padang',
            'maps_shared_url' => 'https://share.google/ieOKMbBFASpk5Syqd',
            'maps_embed_url' => $this->mapsEmbedUrl(),
            'operational_hours' => [
                'weekday' => '06:00-22:00',
                'weekend' => '06:00-20:00',
            ],
        ];
    }

    private function mapsEmbedUrl(): string
    {
        return implode('', [
            'https://www.google.com/maps/embed?pb=',
            '!1m18!1m12!1m3!1d3989.272322888427',
            '!2d100.3644218!3d-0.9478974',
            '!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1',
            '!3m3!1m2!1s0x2fd4b96a2e6fdb8d%3A0xaf8c33837ed28179',
            '!2sPlatinumGym.Padang!5e0!3m2!1sid!2sid',
            '!4v1780128339715!5m2!1sid!2sid',
        ]);
    }

    private function isEmptyPublicValue(mixed $value): bool
    {
        if (is_array($value)) {
            return $value === [];
        }

        return blank($value);
    }
}
