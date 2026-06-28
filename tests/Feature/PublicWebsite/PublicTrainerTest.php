<?php

use Tests\Feature\PublicWebsite\Support\PublicWebsiteFixtures as PublicFixtures;

test('about page lists only active trainer cards', function () {
    PublicFixtures::trainer(['name' => 'Coach Ola', 'specialization' => 'Aerobic']);
    PublicFixtures::trainer(['name' => 'Zin Nila', 'specialization' => 'Zumba']);
    PublicFixtures::trainer(['name' => 'Coach Ajeng', 'specialization' => 'Poundfit']);
    PublicFixtures::trainer(['name' => 'Coach Public Active', 'specialization' => 'Strength']);
    PublicFixtures::trainer(['name' => 'Coach Public Inactive', 'is_active' => false]);

    $this->get(route('public.about'))
        ->assertOk()
        ->assertSee('Tim Pelatih')
        ->assertDontSee('Tim Coach')
        ->assertSee('Ola')
        ->assertSee('Instruktur Aerobic')
        ->assertDontSee('Coach Ola')
        ->assertSee('Zin Nila')
        ->assertSee('Instruktur Zumba')
        ->assertSee('Ajeng')
        ->assertSee('Pro Poundfit')
        ->assertDontSee('Coach Ajeng')
        ->assertSee('Coach Public Active')
        ->assertSee('Coach Strength')
        ->assertDontSee('Coach Public Inactive');
});
