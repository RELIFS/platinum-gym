<?php

test('legal pages are guest accessible and use public layout', function (string $route, string $heading) {
    $this->get(route($route))
        ->assertOk()
        ->assertSee($heading)
        ->assertSee('public-skip-link', false)
        ->assertSee('main-content', false)
        ->assertSee('Daftar Member')
        ->assertSee('Kembali ke Beranda')
        ->assertDontSee('admin-main', false)
        ->assertDontSee('member-main', false)
        ->assertDontSee('owner-main', false);
})->with([
    ['legal.terms', 'Syarat Ketentuan'],
    ['legal.privacy', 'Kebijakan Privasi'],
]);
