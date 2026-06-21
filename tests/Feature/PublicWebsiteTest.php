<?php

use App\Models\Product;
use App\Models\ProductCategory;
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
        ->assertSee('Gymmi')
        ->assertDontSee('Member Baru Lebih Hemat')
        ->assertDontSee('Trial Senam Sore')
        ->assertDontSee('Testimoni belum tersedia');
});

test('public header exposes clean logo, accessible theme action, and chatbot', function () {
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee('brand-logo', false)
        ->assertSee('public-mobile-menu-button', false)
        ->assertSee('aria-controls="mobile-navigation"', false)
        ->assertDontSee('bg-zinc-950 text-white shadow-[0_12px_28px_rgba(24,24,27,0.24)]', false)
        ->assertDontSee('brand-logo-frame', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('aria-label="Aktifkan mode gelap"', false)
        ->assertSee('aria-label="Buka Gymmi"', false)
        ->assertSee('role="log"', false)
        ->assertSee('aria-label="Percakapan Gymmi"', false)
        ->assertSee('name="gymmi_public_message"', false)
        ->assertSee('autocomplete="off"', false)
        ->assertSee('spellcheck="true"', false)
        ->assertSee('Ketik pertanyaan untuk Gymmi', false)
        ->assertDontSee('Chat Admin');

    preg_match('/<section\b(?=[^>]*data-chatbot-panel)[^>]*class="([^"]*)"/', $response->getContent(), $matches);

    expect(explode(' ', $matches[1]))
        ->not->toContain('flex')
        ->toContain('flex-col');
});

test('services page shows seeded packages', function () {
    $this->get('/layanan')
        ->assertOk()
        ->assertSee('Gym Umum')
        ->assertSee('PT 10x')
        ->assertSee('Muaythai Umum 8x')
        ->assertSee('Daftar Membership')
        ->assertSee('Daftar Paket')
        ->assertDontSee('Lihat Detail Paket')
        ->assertDontSee('Promo aktif untuk paket pilihan')
        ->assertDontSee('Member Baru Lebih Hemat')
        ->assertDontSee('Konsultasi Paket')
        ->assertDontSee('Tanya Paket');
});

test('classes page shows schedules and filters by day and type', function () {
    $this->get('/kelas')
        ->assertOk()
        ->assertSee('Aerobic')
        ->assertSee('Muaythai')
        ->assertSee('Member gratis')
        ->assertSee('Non-member')
        ->assertDontSee('Konfirmasi Kelas')
        ->assertDontSee('Tanya Kelas')
        ->assertDontSee('hubungi admin untuk konfirmasi');

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
        ->assertSee('aria-label="Filter kategori produk"', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee('Roti')
        ->assertDontSee('Glove BN Classic');
});

test('products page presents catalog stock and location purchase scope', function () {
    $this->get('/produk')
        ->assertOk()
        ->assertSee('Pembelian produk dilakukan langsung di lokasi Platinum Gym Padang')
        ->assertSee('Stok:')
        ->assertSee('Lihat Lokasi')
        ->assertSee('autocomplete="off"', false)
        ->assertSee('autocapitalize="none"', false)
        ->assertSee('spellcheck="false"', false)
        ->assertDontSee('Tanya Produk')
        ->assertDontSee('Beli langsung di lokasi')
        ->assertDontSee('saya ingin tanya stok', false)
        ->assertDontSee('Checkout')
        ->assertDontSee('Beli Sekarang')
        ->assertDontSee('Pesan Produk');
});

test('product seed data keeps catalog images and stock valid', function () {
    expect(ProductCategory::query()->count())->toBe(3)
        ->and(Product::query()->count())->toBe(41)
        ->and(Product::query()->whereNotNull('image_path')->count())->toBe(40)
        ->and(Product::query()->where('stock', '<', 0)->exists())->toBeFalse()
        ->and(Product::query()->where('stock', '!=', 0)->exists())->toBeFalse();

    $productWithImage = Product::query()->where('name', 'Roti')->firstOrFail();

    expect($productWithImage->image_path)->toBe('images/public/products/roti.webp')
        ->and(file_exists(public_path($productWithImage->image_path)))->toBeTrue()
        ->and($productWithImage->image_alt)->toContain('Roti');

    $productWithoutImage = Product::query()->where('name', 'Buah')->firstOrFail();

    expect($productWithoutImage->image_path)->toBeNull()
        ->and($productWithoutImage->stock)->toBeGreaterThanOrEqual(0);
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

test('public light theme keeps utility map and final cta from using large black surfaces', function () {
    $location = $this->get('/lokasi');

    $location
        ->assertOk()
        ->assertSee('public-card flex min-h-[32rem] flex-col justify-between overflow-hidden p-0', false)
        ->assertSee('bg-zinc-100 sm:min-h-[30rem] dark:bg-zinc-950', false)
        ->assertDontSee('public-card flex min-h-[32rem] flex-col justify-between overflow-hidden bg-zinc-950 p-0 text-white', false);

    $this->get('/')
        ->assertOk()
        ->assertSee('border border-zinc-200 bg-white/95', false)
        ->assertSee('text-zinc-950 dark:text-white', false)
        ->assertDontSee('overflow-hidden rounded-[2rem] bg-zinc-950 p-6 text-white shadow-2xl', false);

    $this->get('/galeri')
        ->assertOk()
        ->assertSee('relative flex aspect-[4/3] items-end overflow-hidden bg-zinc-100 dark:bg-zinc-950', false)
        ->assertDontSee('relative flex aspect-[4/3] items-end overflow-hidden bg-zinc-950', false);

    $this->get('/bmi')
        ->assertOk()
        ->assertSee('bg-gradient-to-br from-white via-zinc-50 to-gold-500/10', false)
        ->assertDontSee('border border-gold-500/25 bg-zinc-950 p-6 text-white', false);

    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('.public-media-frame')
        ->toContain('bg-zinc-100')
        ->not->toContain('@apply relative overflow-hidden rounded-2xl bg-zinc-950 ring-1 ring-zinc-200/80 dark:ring-white/10;');
});

test('location page falls back to visual card when embed url is blank', function () {
    Setting::query()->where('key', 'maps_embed_url')->update(['value' => '']);

    $this->get('/lokasi')
        ->assertOk()
        ->assertDontSee('data-public-map-embed', false)
        ->assertSee('Tampak depan Platinum Gym Padang')
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
