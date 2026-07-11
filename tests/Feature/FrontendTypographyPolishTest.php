<?php

use Illuminate\Support\Facades\File;

function frontend_typography_files(array $paths): array
{
    $files = [];

    foreach ($paths as $path) {
        if (is_file($path)) {
            $files[$path] = new SplFileInfo($path);

            continue;
        }

        foreach (File::allFiles($path) as $file) {
            $files[$file->getPathname()] = $file;
        }
    }

    ksort($files);

    return array_values($files);
}

function frontend_typography_view_files(): array
{
    return frontend_typography_files([
        resource_path('views/public'),
        resource_path('views/member'),
        resource_path('views/admin'),
        resource_path('views/owner'),
        resource_path('views/auth'),
        resource_path('views/legal'),
        resource_path('views/profile'),
        resource_path('views/components'),
        resource_path('views/partials'),
        resource_path('views/layouts/app.blade.php'),
        resource_path('views/layouts/guest.blade.php'),
        resource_path('views/layouts/navigation.blade.php'),
        resource_path('views/layouts/public.blade.php'),
        resource_path('views/layouts/member.blade.php'),
        resource_path('views/layouts/admin.blade.php'),
        resource_path('views/layouts/owner.blade.php'),
        resource_path('views/invoices/show.blade.php'),
        resource_path('views/invoices/receipt.blade.php'),
        resource_path('views/invoices/partials/document.blade.php'),
        resource_path('views/invoices/partials/receipt-paper.blade.php'),
    ]);
}

function frontend_typography_source_files(): array
{
    return array_merge(
        frontend_typography_view_files(),
        frontend_typography_files([
            resource_path('css/app.css'),
            resource_path('js'),
        ]),
    );
}

function frontend_relative_path(string $path): string
{
    return str_replace('\\', '/', str_replace(base_path().DIRECTORY_SEPARATOR, '', $path));
}

function frontend_raw_weight_tokens(string $content): array
{
    preg_match_all(
        '/(?<![A-Za-z0-9_-])(?:(?:[A-Za-z0-9_\-\[\]=]+):)*font-(?:black|extrabold|bold|semibold|medium)(?![A-Za-z0-9_-])/',
        $content,
        $matches,
    );

    $allowedVariants = [
        'file:font-semibold',
        'placeholder:font-medium',
    ];

    return array_values(array_unique(array_diff($matches[0], $allowedVariants)));
}

function frontend_surface_content(array $paths): string
{
    return implode("\n", array_map(
        fn (SplFileInfo $file): string => file_get_contents($file->getPathname()),
        frontend_typography_files($paths),
    ));
}

function frontend_hex_rgb(string $hex): array
{
    $hex = ltrim($hex, '#');

    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}

function frontend_relative_luminance(string $hex): float
{
    $channels = array_map(function (int $channel): float {
        $value = $channel / 255;

        return $value <= 0.04045
            ? $value / 12.92
            : (($value + 0.055) / 1.055) ** 2.4;
    }, frontend_hex_rgb($hex));

    return (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);
}

function frontend_contrast_ratio(string $foreground, string $background): float
{
    $foregroundLuminance = frontend_relative_luminance($foreground);
    $backgroundLuminance = frontend_relative_luminance($background);

    return (max($foregroundLuminance, $backgroundLuminance) + 0.05)
        / (min($foregroundLuminance, $backgroundLuminance) + 0.05);
}

function frontend_blend_hex(string $foreground, string $background, float $alpha): string
{
    $foregroundRgb = frontend_hex_rgb($foreground);
    $backgroundRgb = frontend_hex_rgb($background);
    $channels = [];

    foreach ($foregroundRgb as $index => $channel) {
        $channels[] = (int) round(($channel * $alpha) + ($backgroundRgb[$index] * (1 - $alpha)));
    }

    return sprintf('#%02x%02x%02x', ...$channels);
}

function frontend_css_hex_variable(string $css, string $scope, string $variable): string
{
    preg_match('/'.preg_quote($scope, '/').'\s*\{(?<body>.*?)\n\s*\}/s', $css, $scopeMatch);
    preg_match('/'.preg_quote($variable, '/').'\s*:\s*(?<value>#[0-9a-f]{6})/i', $scopeMatch['body'] ?? '', $variableMatch);

    return strtolower($variableMatch['value'] ?? '');
}

test('semantic typography contract is defined with the approved weights', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('--font-weight-display: 800;')
        ->toContain('--font-weight-title: 700;')
        ->toContain('--font-weight-emphasis: 700;')
        ->toContain('--font-weight-control: 600;')
        ->toContain('--font-weight-compact: 500;')
        ->toContain('--font-weight-body: 400;')
        ->toMatch('/\.type-display\s*\{\s*font-weight:\s*var\(--font-weight-display\);/s')
        ->toMatch('/\.type-title\s*\{\s*font-weight:\s*var\(--font-weight-title\);/s')
        ->toMatch('/\.type-emphasis\s*\{\s*font-weight:\s*var\(--font-weight-emphasis\);/s')
        ->toMatch('/\.type-control\s*\{\s*font-weight:\s*var\(--font-weight-control\);/s')
        ->toMatch('/\.type-compact\s*\{\s*font-weight:\s*var\(--font-weight-compact\);/s')
        ->toMatch('/\.type-body\s*\{\s*font-weight:\s*var\(--font-weight-body\);/s');
});

test('scoped web sources use semantic roles instead of standalone raw weight utilities', function () {
    $hits = [];

    foreach (frontend_typography_source_files() as $file) {
        $tokens = frontend_raw_weight_tokens(file_get_contents($file->getPathname()));

        if ($tokens !== []) {
            $hits[frontend_relative_path($file->getPathname())] = $tokens;
        }
    }

    expect($hits)->toBe([]);
});

test('font black and numeric heavy declarations are absent from scoped web sources', function () {
    $hits = [];

    foreach (frontend_typography_source_files() as $file) {
        $content = file_get_contents($file->getPathname());

        if (str_contains($content, 'font-black') || preg_match('/font-weight:\s*(?:800|900)\s*;/', $content) === 1) {
            $hits[] = frontend_relative_path($file->getPathname());
        }
    }

    expect($hits)->toBe([]);
});

test('display weight is restricted to the documented marketing allowlist', function () {
    $viewHits = [];

    foreach (frontend_typography_view_files() as $file) {
        $count = substr_count(file_get_contents($file->getPathname()), 'type-display');

        for ($index = 0; $index < $count; $index++) {
            $viewHits[] = frontend_relative_path($file->getPathname());
        }
    }

    sort($viewHits);

    expect($viewHits)->toBe([
        'resources/views/layouts/guest.blade.php',
        'resources/views/public/partials/page-hero.blade.php',
    ]);

    $css = file_get_contents(resource_path('css/app.css'));

    expect(substr_count($css, 'font-weight: var(--font-weight-display);'))->toBe(2)
        ->and($css)->toMatch('/\.public-home-hero-title\s*\{[^}]*font-weight:\s*var\(--font-weight-display\);/s');
});

test('each interactive surface uses the semantic hierarchy required for its role', function () {
    $surfaces = [
        'public' => [[resource_path('views/public'), resource_path('views/layouts/public.blade.php')], ['type-display', 'type-title', 'type-emphasis', 'type-control', 'type-compact']],
        'auth and profile' => [[resource_path('views/auth'), resource_path('views/legal'), resource_path('views/profile'), resource_path('views/layouts/guest.blade.php')], ['type-display', 'type-title', 'type-emphasis', 'type-control', 'type-compact']],
        'member' => [[resource_path('views/member'), resource_path('views/layouts/member.blade.php')], ['type-title', 'type-emphasis', 'type-control', 'type-compact']],
        'admin' => [[resource_path('views/admin'), resource_path('views/layouts/admin.blade.php')], ['type-title', 'type-emphasis', 'type-control', 'type-compact']],
        'owner' => [[resource_path('views/owner'), resource_path('views/layouts/owner.blade.php')], ['type-title', 'type-emphasis', 'type-control', 'type-compact']],
        'invoice and receipt html' => [[resource_path('views/invoices/show.blade.php'), resource_path('views/invoices/receipt.blade.php'), resource_path('views/invoices/partials/document.blade.php'), resource_path('views/invoices/partials/receipt-paper.blade.php')], ['type-title', 'type-emphasis', 'type-control']],
    ];

    foreach ($surfaces as $surface => [$paths, $roles]) {
        $content = frontend_surface_content($paths);

        foreach ($roles as $role) {
            expect($content, $surface)->toContain($role);
        }
    }
});

test('the weight guard covers every supported text color family', function () {
    $colorPatterns = [
        'white and off-white' => '/(?:^|\s)(?:(?:dark|hover|focus|group-hover):)*text-white(?:\/\d+)?(?:\s|["\'`;])/',
        'zinc neutrals' => '/(?:^|\s)(?:(?:dark|hover|focus|group-hover):)*text-zinc-\d{2,3}(?:\s|["\'`;])/',
        'gold' => '/(?:^|\s)(?:(?:dark|hover|focus|group-hover):)*text-gold(?:-[A-Za-z0-9-]+)?(?:\s|["\'`;])/',
        'red' => '/(?:^|\s)(?:(?:dark|hover|focus|group-hover):)*text-red-\d{2,3}(?:\s|["\'`;])/',
        'amber' => '/(?:^|\s)(?:(?:dark|hover|focus|group-hover):)*text-amber-\d{2,3}(?:\s|["\'`;])/',
        'emerald' => '/(?:^|\s)(?:(?:dark|hover|focus|group-hover):)*text-emerald-\d{2,3}(?:\s|["\'`;])/',
        'sky' => '/(?:^|\s)(?:(?:dark|hover|focus|group-hover):)*text-sky-\d{2,3}(?:\s|["\'`;])/',
    ];

    foreach ($colorPatterns as $color => $colorPattern) {
        $matchedLines = [];

        foreach (frontend_typography_source_files() as $file) {
            foreach (preg_split('/\R/', file_get_contents($file->getPathname())) as $lineNumber => $line) {
                if (preg_match($colorPattern, $line) !== 1) {
                    continue;
                }

                $matchedLines[] = frontend_relative_path($file->getPathname()).':'.($lineNumber + 1);
                expect(frontend_raw_weight_tokens($line), $color)->toBe([]);
            }
        }

        expect($matchedLines, $color)->not->toBe([]);
    }
});

test('placeholder and disabled text stay at compact weight or below', function () {
    $content = implode("\n", array_map(
        fn (SplFileInfo $file): string => file_get_contents($file->getPathname()),
        frontend_typography_source_files(),
    ));
    $css = file_get_contents(resource_path('css/app.css'));

    expect($content)
        ->not->toMatch('/placeholder:font-(?:semibold|bold|extrabold|black)/')
        ->not->toMatch('/disabled:font-(?:semibold|bold|extrabold|black)/')
        ->toContain('placeholder:text-zinc-500')
        ->toContain('dark:placeholder:text-zinc-400');

    expect($css)->toMatch('/:disabled,\s*\[aria-disabled=\'true\'\]\s*\{\s*font-weight:\s*var\(--font-weight-compact\);/s');
});

test('poppins weight 900 is not loaded by web layouts', function () {
    foreach (frontend_typography_files([resource_path('views/layouts')]) as $file) {
        $content = file_get_contents($file->getPathname());

        expect($content)->not->toMatch('/family=Poppins[^"\']*wght@[^"\']*900/i');
    }
});

test('brand gold tokens keep readable foregrounds in light and dark modes', function () {
    $css = file_get_contents(resource_path('css/app.css'));
    $tailwind = strtolower(file_get_contents(base_path('tailwind.config.js')));

    expect(frontend_css_hex_variable($css, ':root', '--color-gold-brand'))->toBe('#feac18')
        ->and(frontend_css_hex_variable($css, ':root', '--color-gold-text'))->toBe('#3f3f46')
        ->and(frontend_css_hex_variable($css, ':root', '--color-gold-text-strong'))->toBe('#27272a')
        ->and(frontend_css_hex_variable($css, '.dark', '--color-gold-text'))->toBe('#ffd978')
        ->and(frontend_css_hex_variable($css, '.dark', '--color-gold-text-strong'))->toBe('#ffd978');

    expect($tailwind)
        ->not->toContain('#a16207')
        ->not->toContain('#854d0e')
        ->not->toContain('#9f5f00')
        ->not->toContain('#b36b00');
});

test('primary foreground and surface pairs meet wcag aa contrast', function () {
    $css = file_get_contents(resource_path('css/app.css'));
    $goldBrand = frontend_css_hex_variable($css, ':root', '--color-gold-brand');
    $goldLinkLight = frontend_css_hex_variable($css, ':root', '--color-gold-text');
    $goldLinkDark = frontend_css_hex_variable($css, '.dark', '--color-gold-text');
    $white = '#ffffff';
    $zinc950 = '#09090b';

    $pairs = [
        'neutral light heading' => ['#09090b', $white],
        'neutral light body' => ['#52525b', $white],
        'neutral light metadata and placeholder' => ['#71717a', $white],
        'neutral dark heading' => ['#f4f4f5', $zinc950],
        'neutral dark body' => ['#d4d4d8', $zinc950],
        'neutral dark metadata and placeholder' => ['#a1a1aa', $zinc950],
        'solid gold action' => [$zinc950, $goldBrand],
        'solid destructive action' => [$white, '#dc2626'],
        'emerald tinted status' => ['#047857', frontend_blend_hex('#10b981', $white, 0.10)],
        'amber tinted status' => ['#b45309', frontend_blend_hex('#f59e0b', $white, 0.10)],
        'red tinted status' => ['#b91c1c', frontend_blend_hex('#ef4444', $white, 0.10)],
        'sky tinted status' => ['#0369a1', frontend_blend_hex('#0ea5e9', $white, 0.10)],
        'light inline link' => [$goldLinkLight, $white],
        'dark inline link' => [$goldLinkDark, $zinc950],
    ];

    foreach ($pairs as $label => [$foreground, $background]) {
        expect(frontend_contrast_ratio($foreground, $background), $label)->toBeGreaterThanOrEqual(4.5);
    }
});
