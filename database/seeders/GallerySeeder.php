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
                'Studio Area',
                'Area studio serbaguna untuk aktivitas kelas dan latihan grup.',
                'images/public/gallery/platinum-gym-padang-studio-area.webp',
                'Area studio Platinum Gym Padang untuk latihan grup.',
            ],
            [
                'Training Floor',
                'Area latihan utama dengan deretan mesin strength untuk program harian member.',
                'images/public/gallery/platinum-gym-padang-training-floor.webp',
                'Training floor Platinum Gym Padang dengan mesin strength.',
            ],
            [
                'Machine Row',
                'Deretan alat gym untuk latihan beban yang lebih terarah dan aman.',
                'images/public/gallery/platinum-gym-padang-machine-row.webp',
                'Deretan alat mesin latihan beban Platinum Gym Padang.',
            ],
            [
                'Free Weight Area',
                'Area dumbbell dan alat pendukung untuk variasi latihan strength.',
                'images/public/gallery/platinum-gym-padang-free-weight-area.webp',
                'Area dumbbell dan free weight Platinum Gym Padang.',
            ],
            [
                'Eksterior Gym',
                'Tampak depan lokasi Platinum Gym Padang yang mudah dikenali pengunjung.',
                'images/public/gallery/platinum-gym-padang-gym-exterior.webp',
                'Tampak depan gedung Platinum Gym Padang.',
            ],
            [
                'Bench Press Detail',
                'Detail area bench press dan mirror wall untuk latihan beban.',
                'images/public/gallery/platinum-gym-padang-bench-press-detail.webp',
                'Area bench press Platinum Gym Padang dengan mirror wall.',
            ],
            [
                'Strength Equipment',
                'Alat strength training untuk mendukung latihan otot bagian atas dan bawah.',
                'images/public/gallery/platinum-gym-padang-strength-equipment.webp',
                'Alat strength training Platinum Gym Padang.',
            ],
            [
                'Equipment Corner',
                'Sudut alat gym yang memperlihatkan suasana fasilitas latihan harian.',
                'images/public/gallery/platinum-gym-padang-equipment-corner.webp',
                'Sudut alat latihan Platinum Gym Padang.',
            ],
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

        Gallery::query()
            ->where('image_path', 'like', 'images/public/gallery/platinum-gym-padang-instagram-%')
            ->update(['is_published' => false]);

        Gallery::query()
            ->where('image_path', 'images/public/gallery/platinum-gym-padang-class-studio.webp')
            ->update(['is_published' => false]);

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
