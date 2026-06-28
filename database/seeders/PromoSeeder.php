<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Promo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromoSeeder extends Seeder
{
    public function run(): void
    {
        Promo::query()
            ->whereIn('title', ['Member Baru Lebih Hemat', 'Trial Senam Sore'])
            ->update(['is_published' => false]);

        $promos = [
            [
                'package_slug' => 'gym-umum-3-bulan',
                'title' => 'Beli Gym Umum 3 Bulan Gratis 1 Bulan',
                'description' => 'Bayar paket Gym Umum 3 bulan dan dapat total masa aktif 4 bulan. Durasi mulai saat check-in pertama.',
                'sort_order' => 10,
            ],
            [
                'package_slug' => 'gym-umum-6-bulan',
                'title' => 'Beli Gym Umum 6 Bulan Gratis 2 Bulan',
                'description' => 'Bayar paket Gym Umum 6 bulan dan dapat total masa aktif 8 bulan. Durasi mulai saat check-in pertama.',
                'sort_order' => 20,
            ],
        ];

        foreach ($promos as $promo) {
            $package = Package::query()
                ->where('slug', $promo['package_slug'])
                ->first();

            if (! $package) {
                continue;
            }

            Promo::updateOrCreate(
                ['slug' => Str::slug($promo['title'])],
                [
                    'package_id' => $package->id,
                    'title' => $promo['title'],
                    'description' => $promo['description'],
                    'starts_at' => null,
                    'ends_at' => null,
                    'discount_type' => 'none',
                    'discount_value' => null,
                    'is_published' => true,
                    'sort_order' => $promo['sort_order'],
                ],
            );
        }
    }
}
