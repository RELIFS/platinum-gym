<?php

test('bmi page is client side only with accessible inputs and live result', function () {
    $response = $this->get(route('public.bmi'))
        ->assertOk()
        ->assertSee('Berat badan (kg)')
        ->assertSee('for="weight"', false)
        ->assertSee('id="weight"', false)
        ->assertSee('aria-describedby="weight-help"', false)
        ->assertSee('Tinggi badan (cm)')
        ->assertSee('for="height"', false)
        ->assertSee('id="height"', false)
        ->assertSee('aria-describedby="height-help"', false)
        ->assertSee('role="status"', false)
        ->assertSee('aria-live="polite"', false)
        ->assertSee('data-bmi-live-result', false)
        ->assertSee('data-bmi-visual-panel', false)
        ->assertSee('data-bmi-gauge', false)
        ->assertSee('data-bmi-gauge-summary', false)
        ->assertSee('data-bmi-segmented-range', false)
        ->assertSee('data-bmi-category-list', false)
        ->assertSee('x-bind:aria-current="isActive(categoryItem) ? \'true\' : null"', false)
        ->assertSee('Data tidak dikirim ke server dan tidak disimpan.')
        ->assertDontSee('Tidak ada data berat atau tinggi yang disimpan oleh sistem')
        ->assertDontSee('method="POST"', false);

    expect(substr_count($response->getContent(), 'tidak disimpan'))->toBe(1);
});

test('bmi page exposes visual gauge and readable bmi category ranges', function () {
    $this->get(route('public.bmi'))
        ->assertOk()
        ->assertSee('Rentang BMI')
        ->assertSee('Visual IMT')
        ->assertSee('Bar warna memberi orientasi cepat')
        ->assertSee('Bobot terlalu rendah')
        ->assertSee('&lt;= 15.9', false)
        ->assertSee('Sangat kurang bobot')
        ->assertSee('16.0 - 16.9')
        ->assertSee('Kurang bobot')
        ->assertSee('17.0 - 18.4')
        ->assertSee('Normal')
        ->assertSee('18.5 - 24.9')
        ->assertSee('Kelebihan bobot')
        ->assertSee('25.0 - 29.9')
        ->assertSee('Obesitas kelas I')
        ->assertSee('30.0 - 34.9')
        ->assertSee('Obesitas kelas II')
        ->assertSee('35.0 - 39.9')
        ->assertSee('Obesitas kelas III')
        ->assertSee('&gt;= 40.0', false)
        ->assertSee('Kategori Anda')
        ->assertSee('x-text="category"', false)
        ->assertDontSee('gaugeRotation')
        ->assertDontSee('x-bind:style', false);
});
