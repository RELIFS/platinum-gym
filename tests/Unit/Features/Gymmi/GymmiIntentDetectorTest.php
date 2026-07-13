<?php

use App\Features\Gymmi\Support\GymmiIntentDetector;
use App\Features\Gymmi\Support\GymmiTextNormalizer;

it('uses phrase boundaries for short aliases', function () {
    $detector = new GymmiIntentDetector(new GymmiTextNormalizer);

    expect($detector->detect('jadwal Muaythai')['intent'])->toBe('class_schedule')
        ->and($detector->detect('wa admin')['intent'])->toBe('location_contact')
        ->and($detector->detect('Status membership saya')['intent'])->toBe('member_membership')
        ->and($detector->detect('transaksi saya belum lunas')['intent'])->toBe('member_payment');
});
