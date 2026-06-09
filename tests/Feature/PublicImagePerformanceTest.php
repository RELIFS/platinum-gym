<?php

function publicAssetInfo(string $path): array
{
    $absolutePath = public_path($path);

    expect(file_exists($absolutePath))->toBeTrue();

    $size = filesize($absolutePath);
    $dimensions = getimagesize($absolutePath) ?: [null, null];

    return [$size, $dimensions[0], $dimensions[1]];
}

test('brand logo assets are optimized for repeated UI use', function () {
    [$size, $width, $height] = publicAssetInfo('images/brand/platinum-gym-wordmark-480.webp');

    expect($size)->toBeLessThan(12 * 1024)
        ->and($width)->toBe(480)
        ->and($height)->toBe(112);

    [$size, $width, $height] = publicAssetInfo('images/brand/platinum-gym-wordmark-1200.jpg');

    expect($size)->toBeLessThan(60 * 1024)
        ->and($width)->toBe(1200)
        ->and($height)->toBe(279);
});

test('public gallery images stay within card performance budget', function () {
    $galleryImages = glob(public_path('images/public/gallery/*.webp')) ?: [];

    expect($galleryImages)->not->toBeEmpty();

    foreach ($galleryImages as $absolutePath) {
        $relativePath = str_replace(public_path().DIRECTORY_SEPARATOR, '', $absolutePath);
        [$size, $width, $height] = publicAssetInfo($relativePath);

        expect($size)->toBeLessThan(90 * 1024)
            ->and($width)->toBeLessThanOrEqual(600)
            ->and($height)->toBeLessThanOrEqual(1000);
    }
});

test('public product images stay within catalog card performance budget', function () {
    $products = glob(public_path('images/public/products/*.webp')) ?: [];

    expect($products)->toHaveCount(40);

    foreach ($products as $absolutePath) {
        $relativePath = str_replace(public_path().DIRECTORY_SEPARATOR, '', $absolutePath);
        [$size, $width, $height] = publicAssetInfo($relativePath);

        expect($size)->toBeLessThan(50 * 1024)
            ->and($width)->toBe(640)
            ->and($height)->toBe(480);
    }
});

test('social preview and favicons use production-ready dimensions', function () {
    [$size, $width, $height] = publicAssetInfo('images/public/og/platinum-gym-padang-social.jpg');

    expect($size)->toBeLessThan(250 * 1024)
        ->and($width)->toBe(1200)
        ->and($height)->toBe(630);

    [$size, $width, $height] = publicAssetInfo('apple-touch-icon.png');

    expect($size)->toBeLessThan(20 * 1024)
        ->and($width)->toBe(180)
        ->and($height)->toBe(180);

    expect(file_exists(public_path('favicon.ico')))->toBeTrue()
        ->and(filesize(public_path('favicon.ico')))->toBeLessThan(40 * 1024)
        ->and(file_exists(public_path('favicon.svg')))->toBeTrue()
        ->and(filesize(public_path('favicon.svg')))->toBeLessThan(30 * 1024)
        ->and(file_exists(public_path('site.webmanifest')))->toBeTrue();
});
