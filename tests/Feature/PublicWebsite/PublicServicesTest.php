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
