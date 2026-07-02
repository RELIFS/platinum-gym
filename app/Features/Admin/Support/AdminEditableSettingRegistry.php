<?php

namespace App\Features\Admin\Support;

use App\Models\Setting;

class AdminEditableSettingRegistry
{
    public function fields(): array
    {
        return [
            ['name' => 'site_name', 'label' => 'Nama Website', 'group' => 'general', 'type' => 'text', 'placeholder' => 'Platinum Gym Padang', 'help' => 'Nama ini tampil di website publik, meta SEO, dan beberapa dokumen operasional.'],
            ['name' => 'operational_hours_weekday', 'label' => 'Jam operasional hari kerja', 'group' => 'general', 'type' => 'text', 'placeholder' => '06:00-22:00', 'help' => 'Gunakan format singkat, misalnya 06:00-22:00.'],
            ['name' => 'operational_hours_weekend', 'label' => 'Jam operasional akhir pekan', 'group' => 'general', 'type' => 'text', 'placeholder' => '06:00-20:00', 'help' => 'Tampil di halaman lokasi dan informasi kontak publik.'],
            ['name' => 'address', 'label' => 'Alamat Gym', 'group' => 'contact', 'type' => 'textarea', 'placeholder' => 'Jl. ... Padang', 'help' => 'Alamat utama yang tampil pada halaman lokasi.', 'fullWidth' => true],
            ['name' => 'phone_number', 'label' => 'Nomor Telepon', 'group' => 'contact', 'type' => 'text', 'placeholder' => '081234567890', 'help' => 'Nomor utama untuk link telepon. Gunakan angka yang bisa dihubungi.'],
            ['name' => 'phone_display', 'label' => 'Nomor telepon yang ditampilkan', 'group' => 'contact', 'type' => 'text', 'placeholder' => '0812-3456-7890', 'help' => 'Versi yang lebih mudah dibaca oleh pengunjung website.'],
            ['name' => 'whatsapp_number', 'label' => 'Nomor WhatsApp', 'group' => 'contact', 'type' => 'text', 'placeholder' => '6281234567890', 'help' => 'Gunakan format internasional tanpa tanda plus agar link WhatsApp stabil.'],
            ['name' => 'public_email', 'label' => 'Email Publik', 'group' => 'contact', 'type' => 'email', 'placeholder' => 'halo@platinumgym.test', 'help' => 'Email kontak yang aman ditampilkan kepada pengunjung.', 'autocomplete' => 'email'],
            ['name' => 'instagram_handle', 'label' => 'Instagram Handle', 'group' => 'social', 'type' => 'text', 'placeholder' => '@platinumgympadang', 'help' => 'Nama akun yang ditampilkan sebagai teks.'],
            ['name' => 'instagram_url', 'label' => 'URL Instagram', 'group' => 'social', 'type' => 'url', 'placeholder' => 'https://instagram.com/platinumgympadang', 'help' => 'Link profil Instagram resmi.'],
            ['name' => 'invoice_prefix', 'label' => 'Awalan Nomor Invoice', 'group' => 'invoice', 'type' => 'text', 'placeholder' => 'PGP', 'help' => 'Dipakai sebagai awalan nomor invoice baru. Maksimal 12 karakter.'],
            ['name' => 'invoice_footer', 'label' => 'Catatan Footer Invoice', 'group' => 'invoice', 'type' => 'textarea', 'placeholder' => 'Terima kasih telah bertransaksi di Platinum Gym Padang.', 'help' => 'Pesan singkat yang tampil pada invoice dan struk.', 'fullWidth' => true],
        ];
    }

    public function groups(): array
    {
        return [
            'general' => [
                'label' => 'Informasi Umum',
                'description' => 'Identitas website dan jam operasional yang tampil untuk pengunjung.',
            ],
            'contact' => [
                'label' => 'Kontak Publik',
                'description' => 'Alamat, telepon, WhatsApp, dan email yang aman ditampilkan di website publik.',
            ],
            'social' => [
                'label' => 'Media Sosial',
                'description' => 'Link resmi Instagram yang tampil di website publik.',
            ],
            'invoice' => [
                'label' => 'Invoice',
                'description' => 'Format ringkas yang muncul di dokumen invoice dan struk transaksi.',
            ],
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
        $field = collect($this->fields())->firstWhere('name', $key);

        return $field['group'] ?? 'general';
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
