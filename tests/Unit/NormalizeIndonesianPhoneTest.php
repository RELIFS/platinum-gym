<?php

use App\Features\Shared\Support\NormalizeIndonesianPhone;

it('normalizes indonesian mobile numbers to local whatsapp format', function (?string $input, string $expected) {
    expect(NormalizeIndonesianPhone::toLocalMobile($input))->toBe($expected);
})->with([
    ['+62 812-3456-7891', '081234567891'],
    ['6281234567891', '081234567891'],
    ['0812 3456 7891', '081234567891'],
    ['12345', '12345'],
    [null, ''],
]);
