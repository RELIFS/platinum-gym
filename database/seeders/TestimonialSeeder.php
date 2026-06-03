<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            ['Rani', 'Member Senam', 'Kelas sorenya rapi, coach aktif membimbing, dan suasana latihannya bikin konsisten.', 5, 0],
            ['Fadli', 'Member Gym', 'Alat lengkap untuk latihan harian. Lokasinya strategis dan mudah diakses setelah kerja.', 5, 1],
            ['Maya', 'Member PT', 'Program personal trainer membantu saya latihan lebih terarah dan aman.', 5, 2],
        ];

        foreach ($testimonials as [$name, $role, $content, $rating, $sortOrder]) {
            Testimonial::updateOrCreate(['name' => $name, 'role' => $role], [
                'content' => $content,
                'rating' => $rating,
                'is_published' => true,
                'sort_order' => $sortOrder,
            ]);
        }
    }
}
