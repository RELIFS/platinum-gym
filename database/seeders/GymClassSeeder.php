<?php

namespace Database\Seeders;

use App\Models\GymClass;
use App\Models\Trainer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GymClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            ['Aerobic', 'senam', 'included', 'senam', 25, 0, 45000, null, [['Coach Ola', 1], ['Coach Irgo', 3], ['Coach Ola', 5]], '17:00', '18:00'],
            ['Zumba', 'senam', 'included', 'senam', 25, 0, 45000, null, [['Zin Nila', 2], ['Zin Nila', 4]], '17:00', '18:00'],
            ['Poundfit', 'poundfit', 'paid', null, 25, 50000, 50000, null, [['Coach Ajeng', 3]], '19:15', '20:15'],
            ['Muaythai', 'muaythai', 'session_based', 'muaythai', 6, null, 85000, null, [['Coach Arie', 1], ['Coach Adi', 1], ['Coach Arie', 2], ['Coach Adi', 2], ['Coach Arie', 3], ['Coach Adi', 3], ['Coach Arie', 4], ['Coach Adi', 4], ['Coach Arie', 5], ['Coach Adi', 5], ['Coach Arie', 6], ['Coach Adi', 6]], '19:00', '20:00'],
        ];

        foreach ($classes as [$name, $classType, $accessType, $requiredPackageType, $capacity, $memberPrice, $nonMemberPrice, $promoPrice, $schedules, $startTime, $endTime]) {
            $gymClass = GymClass::updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => $name.' Platinum Gym Padang.',
                'class_type' => $classType,
                'access_type' => $accessType,
                'required_package_type' => $requiredPackageType,
                'capacity' => $capacity,
                'member_price' => $memberPrice,
                'non_member_price' => $nonMemberPrice,
                'promo_price' => $promoPrice,
                'is_active' => true,
            ]);

            foreach ($schedules as [$trainerName, $day]) {
                $trainer = Trainer::where('name', $trainerName)->first();

                $gymClass->schedules()->updateOrCreate([
                    'day_of_week' => $day,
                    'start_time' => $startTime,
                ], [
                    'trainer_id' => $trainer?->id,
                    'end_time' => $endTime,
                    'capacity' => $capacity,
                    'is_active' => true,
                ]);
            }
        }
    }
}
