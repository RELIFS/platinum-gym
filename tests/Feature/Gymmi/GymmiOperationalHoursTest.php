<?php

use App\Features\PublicWebsite\Queries\PublicSettingsQuery;
use App\Features\PublicWebsite\ViewModels\PublicLayoutViewModel;
use App\Models\Setting;
use App\Support\OperationalHours;

beforeEach(function () {
    Setting::query()->updateOrCreate(
        ['key' => 'operational_hours'],
        [
            'value' => json_encode(['monday_saturday' => '08:00-22:00', 'sunday' => 'Tutup'], JSON_THROW_ON_ERROR),
            'type' => 'json',
            'group' => 'general',
        ],
    );
});

test('operational hours normalize legacy and canonical values', function () {
    expect(OperationalHours::normalize(['weekday' => '06:00-22:00', 'weekend' => '06:00-20:00']))
        ->toBe(['monday_saturday' => '08:00-22:00', 'sunday' => 'Tutup'])
        ->and(OperationalHours::sentence(OperationalHours::normalize(null)))
        ->toBe('Jam operasional Platinum Gym: Senin-Sabtu 08:00-22:00, Minggu tutup.');
});

test('location footer and schema use the same official hours', function () {
    $settings = app(PublicSettingsQuery::class)->get();
    $layout = PublicLayoutViewModel::make($settings, 'Lokasi', 'Lokasi gym');

    expect($settings['operational_hours'])->toBe([
        'monday_saturday' => '08:00-22:00',
        'sunday' => 'Tutup',
    ])->and($layout['structuredData']['openingHours'])->toBe(['Mo-Sa 08:00-22:00']);

    $this->get(route('public.location'))
        ->assertOk()
        ->assertSee('Senin-Sabtu')
        ->assertSee('08:00-22:00')
        ->assertSee('Minggu')
        ->assertSee('Tutup');
});
