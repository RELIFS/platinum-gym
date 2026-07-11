<?php

use Tests\Feature\PublicWebsite\Support\PublicWebsiteFixtures as PublicFixtures;

test('public pages render the public shell and do not bleed portal shells', function (string $path) {
    $this->get($path)
        ->assertOk()
        ->assertSee('public-container', false)
        ->assertSee('brand-logo', false)
        ->assertSee('public-mobile-menu-button', false)
        ->assertSee('public-skip-link', false)
        ->assertSee('data-auto-hide-topbar', false)
        ->assertSee('data-auto-hide-scope="all"', false)
        ->assertDontSee('admin-main', false)
        ->assertDontSee('member-main', false)
        ->assertDontSee('owner-main', false)
        ->assertDontSee('Admin Portal')
        ->assertDontSee('Portal Member')
        ->assertDontSee('Owner Portal');
})->with(PublicFixtures::getRoutes());

test('public buttons and cards keep stable responsive utility contracts', function () {
    PublicFixtures::package(['name' => 'Public UI Package']);
    PublicFixtures::product(overrides: ['name' => 'Public UI Product']);

    $this->get(route('public.home'))
        ->assertOk()
        ->assertSee('public-button-primary', false)
        ->assertSee('public-button-secondary', false)
        ->assertSee('public-card', false)
        ->assertSee('touch-manipulation', false)
        ->assertSee('break-words', false)
        ->assertSee('dark:', false);

    $this->get(route('public.products'))
        ->assertOk()
        ->assertSee('public-product-card', false)
        ->assertSee('public-product-filter', false)
        ->assertSee('max-w-[calc(100%-4rem)]', false);
});

test('public home uses scoped responsive sizing primitives', function () {
    PublicFixtures::package(['name' => 'Public Responsive Package']);
    PublicFixtures::product(overrides: ['name' => 'Public Responsive Product']);
    PublicFixtures::schedule(['name' => 'Public Responsive Class']);
    PublicFixtures::gallery(['title' => 'Public Responsive Gallery']);

    $this->get(route('public.home'))
        ->assertOk()
        ->assertSee('public-home-hero-grid', false)
        ->assertSee('public-home-hero-title', false)
        ->assertSee('public-home-hero-visual', false)
        ->assertSee('public-home-stat-grid', false)
        ->assertSee('public-home-section-toolbar', false)
        ->assertSee('public-home-cta-frame', false)
        ->assertSee('w-full public-button-primary public-motion-cta sm:w-auto', false)
        ->assertSee('grid min-w-0 gap-8', false);
});

test('public page hero exposes polished reusable hero and reduced motion safe animation tokens', function () {
    $this->get(route('public.services'))
        ->assertOk()
        ->assertSee('public-page-hero', false)
        ->assertSee('public-page-hero-content', false)
        ->assertSee('public-page-hero-accent', false)
        ->assertDontSee('public-page-hero-photo', false)
        ->assertDontSee('public-page-hero-image', false);

    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('@keyframes publicHeroIn')
        ->toContain('@keyframes publicHeroAccent')
        ->toContain('prefers-reduced-motion: reduce')
        ->not->toContain('public-page-hero-photo')
        ->not->toContain('public-page-hero-image');
});
