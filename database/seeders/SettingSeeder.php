<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['site_name', 'Platinum Gym Padang', 'text', 'general'],
            ['address', 'Padang, Sumatera Barat', 'text', 'general'],
            ['whatsapp_number', '', 'text', 'contact'],
            ['instagram_url', '', 'text', 'contact'],
            ['operational_hours', json_encode(['weekday' => '06:00-22:00', 'weekend' => '06:00-20:00']), 'json', 'general'],
            ['invoice_prefix', 'PGP', 'text', 'invoice'],
            ['invoice_footer', 'Terima kasih telah bertransaksi di Platinum Gym Padang.', 'text', 'invoice'],
            ['bank_accounts', json_encode([]), 'json', 'payment'],
            ['gemini_system_prompt', 'Anda adalah AI assistant Platinum Gym Padang yang membantu informasi layanan, kelas, booking, dan membership.', 'text', 'ai'],
            ['qr_secret', 'change-this-in-production', 'text', 'security'],
        ];

        foreach ($settings as [$key, $value, $type, $group]) {
            Setting::updateOrCreate(['key' => $key], compact('value', 'type', 'group'));
        }
    }
}
