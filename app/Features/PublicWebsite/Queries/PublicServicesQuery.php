<?php

namespace App\Features\PublicWebsite\Queries;

use App\Models\Package;
use App\Models\Promo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicServicesQuery
{
    private const PACKAGE_KIND_ORDER = [
        'membership' => 10,
        'muaythai' => 20,
        'personal_trainer' => 30,
        'session' => 40,
    ];

    private const MEMBERSHIP_ORDER = [
        'gym umum' => 10,
        'senam umum' => 20,
        'gym + senam umum' => 30,
        'gym mahasiswa' => 40,
        'senam mahasiswa' => 50,
        'gym + senam mahasiswa' => 60,
    ];

    private const MUAYTHAI_ORDER = [
        'muaythai 1x' => 10,
        'muaythai umum 4x' => 20,
        'muaythai umum 8x' => 30,
        'muaythai mahasiswa 4x' => 40,
        'muaythai mahasiswa 8x' => 50,
    ];

    public function __construct(private readonly PublicSettingsQuery $settings) {}

    public function get(): array
    {
        return [
            'settings' => $this->settings->get(),
            'packagesByKind' => $this->packagesByKind(),
            'promos' => $this->publishedPromos()->get(),
        ];
    }

    private function packagesByKind(): Collection
    {
        return Package::query()
            ->where('is_active', true)
            ->get()
            ->sort(fn (Package $first, Package $second): int => $this->packageSortTuple($first) <=> $this->packageSortTuple($second))
            ->groupBy('package_kind')
            ->sortKeysUsing(fn (string $first, string $second): int => $this->packageKindSortTuple($first) <=> $this->packageKindSortTuple($second));
    }

    /**
     * @return array<int, int|string>
     */
    private function packageSortTuple(Package $package): array
    {
        $kind = $this->normalizeKey((string) $package->package_kind);
        $name = Str::of((string) $package->name)->lower()->squish()->toString();
        $category = Str::lower((string) $package->category);

        $base = [
            self::PACKAGE_KIND_ORDER[$kind] ?? 999,
            $kind,
        ];

        if ($kind === 'membership') {
            return [
                ...$base,
                self::MEMBERSHIP_ORDER[$name] ?? 999,
                $this->categoryRank($category),
                (int) ($package->price ?? 0),
                $name,
            ];
        }

        if ($kind === 'muaythai') {
            return [
                ...$base,
                self::MUAYTHAI_ORDER[$name] ?? 999,
                (int) ($package->session_count ?? 999),
                $this->categoryRank($category),
                (int) ($package->price ?? 0),
                $name,
            ];
        }

        return [
            ...$base,
            $this->categoryRank($category),
            (int) ($package->price ?? 0),
            $name,
        ];
    }

    private function categoryRank(string $category): int
    {
        return match ($category) {
            'umum' => 10,
            'mahasiswa' => 20,
            default => 99,
        };
    }

    /**
     * @return array<int, int|string>
     */
    private function packageKindSortTuple(string $kind): array
    {
        $normalized = $this->normalizeKey($kind);

        return [
            self::PACKAGE_KIND_ORDER[$normalized] ?? 999,
            $normalized,
        ];
    }

    private function normalizeKey(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->squish()
            ->replace([' ', '-'], '_')
            ->toString();
    }

    private function publishedPromos(): Builder
    {
        return Promo::query()
            ->where('is_published', true)
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }
}
