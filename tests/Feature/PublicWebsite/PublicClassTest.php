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

test('classes page groups schedules into fixed public class sections', function () {
    PublicFixtures::schedule(['name' => 'Aerobic Legacy Senam', 'class_type' => 'senam'], ['day_of_week' => 1]);
    PublicFixtures::schedule(['name' => 'Zumba Legacy Senam', 'class_type' => 'senam'], ['day_of_week' => 2]);
    PublicFixtures::schedule(['name' => 'Public Muaythai', 'class_type' => 'muaythai'], ['day_of_week' => 3]);
    PublicFixtures::schedule(['name' => 'Public Poundfit', 'class_type' => 'poundfit'], ['day_of_week' => 4]);

    $response = $this->get(route('public.classes'))->assertOk();

    $response
        ->assertSee('id="kelas-aerobic"', false)
        ->assertSee('id="kelas-zumba"', false)
        ->assertSee('id="kelas-muaythai"', false)
        ->assertSee('id="kelas-poundfit"', false)
        ->assertSee('Aerobic Legacy Senam')
        ->assertSee('Zumba Legacy Senam')
        ->assertSee('Public Muaythai')
        ->assertSee('Public Poundfit');

    $content = $response->getContent();

    expect(strpos($content, 'id="kelas-aerobic"'))->toBeLessThan(strpos($content, 'id="kelas-zumba"'))
        ->and(strpos($content, 'id="kelas-zumba"'))->toBeLessThan(strpos($content, 'id="kelas-muaythai"'))
        ->and(strpos($content, 'id="kelas-muaythai"'))->toBeLessThan(strpos($content, 'id="kelas-poundfit"'));
});

test('classes type filter uses public section resolver for legacy senam class types', function () {
    PublicFixtures::schedule(['name' => 'Aerobic Resolver Legacy', 'class_type' => 'senam'], ['day_of_week' => 1]);
    PublicFixtures::schedule(['name' => 'Zumba Resolver Legacy', 'class_type' => 'senam'], ['day_of_week' => 2]);

    $this->get(route('public.classes', ['jenis' => 'aerobic']))
        ->assertOk()
        ->assertSee('Aerobic Resolver Legacy')
        ->assertDontSee('Zumba Resolver Legacy')
        ->assertSee('jenis Aerobic');
});
