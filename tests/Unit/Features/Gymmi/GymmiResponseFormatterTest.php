<?php

use App\Features\Gymmi\Support\GymmiResponseFormatter;

it('formats a safe short reply', function () {
    expect(app(GymmiResponseFormatter::class)->reply('  Halo  Gymmi  '))->toBe('Halo Gymmi');
});

it('redacts common secrets and preserves a sentence boundary when truncating', function () {
    $formatter = app(GymmiResponseFormatter::class);
    $message = 'API key=secret-value token=another-secret email@example.test +6281234567890';
    $long = str_repeat('Kalimat aman. ', 160).'akhir tanpa titik';

    expect($formatter->logMessage($message))
        ->not->toContain('secret-value')
        ->not->toContain('another-secret')
        ->not->toContain('email@example.test')
        ->not->toContain('+6281234567890')
        ->and($formatter->reply($long))->toEndWith('.');
});
