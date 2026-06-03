<?php

use App\Models\Setting;
use Database\Seeders\GallerySeeder;
use Database\Seeders\GymClassSeeder;
use Database\Seeders\PackageSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\PromoSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\TestimonialSeeder;
use Database\Seeders\TrainerSeeder;

beforeEach(function () {
    $this->seed([
        PackageSeeder::class,
        TrainerSeeder::class,
        GymClassSeeder::class,
        ProductSeeder::class,
        SettingSeeder::class,
        PromoSeeder::class,
        TestimonialSeeder::class,
        GallerySeeder::class,
    ]);
});

test('public routes return successful responses', function (string $path) {
    $this->get($path)->assertOk();
})->with([
    '/',
    '/tentang-kami',
    '/layanan',
    '/kelas',
    '/produk',
    '/galeri',
    '/lokasi',
    '/bmi',
    '/syarat-ketentuan',
    '/kebijakan-privasi',
]);

test('home shows brand and core calls to action', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Platinum Gym Padang')
        ->assertSee('Daftar Member')
        ->assertSee('Lihat Layanan')
        ->assertSee('Chatbot Platinum Gym');
});

test('public header exposes clean logo, accessible theme action, and chatbot', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('brand-logo', false)
        ->assertDontSee('brand-logo-frame', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('aria-label="Aktifkan mode gelap"', false)
        ->assertSee('aria-label="Buka chatbot Platinum Gym"', false)
        ->assertSee('Ketik pertanyaan chatbot', false)
        ->assertDontSee('Chat Admin');
});

test('services page shows seeded packages', function () {
    $this->get('/layanan')
        ->assertOk()
        ->assertSee('Gym Umum')
        ->assertSee('PT 10x')
        ->assertSee('Muaythai Umum 8x');
});

test('classes page shows schedules and filters by day and type', function () {
    $this->get('/kelas')
        ->assertOk()
        ->assertSee('Aerobic')
        ->assertSee('Muaythai');

    $this->get('/kelas?hari=rabu&jenis=poundfit')
        ->assertOk()
        ->assertSee('Poundfit')
        ->assertDontSee('Zin Nila');
});

test('product search and category filter work', function () {
    $this->get('/produk?q=whey')
        ->assertOk()
        ->assertSee('Whey')
        ->assertDontSee('Roti');

    $this->get('/produk?kategori=makanan')
        ->assertOk()
        ->assertSee('Roti')
        ->assertDontSee('Glove BN Classic');
});

test('location page shows final contact data', function () {
    $this->get('/lokasi')
        ->assertOk()
        ->assertSee('Jl. H. Agus Salim No.3A')
        ->assertSee('+62 821-7477-7761')
        ->assertSee('info@platinumgympadang.com')
        ->assertSee('@platinumgym.padang_new')
        ->assertSee('https://www.google.com/maps?cid=12649542093238337913')
        ->assertSee('Buka Google Maps');
});

test('location page renders google maps iframe when embed url is configured', function () {
    $this->get('/lokasi')
        ->assertOk()
        ->assertSee('data-public-map-embed', false)
        ->assertSee('title="Peta lokasi Platinum Gym Padang di Google Maps"', false)
        ->assertSee('loading="lazy"', false)
        ->assertSee('referrerpolicy="no-referrer-when-downgrade"', false)
        ->assertSee('https://www.google.com/maps/embed?pb=', false)
        ->assertSee('Gunakan tombol di bawah peta untuk membuka rute langsung di Google Maps.')
        ->assertSee('Buka Google Maps');
});

test('location page falls back to visual card when embed url is blank', function () {
    Setting::query()->where('key', 'maps_embed_url')->update(['value' => '']);

    $this->get('/lokasi')
        ->assertOk()
        ->assertDontSee('data-public-map-embed', false)
        ->assertSee('Kelas aktif Platinum Gym Padang')
        ->assertSee('Padang Timur')
        ->assertSee('Buka Google Maps');
});

test('public whatsapp links fall back when stored setting is blank', function () {
    Setting::query()->where('key', 'whatsapp_number')->update(['value' => '']);

    $this->get('/')
        ->assertOk()
        ->assertSee('https://wa.me/6282174777761', false)
        ->assertDontSee('https://wa.me/?text=', false);
});

test('sensitive settings are not visible on public pages', function () {
    $this->get('/')
        ->assertOk()
        ->assertDontSee('change-this-in-production')
        ->assertDontSee('qr_secret')
        ->assertDontSee('gemini_system_prompt');
});
