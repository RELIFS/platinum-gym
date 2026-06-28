<?php

use Tests\Feature\PublicWebsite\Support\PublicWebsiteFixtures as PublicFixtures;

test('gallery page renders only published gallery items', function () {
    PublicFixtures::gallery(['title' => 'Public Published Gallery', 'caption' => 'Published caption']);
    PublicFixtures::gallery(['title' => 'Public Draft Gallery', 'is_published' => false]);

    $this->get(route('public.gallery'))
        ->assertOk()
        ->assertSee('Public Published Gallery')
        ->assertSee('Published caption')
        ->assertDontSee('Public Draft Gallery');
});

test('gallery page renders a clear empty state', function () {
    $this->get(route('public.gallery'))
        ->assertOk()
        ->assertSee('Galeri belum tersedia.')
        ->assertSee('Konten galeri akan ditampilkan setelah data dipublikasi.');
});
