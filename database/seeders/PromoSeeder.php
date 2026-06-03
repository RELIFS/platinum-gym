<?php

namespace Database\Seeders;

use App\Models\Promo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromoSeeder extends Seeder
{
    public function run(): void
    {
        $promos = [
            ['Member Baru Lebih Hemat', 'Promo harga khusus untuk paket Gym Umum dan Senam Umum selama periode berjalan.', 'fixed', 24000, 0],
            ['Trial Senam Sore', 'Ikuti kelas senam pilihan dengan harga trial sesuai jadwal dan kuota yang tersedia.', 'fixed', 24000, 1],
        ];

        foreach ($promos as [$title, $description, $discountType, $discountValue, $sortOrder]) {
            Promo::updateOrCreate(['slug' => Str::slug($title)], [
                'title' => $title,
                'description' => $description,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'is_published' => true,
                'sort_order' => $sortOrder,
            ]);
        }
    }
}
