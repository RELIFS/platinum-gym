<?php

use Tests\Feature\PublicWebsite\Support\PublicWebsiteFixtures as PublicFixtures;

test('about page lists only active trainer cards', function () {
    PublicFixtures::trainer(['name' => 'Coach Public Active', 'specialization' => 'Strength']);
    PublicFixtures::trainer(['name' => 'Coach Public Inactive', 'is_active' => false]);

    $this->get(route('public.about'))
        ->assertOk()
        ->assertSee('Coach Public Active')
        ->assertSee('Strength')
        ->assertDontSee('Coach Public Inactive')
        ->assertSee('Tim Coach');
});
