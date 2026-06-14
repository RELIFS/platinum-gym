<?php

namespace App\Features\Admin\Support;

use App\Models\Setting;

class AdminEditableSettingRegistry
{
    public function fields(): array
    {
        return [
            ['name' => 'site_name', 'label' => 'Nama Website', 'group' => 'general', 'type' => 'text'],
            ['name' => 'address', 'label' => 'Alamat Gym', 'group' => 'contact', 'type' => 'textarea'],
            ['name' => 'phone_number', 'label' => 'Nomor Telepon', 'group' => 'contact', 'type' => 'text'],
            ['name' => 'phone_display', 'label' => 'Tampilan Telepon', 'group' => 'contact', 'type' => 'text'],
            ['name' => 'whatsapp_number', 'label' => 'Nomor WhatsApp', 'group' => 'contact', 'type' => 'text'],
            ['name' => 'public_email', 'label' => 'Email Publik', 'group' => 'contact', 'type' => 'email'],
            ['name' => 'instagram_handle', 'label' => 'Instagram Handle', 'group' => 'contact', 'type' => 'text'],
            ['name' => 'instagram_url', 'label' => 'URL Instagram', 'group' => 'contact', 'type' => 'url'],
            ['name' => 'maps_url', 'label' => 'URL Google Maps', 'group' => 'contact', 'type' => 'url'],
            ['name' => 'maps_search_url', 'label' => 'URL Pencarian Maps', 'group' => 'contact', 'type' => 'url'],
            ['name' => 'maps_shared_url', 'label' => 'URL Share Maps', 'group' => 'contact', 'type' => 'url'],
            ['name' => 'maps_embed_url', 'label' => 'URL Embed Maps', 'group' => 'contact', 'type' => 'textarea'],
            ['name' => 'operational_hours_weekday', 'label' => 'Jam Operasional Weekday', 'group' => 'general', 'type' => 'text'],
            ['name' => 'operational_hours_weekend', 'label' => 'Jam Operasional Weekend', 'group' => 'general', 'type' => 'text'],
            ['name' => 'invoice_prefix', 'label' => 'Prefix Invoice', 'group' => 'invoice', 'type' => 'text'],
            ['name' => 'invoice_footer', 'label' => 'Footer Invoice', 'group' => 'invoice', 'type' => 'textarea'],
        ];
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:500'],
            'phone_number' => ['required', 'string', 'max:30'],
            'phone_display' => ['required', 'string', 'max:40'],
            'whatsapp_number' => ['required', 'string', 'max:30'],
            'public_email' => ['required', 'email:rfc', 'max:150'],
            'instagram_handle' => ['nullable', 'string', 'max:80'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'maps_search_url' => ['nullable', 'url', 'max:500'],
            'maps_shared_url' => ['nullable', 'url', 'max:255'],
            'maps_embed_url' => ['nullable', 'url', 'max:1200'],
            'operational_hours_weekday' => ['required', 'string', 'max:40'],
            'operational_hours_weekend' => ['required', 'string', 'max:40'],
            'invoice_prefix' => ['required', 'string', 'max:12'],
            'invoice_footer' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function values(): array
    {
        $settings = Setting::query()
            ->whereIn('key', $this->storageKeys())
            ->pluck('value', 'key');

        $hours = json_decode((string) $settings->get('operational_hours', ''), true) ?: [];

        return [
            'site_name' => $settings->get('site_name', 'Platinum Gym Padang'),
            'address' => $settings->get('address', ''),
            'phone_number' => $settings->get('phone_number', ''),
            'phone_display' => $settings->get('phone_display', ''),
            'whatsapp_number' => $settings->get('whatsapp_number', ''),
            'public_email' => $settings->get('public_email', ''),
            'instagram_handle' => $settings->get('instagram_handle', ''),
            'instagram_url' => $settings->get('instagram_url', ''),
            'maps_url' => $settings->get('maps_url', ''),
            'maps_search_url' => $settings->get('maps_search_url', ''),
            'maps_shared_url' => $settings->get('maps_shared_url', ''),
            'maps_embed_url' => $settings->get('maps_embed_url', ''),
            'operational_hours_weekday' => $hours['weekday'] ?? '06:00-22:00',
            'operational_hours_weekend' => $hours['weekend'] ?? '06:00-20:00',
            'invoice_prefix' => $settings->get('invoice_prefix', 'PGP'),
            'invoice_footer' => $settings->get('invoice_footer', 'Terima kasih telah bertransaksi di Platinum Gym Padang.'),
        ];
    }

    public function storagePayload(array $data): array
    {
        $payload = collect($data)->only($this->plainKeys())->map(fn ($value): ?string => filled($value) ? (string) $value : null)->all();

        $payload['operational_hours'] = json_encode([
            'weekday' => $data['operational_hours_weekday'],
            'weekend' => $data['operational_hours_weekend'],
        ]);

        return $payload;
    }

    public function groupFor(string $key): string
    {
        return match ($key) {
            'address', 'phone_number', 'phone_display', 'whatsapp_number', 'public_email', 'instagram_handle', 'instagram_url', 'maps_url', 'maps_search_url', 'maps_shared_url', 'maps_embed_url' => 'contact',
            'invoice_prefix', 'invoice_footer' => 'invoice',
            default => 'general',
        };
    }

    public function typeFor(string $key): string
    {
        return $key === 'operational_hours' ? 'json' : 'text';
    }

    private function storageKeys(): array
    {
        return [...$this->plainKeys(), 'operational_hours'];
    }

    private function plainKeys(): array
    {
        return collect($this->fields())
            ->pluck('name')
            ->reject(fn (string $key): bool => str_starts_with($key, 'operational_hours_'))
            ->values()
            ->all();
    }
}
