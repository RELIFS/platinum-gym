<?php

use Tests\Feature\PublicWebsite\Support\PublicWebsiteFixtures as PublicFixtures;

test('classes page shows active schedules and hides inactive schedules or classes', function () {
    PublicFixtures::schedule(['name' => 'Public Active Aerobic']);
    PublicFixtures::schedule(['name' => 'Public Inactive Class'], ['is_active' => false]);
    PublicFixtures::schedule(['name' => 'Public Hidden Gym Class', 'is_active' => false]);

    $this->get(route('public.classes'))
        ->assertOk()
        ->assertSee('Public Active Aerobic')
        ->assertDontSee('Public Inactive Class')
        ->assertDontSee('Public Hidden Gym Class')
        ->assertSee('Member gratis')
        ->assertSee('Non-member');
});

test('classes filters accept only allowed day and active class type values', function () {
    PublicFixtures::schedule(['name' => 'Public Wednesday Poundfit', 'class_type' => 'poundfit'], ['day_of_week' => 3]);
    PublicFixtures::schedule(['name' => 'Public Friday Muaythai', 'class_type' => 'muaythai'], ['day_of_week' => 5]);

    $this->get(route('public.classes', ['hari' => 'rabu', 'jenis' => 'poundfit']))
        ->assertOk()
        ->assertSee('Public Wednesday Poundfit')
        ->assertDontSee('Public Friday Muaythai')
        ->assertSee('Menampilkan 1 jadwal')
        ->assertSee('hari Rabu')
        ->assertSee('jenis Poundfit');

    $this->get(route('public.classes', ['hari' => '<script>', 'jenis' => '<b>bad</b>']))
        ->assertOk()
        ->assertSee('Menampilkan 2 jadwal.')
        ->assertDontSee('value="<script>', false)
        ->assertDontSee('selected>&lt;script&gt;', false);
});
