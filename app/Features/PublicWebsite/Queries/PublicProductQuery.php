<?php

namespace App\Features\PublicWebsite\Queries;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicProductQuery
{
    public function __construct(private readonly PublicSettingsQuery $settings) {}

    public function forIndex(Request $request): array
    {
        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedCategory = $categories->firstWhere('slug', (string) $request->query('kategori'));
        $search = $this->cleanSearch($request);

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($selectedCategory, fn ($query) => $query->where('category_id', $selectedCategory->id))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return [
            'settings' => $this->settings->get(),
            'categories' => $categories,
            'products' => $products,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
        ];
    }

    public function preview(int $limit = 6): Collection
    {
        return Product::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    private function cleanSearch(Request $request): string
    {
        return Str::limit(Str::squish(strip_tags((string) $request->query('q', ''))), 60, '');
    }
}
