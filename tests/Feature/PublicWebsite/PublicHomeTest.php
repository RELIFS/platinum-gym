<?php

use Database\Seeders\PackageSeeder;
use Database\Seeders\PromoSeeder;
use Tests\Feature\PublicWebsite\Support\PublicWebsiteFixtures as PublicFixtures;

test('home renders real published content and hides unpublished public content', function () {
    PublicFixtures::package(['name' => 'Public Active Gym Plan']);
    PublicFixtures::package(['name' => 'Public Inactive Gym Plan', 'is_active' => false]);
    PublicFixtures::schedule(['name' => 'Public Active Zumba']);
    PublicFixtures::product(overrides: ['name' => 'Public Active Whey']);
    PublicFixtures::gallery(['title' => 'Public Active Gallery']);
    $package = PublicFixtures::package(['name' => 'Public Promo Package']);
    PublicFixtures::promo(['title' => 'Public Active Promo', 'package_id' => $package->id]);
    PublicFixtures::promo(['title' => 'Public Draft Promo', 'is_published' => false]);
    PublicFixtures::testimonial(['name' => 'Public Active Testimonial']);
    PublicFixtures::testimonial(['name' => 'Public Draft Testimonial', 'is_published' => false]);

    $this->get(route('public.home'))
        ->assertOk()
        ->assertSee('Your Comfort Gym', false)
        ->assertSee('In Padang', false)
        ->assertSee('Public Active Gym Plan')
        ->assertDontSee('Public Inactive Gym Plan')
        ->assertSee('Public Active Zumba')
        ->assertSee('Public Active Whey')
        ->assertSee('Public Active Gallery')
        ->assertSee('Public Active Promo')
        ->assertSee('Untuk: Public Promo Package')
        ->assertDontSee('Public Draft Promo')
        ->assertSee('Public Active Testimonial')
        ->assertSee('Rating 5 dari 5', false)
        ->assertDontSee('Public Draft Testimonial');
});

test('home renders production empty states when public content is unavailable', function () {
    $this->get(route('public.home'))
        ->assertOk()
        ->assertSee('Data layanan belum tersedia.')
        ->assertSee('Jadwal kelas belum tersedia.')
        ->assertSee('Data produk belum tersedia.')
        ->assertSee('Galeri belum tersedia.')
        ->assertDontSee('Testimoni belum tersedia');
});

test('home shows official seeded gym duration promos', function () {
    $this->seed([PackageSeeder::class, PromoSeeder::class]);

    $this->get(route('public.home'))
        ->assertOk()
        ->assertSee('Beli Gym Umum 3 Bulan Gratis 1 Bulan')
        ->assertSee('Beli Gym Umum 6 Bulan Gratis 2 Bulan')
        ->assertSee('Untuk: Gym Umum 3 Bulan')
        ->assertSee('Untuk: Gym Umum 6 Bulan')
        ->assertDontSee('Hemat 0%');
});
