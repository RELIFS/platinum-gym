<?php

use Tests\Feature\PublicWebsite\Support\PublicWebsiteFixtures as PublicFixtures;

function publicWebsiteStructuredData(string $html): array
{
    preg_match('/<script type="application\/ld\+json">\s*(.*?)\s*<\/script>/s', $html, $matches);

    expect($matches[1] ?? null)->not->toBeNull();

    return json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
}

test('public pages expose baseline seo metadata and canonical urls', function (string $path) {
    $response = $this->get($path)->assertOk();
    $html = $response->getContent();

    $response
        ->assertSee('<title>', false)
        ->assertSee('name="description"', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('property="og:description"', false)
        ->assertSee('property="og:url"', false)
        ->assertSee('property="og:image"', false)
        ->assertSee('name="twitter:card"', false)
        ->assertSee('images/public/og/platinum-gym-padang-social.jpg', false);

    $structuredData = publicWebsiteStructuredData($html);

    expect($structuredData['@type'])->toBe('HealthClub')
        ->and($structuredData['name'])->toBe('Platinum Gym Padang')
        ->and($structuredData['address']['addressCountry'])->toBe('ID');

    foreach (PublicFixtures::SENSITIVE_FRAGMENTS as $fragment) {
        $response->assertDontSee($fragment, false);
    }
})->with(PublicFixtures::getRoutes());

test('public filtered pages keep canonical url on the current route without query noise', function () {
    PublicFixtures::schedule(['name' => 'Public SEO Class', 'class_type' => 'senam'], ['day_of_week' => 1]);

    $this->get(route('public.classes', ['hari' => 'senin']))
        ->assertOk()
        ->assertSee('rel="canonical" href="'.route('public.classes').'"', false)
        ->assertDontSee('rel="canonical" href="'.route('public.classes', ['hari' => 'senin']).'"', false);
});
