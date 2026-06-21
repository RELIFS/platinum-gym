<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            ['Gym Umum', 'membership', 'gym', 'umum', 'all', null, 249000, null, 30, null, false, ['Akses gym selama masa aktif']],
            ['Gym Mahasiswa', 'membership', 'gym', 'mahasiswa', 'all', 22, 199000, null, 30, null, false, ['Akses gym untuk mahasiswa maksimal 22 tahun']],
            ['Senam Umum', 'membership', 'senam', 'umum', 'all', null, 249000, null, 30, null, false, ['Akses kelas Aerobic dan Zumba']],
            ['Senam Mahasiswa', 'membership', 'senam', 'mahasiswa', 'all', 22, 199000, null, 30, null, false, ['Akses senam untuk mahasiswa maksimal 22 tahun']],
            ['Gym + Senam Umum', 'membership', 'include', 'umum', 'female', null, 250000, null, 30, null, false, ['Akses gym, Aerobic, dan Zumba khusus perempuan']],
            ['Gym + Senam Mahasiswa', 'membership', 'include', 'mahasiswa', 'female', 22, 200000, null, 30, null, false, ['Akses gym dan senam untuk mahasiswi maksimal 22 tahun']],
            ['PT 5x', 'personal_trainer', 'pt', null, 'all', null, 650000, null, null, 5, true, ['5 sesi personal trainer']],
            ['PT 10x', 'personal_trainer', 'pt', null, 'all', null, 1000000, null, null, 10, true, ['10 sesi personal trainer']],
            ['PT 24x', 'personal_trainer', 'pt', null, 'all', null, 2000000, null, null, 24, true, ['24 sesi personal trainer']],
            ['Muaythai 1x', 'muaythai', 'muaythai', 'umum', 'all', null, 85000, null, null, 1, false, ['1 sesi Muaythai']],
            ['Muaythai Umum 4x', 'muaythai', 'muaythai', 'umum', 'all', null, 300000, null, null, 4, false, ['4 sesi Muaythai umum']],
            ['Muaythai Mahasiswa 4x', 'muaythai', 'muaythai', 'mahasiswa', 'all', 22, 250000, null, null, 4, false, ['4 sesi Muaythai mahasiswa']],
            ['Muaythai Umum 8x', 'muaythai', 'muaythai', 'umum', 'all', null, 500000, null, null, 8, false, ['8 sesi Muaythai umum']],
            ['Muaythai Mahasiswa 8x', 'muaythai', 'muaythai', 'mahasiswa', 'all', 22, 400000, null, null, 8, false, ['8 sesi Muaythai mahasiswa']],
            ['Poundfit 1x', 'session', 'poundfit', 'umum', 'all', null, 50000, null, null, 1, false, ['1 sesi Poundfit']],
        ];

        foreach ($packages as [$name, $kind, $type, $category, $gender, $maxAge, $price, $promo, $duration, $sessions, $requiresMembership, $benefits]) {
            Package::updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'package_kind' => $kind,
                'type' => $type,
                'category' => $category,
                'gender_restriction' => $gender,
                'max_age' => $maxAge,
                'price' => $price,
                'promo_price' => $promo,
                'duration_days' => $duration,
                'session_count' => $sessions,
                'requires_active_membership' => $requiresMembership,
                'description' => implode(', ', $benefits),
                'benefits' => $benefits,
                'is_active' => true,
            ]);
        }
    }
}
