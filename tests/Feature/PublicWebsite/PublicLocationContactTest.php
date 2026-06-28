<?php

use App\Models\Setting;
use Tests\Feature\PublicWebsite\Support\PublicWebsiteFixtures as PublicFixtures;

test('location page uses only public settings and safe map attributes', function () {
    PublicFixtures::publicSettings();
    PublicFixtures::sensitiveSettings();

    $response = $this->get(route('public.location'));

    $response
        ->assertOk()
        ->assertSee('Jl. Public QA No. 1, Padang')
        ->assertSee('+62 821-7477-7761')
        ->assertSee('public.qa@platinum.test')
        ->assertSee('@platinumgym.qa')
        ->assertSee('data-public-map-embed', false)
        ->assertSee('title="Peta lokasi Platinum Gym Padang di Google Maps"', false)
        ->assertSee('loading="lazy"', false)
        ->assertSee('referrerpolicy="no-referrer-when-downgrade"', false);

    foreach (PublicFixtures::SENSITIVE_FRAGMENTS as $fragment) {
        $response->assertDontSee($fragment, false);
    }
});

test('location page falls back gracefully when maps embed is blank', function () {
    PublicFixtures::publicSettings();
    Setting::query()->where('key', 'maps_embed_url')->update(['value' => '']);

    $this->get(route('public.location'))
        ->assertOk()
        ->assertDontSee('data-public-map-embed', false)
        ->assertSee('Tampak depan Platinum Gym Padang')
        ->assertSee('Buka Google Maps');
});
