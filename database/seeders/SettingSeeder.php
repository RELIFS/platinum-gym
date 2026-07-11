<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $mapsEmbedUrl = implode('', [
            'https://www.google.com/maps/embed?pb=',
            '!1m18!1m12!1m3!1d3989.272322888427',
            '!2d100.3644218!3d-0.9478974',
            '!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1',
            '!3m3!1m2!1s0x2fd4b96a2e6fdb8d%3A0xaf8c33837ed28179',
            '!2sPlatinumGym.Padang!5e0!3m2!1sid!2sid',
            '!4v1780128339715!5m2!1sid!2sid',
        ]);

        $settings = [
            ['site_name', 'Platinum Gym Padang', 'text', 'general'],
            ['address', 'Jl. H. Agus Salim No.3A, Sawahan, Kec. Padang Timur, Kota Padang, Sumatera Barat 25121.', 'text', 'contact'],
            ['phone_number', '082174777761', 'text', 'contact'],
            ['phone_display', '+62 821-7477-7761', 'text', 'contact'],
            ['whatsapp_number', '6282174777761', 'text', 'contact'],
            ['public_email', 'info@platinumgympadang.com', 'text', 'contact'],
            ['instagram_handle', '@platinumgym.padang_new', 'text', 'contact'],
            ['instagram_url', 'https://www.instagram.com/platinumgym.padang_new', 'text', 'contact'],
            ['maps_url', 'https://www.google.com/maps?cid=12649542093238337913', 'text', 'contact'],
            ['maps_search_url', 'https://www.google.com/maps/search/?api=1&query=Platinum%20Gym%20Jl%20H%20Agus%20Salim%20No%203A%20Sawahan%20Padang', 'text', 'contact'],
            ['maps_shared_url', 'https://share.google/ieOKMbBFASpk5Syqd', 'text', 'contact'],
            ['maps_embed_url', $mapsEmbedUrl, 'text', 'contact'],
            ['operational_hours', json_encode(['monday_saturday' => '08:00-22:00', 'sunday' => 'Tutup']), 'json', 'general'],
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
