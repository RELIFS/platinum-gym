<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'Personal Trainer',
                'Pendampingan latihan privat berbasis target member.',
                'images/public/gallery/platinum-gym-padang-instagram-01.webp',
                'Sesi personal trainer di Platinum Gym Padang.',
            ],
            [
                'Poundfit Regular Class',
                'Kelas Poundfit rutin dengan suasana aktif dan energik.',
                'images/public/gallery/platinum-gym-padang-instagram-02.webp',
                'Kelas Poundfit Platinum Gym Padang.',
            ],
            [
                'Muaythai & Boxing Class',
                'Latihan teknik, stamina, dan conditioning di area kelas.',
                'images/public/gallery/platinum-gym-padang-instagram-03.webp',
                'Kelas Muaythai dan Boxing Platinum Gym Padang.',
            ],
            [
                'Personal Trainer Equipment',
                'Program latihan dengan alat dan arahan coach.',
                'images/public/gallery/platinum-gym-padang-instagram-04.webp',
                'Latihan personal trainer dengan peralatan gym.',
            ],
            [
                'Back Day Strength',
                'Suasana strength training untuk progres massa otot.',
                'images/public/gallery/platinum-gym-padang-instagram-05.webp',
                'Latihan strength back day di Platinum Gym Padang.',
            ],
            [
                'Aerobic Class',
                'Sesi Aerobic sore dengan instruktur dan komunitas aktif.',
                'images/public/gallery/platinum-gym-padang-instagram-06.webp',
                'Kelas Aerobic Platinum Gym Padang.',
            ],
            [
                'Muaythai Session',
                'Latihan Muaythai untuk skill, cardio, dan conditioning.',
                'images/public/gallery/platinum-gym-padang-instagram-07.webp',
                'Sesi Muaythai Platinum Gym Padang.',
            ],
        ];

        foreach ($items as $index => [$title, $caption, $imagePath, $imageAlt]) {
            Gallery::updateOrCreate(['title' => $title], [
                'caption' => $caption,
                'image_path' => $imagePath,
                'image_alt' => $imageAlt,
                'sort_order' => $index,
                'is_published' => true,
            ]);
        }
    }
}
