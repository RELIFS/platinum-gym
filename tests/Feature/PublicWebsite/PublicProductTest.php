<?php

use Tests\Feature\PublicWebsite\Support\PublicWebsiteFixtures as PublicFixtures;

test('products page lists only active products in active categories', function () {
    $activeCategory = PublicFixtures::category(['name' => 'Public Active Category', 'slug' => 'public-active-category']);
    $hiddenCategory = PublicFixtures::category(['name' => 'Public Hidden Category', 'slug' => 'public-hidden-category', 'is_active' => false]);

    PublicFixtures::product($activeCategory, ['name' => 'Public Active Product']);
    PublicFixtures::product($activeCategory, ['name' => 'Public Hidden Product', 'is_active' => false]);

    $this->get(route('public.products'))
        ->assertOk()
        ->assertSee('Public Active Product')
        ->assertDontSee('Public Hidden Product')
        ->assertDontSee('public-hidden-category', false)
        ->assertDontSee('Public Hidden Category')
        ->assertSee('Pembelian produk dilakukan langsung di lokasi Platinum Gym Padang')
        ->assertDontSee('Checkout')
        ->assertDontSee('Beli Sekarang')
        ->assertDontSee('Pesan Produk');

    expect($hiddenCategory->is_active)->toBeFalse();
});

test('products search is sanitized and category filter keeps current marker', function () {
    $category = PublicFixtures::category(['name' => 'Public Suplemen', 'slug' => 'public-suplemen']);
    PublicFixtures::product($category, ['name' => 'Public Whey Isolate']);
    PublicFixtures::product($category, ['name' => 'Public Mineral Water']);

    $this->get(route('public.products', ['q' => '<script>Whey</script>']))
        ->assertOk()
        ->assertSee('Public Whey Isolate')
        ->assertDontSee('Public Mineral Water')
        ->assertSee('value="Whey"', false)
        ->assertDontSee('value="<script>Whey</script>"', false);

    $this->get(route('public.products', ['kategori' => 'public-suplemen']))
        ->assertOk()
        ->assertSee('aria-current="page"', false)
        ->assertSee('Menampilkan 2 produk kategori Public Suplemen.');
});
