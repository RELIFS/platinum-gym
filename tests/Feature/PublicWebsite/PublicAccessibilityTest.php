<?php

test('public layout exposes landmarks, language, skip link, and accessible navigation controls', function () {
    $this->get(route('public.home'))
        ->assertOk()
        ->assertSee('<html lang="id"', false)
        ->assertSee('href="#main-content"', false)
        ->assertSee('<main id="main-content"', false)
        ->assertSee('aria-label="Navigasi utama"', false)
        ->assertSee('aria-label="Navigasi mobile"', false)
        ->assertSee('aria-controls="mobile-navigation"', false)
        ->assertSee('x-bind:aria-expanded', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('aria-label="Aktifkan mode gelap"', false);
});

test('public filter forms expose labels, status text, and reset actions', function (string $route, array $labels, string $statusId) {
    $response = $this->get(route($route))->assertOk();

    foreach ($labels as $label) {
        $response->assertSee($label, false);
    }

    $response
        ->assertSee('aria-describedby="'.$statusId.'"', false)
        ->assertSee('role="status"', false)
        ->assertSee('Reset');
})->with([
    ['public.classes', ['for="hari"', 'for="jenis"'], 'classes-filter-status'],
    ['public.products', ['for="kategori"', 'for="q"'], 'products-filter-status'],
]);

test('public status and empty states use readable text rather than color only', function () {
    $this->get(route('public.classes'))
        ->assertOk()
        ->assertSee('Jadwal tidak ditemukan.')
        ->assertSee('Coba ubah filter hari atau jenis kelas.');

    $this->get(route('public.products'))
        ->assertOk()
        ->assertSee('Produk tidak ditemukan.')
        ->assertSee('Coba kata kunci lain atau reset filter.');
});
