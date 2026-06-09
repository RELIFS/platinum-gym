<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private const PRODUCTS_WITHOUT_IMAGES = ['Buah'];

    public function run(): void
    {
        $catalog = [
            'Makanan' => [
                ['Roti', 7000], ['Salad Buah', 18000], ['Salad Sayur', 25000], ['Salad Sayur + Ayam', 38000], ['Telur Rebus', 4000], ['Buah', 10000], ['Hokkaido Cheese Tropicana', 30000], ['Hokkaido Cheese Tropikana', 6000], ['Jelly', 3000], ['Kacang Tojin', 2000], ['L Men Bar', 13000], ['L Men Crunch', 15000],
            ],
            'Minuman dan Suplemen' => [
                ['AQUA 1500 ml', 8000], ['AQUA 600 ml', 5000], ['CREAPROTINE Pre Workout 500g', 420000], ['Extra Joss Sport', 9000], ['Hilo Protein Chocofit', 15000], ['Jus Buah', 22000], ['L Men 2GO', 15000], ['L Men Creatine', 6000], ['L Men Gel Isopower', 10000], ['Minuman Jelly', 10000], ['NutriSari', 10000], ['OAT Drink Tropicana', 12000], ['Perfect 1L', 10000], ['Perfect 500ml', 6000], ['Pocari Sweat 500ml', 9000], ['Susu Kedelai', 5000], ['Teh Pucuk', 5000], ['Whey Pro Nutrition Gainer 1kg', 400000], ['Whey Pro Nutrition Isolate 1kg', 520000], ['Whey', 20000], ['Yakult 1 Pc', 3000],
            ],
            'Produk Lainnya' => [
                ['Deodorant', 25000], ['Glove BN Beetles', 390000], ['Glove BN Classic', 375000], ['Hand Wrap', 95000], ['Perfume 35ml', 70000], ['Perfume 60ml', 100000], ['Perfume Baccarat 60ml', 125000], ['Sarung Tangan Aolikes', 110000],
            ],
        ];

        $sort = 0;
        foreach ($catalog as $categoryName => $products) {
            $category = ProductCategory::updateOrCreate(['slug' => Str::slug($categoryName)], [
                'name' => $categoryName,
                'sort_order' => $sort++,
                'is_active' => true,
            ]);

            foreach ($products as [$name, $price]) {
                $slug = Str::slug($name);
                $imagePath = $this->imagePathFor($name, $slug);

                Product::updateOrCreate(['slug' => $slug], [
                    'category_id' => $category->id,
                    'name' => $name,
                    'price' => $price,
                    'stock' => 0,
                    'description' => $this->descriptionFor($categoryName),
                    'image_path' => $imagePath,
                    'image_alt' => $imagePath ? "Foto produk {$name} Platinum Gym Padang" : null,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function imagePathFor(string $name, string $slug): ?string
    {
        if (in_array($name, self::PRODUCTS_WITHOUT_IMAGES, true)) {
            return null;
        }

        return "images/public/products/{$slug}.webp";
    }

    private function descriptionFor(string $categoryName): string
    {
        return match ($categoryName) {
            'Makanan' => 'Pilihan makanan pendukung latihan yang tersedia untuk pembelian langsung di lokasi Platinum Gym Padang.',
            'Minuman dan Suplemen' => 'Minuman dan suplemen pendukung hidrasi, energi, dan kebutuhan latihan harian member.',
            default => 'Perlengkapan dan produk pendukung latihan yang dapat dicek stoknya melalui katalog website.',
        };
    }
}
