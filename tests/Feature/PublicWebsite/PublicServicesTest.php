<?php

use Tests\Feature\PublicWebsite\Support\PublicWebsiteFixtures as PublicFixtures;

test('services page lists active packages and hides inactive packages', function () {
    PublicFixtures::package(['name' => 'Public Active Membership']);
    PublicFixtures::package(['name' => 'Public Hidden Membership', 'is_active' => false]);

    $this->get(route('public.services'))
        ->assertOk()
        ->assertSee('Public Active Membership')
        ->assertDontSee('Public Hidden Membership')
        ->assertSee('Daftar Membership')
        ->assertSee('aria-label="Daftar Membership Public Active Membership"', false);
});

test('services promo strip only renders valid published promos', function () {
    PublicFixtures::promo(['title' => 'Public Valid Promo']);
    PublicFixtures::promo(['title' => 'Public Unpublished Promo', 'is_published' => false]);
    PublicFixtures::promo(['title' => 'Public Expired Promo', 'ends_at' => now()->subDay()]);

    $this->get(route('public.services'))
        ->assertOk()
        ->assertSee('Public Valid Promo')
        ->assertDontSee('Public Unpublished Promo')
        ->assertDontSee('Public Expired Promo');
});

test('services page orders membership and muaythai packages by public sales priority', function () {
    foreach (['Gym Mahasiswa', 'Gym + Senam Mahasiswa', 'Senam Umum', 'Gym Umum', 'Senam Mahasiswa', 'Gym + Senam Umum'] as $index => $name) {
        PublicFixtures::package([
            'name' => $name,
            'slug' => 'membership-public-order-'.$index,
            'package_kind' => 'membership',
            'type' => str_contains($name, 'Senam') ? 'senam' : 'gym',
            'category' => str_contains($name, 'Mahasiswa') ? 'mahasiswa' : 'umum',
            'price' => 200000 + ($index * 10000),
        ]);
    }

    foreach (['Muaythai Mahasiswa 8x', 'Muaythai Umum 4x', 'Muaythai 1x', 'Muaythai Mahasiswa 4x', 'Muaythai Umum 8x'] as $index => $name) {
        PublicFixtures::package([
            'name' => $name,
            'slug' => 'muaythai-public-order-'.$index,
            'package_kind' => 'muaythai',
            'type' => 'muaythai',
            'category' => str_contains($name, 'Mahasiswa') ? 'mahasiswa' : 'umum',
            'price' => 150000 + ($index * 10000),
            'session_count' => str_contains($name, '8x') ? 8 : (str_contains($name, '4x') ? 4 : 1),
            'duration_days' => null,
        ]);
    }
    PublicFixtures::package([
        'name' => 'PT 5x',
        'slug' => 'pt-5x-public-group-order',
        'package_kind' => 'personal_trainer',
        'type' => 'personal_trainer',
        'category' => 'umum',
        'session_count' => 5,
        'duration_days' => null,
    ]);
    PublicFixtures::package([
        'name' => 'Poundfit 1x',
        'slug' => 'poundfit-1x-public-group-order',
        'package_kind' => 'session',
        'type' => 'poundfit',
        'category' => 'umum',
        'session_count' => 1,
        'duration_days' => null,
    ]);

    $content = $this->get(route('public.services'))->assertOk()->getContent();

    expect(strpos($content, 'Paket Membership'))->toBeLessThan(strpos($content, 'Paket Muaythai'))
        ->and(strpos($content, 'Paket Muaythai'))->toBeLessThan(strpos($content, 'Paket Personal Trainer'))
        ->and(strpos($content, 'Paket Personal Trainer'))->toBeLessThan(strpos($content, 'Paket Session'))
        ->and(strpos($content, 'Gym Umum'))->toBeLessThan(strpos($content, 'Senam Umum'))
        ->and(strpos($content, 'Senam Umum'))->toBeLessThan(strpos($content, 'Gym + Senam Umum'))
        ->and(strpos($content, 'Gym + Senam Umum'))->toBeLessThan(strpos($content, 'Gym Mahasiswa'))
        ->and(strpos($content, 'Gym Mahasiswa'))->toBeLessThan(strpos($content, 'Senam Mahasiswa'))
        ->and(strpos($content, 'Senam Mahasiswa'))->toBeLessThan(strpos($content, 'Gym + Senam Mahasiswa'))
        ->and(strpos($content, 'Muaythai 1x'))->toBeLessThan(strpos($content, 'Muaythai Umum 4x'))
        ->and(strpos($content, 'Muaythai Umum 4x'))->toBeLessThan(strpos($content, 'Muaythai Umum 8x'))
        ->and(strpos($content, 'Muaythai Umum 8x'))->toBeLessThan(strpos($content, 'Muaythai Mahasiswa 4x'))
        ->and(strpos($content, 'Muaythai Mahasiswa 4x'))->toBeLessThan(strpos($content, 'Muaythai Mahasiswa 8x'));
});
