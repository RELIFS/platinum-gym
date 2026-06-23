<?php

test('public header and footer expose stable navigation and safe ctas', function () {
    $response = $this->get(route('public.home'));

    foreach (['Tentang', 'Layanan', 'Kelas', 'Produk', 'Galeri', 'Lokasi', 'BMI', 'Masuk', 'Daftar Member'] as $label) {
        $response->assertSee($label);
    }

    $response
        ->assertSee('aria-label="Navigasi utama"', false)
        ->assertSee('aria-label="Navigasi mobile"', false)
        ->assertSee('aria-controls="mobile-navigation"', false)
        ->assertSee(route('legal.terms'), false)
        ->assertSee(route('legal.privacy'), false)
        ->assertDontSee('Checkout')
        ->assertDontSee('Beli Sekarang')
        ->assertDontSee('Pesan Produk')
        ->assertDontSee('Konfirmasi Kelas');
});

test('public location external links use safe new tab attributes', function () {
    $this->get(route('public.location'))
        ->assertOk()
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSee('WhatsApp')
        ->assertSee('Instagram')
        ->assertSee('Buka Google Maps');
});
