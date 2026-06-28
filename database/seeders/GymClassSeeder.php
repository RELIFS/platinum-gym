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
            ['Aerobic', 'aerobic', 'included', 'senam', 25, 0, 45000, null, [['Coach Ola', 1, '17:15', '18:15'], ['Coach Irgo', 3, '17:15', '18:15'], ['Coach Ola', 5, '17:15', '18:15']]],
            ['Zumba', 'zumba', 'included', 'senam', 25, 0, 45000, null, [['Zin Nila', 2, '17:15', '18:15'], ['Zin Nila', 4, '17:15', '18:15']]],
            ['Poundfit', 'poundfit', 'session_based', 'poundfit', 25, null, null, null, [['Coach Ajeng', 3, '19:15', '20:15']]],
            ['Muaythai', 'muaythai', 'session_based', 'muaythai', 6, null, 85000, null, [
                ['Coach Adi', 1, '10:00', '11:00'], ['Coach Adi', 1, '19:00', '20:00'], ['Coach Arie', 1, '19:00', '20:00'],
                ['Coach Adi', 2, '10:00', '11:00'], ['Coach Adi', 2, '19:00', '20:00'], ['Coach Arie', 2, '19:00', '20:00'],
                ['Coach Adi', 4, '10:00', '11:00'], ['Coach Adi', 4, '19:00', '20:00'], ['Coach Arie', 4, '19:00', '20:00'],
                ['Coach Adi', 5, '10:00', '11:00'], ['Coach Adi', 5, '19:00', '20:00'], ['Coach Arie', 5, '19:00', '20:00'],
                ['Coach Adi', 6, '10:00', '11:00'], ['Coach Adi', 6, '19:00', '20:00'], ['Coach Arie', 6, '19:00', '20:00'],
            ]],
        ];

        foreach ($classes as [$name, $classType, $accessType, $requiredPackageType, $capacity, $memberPrice, $nonMemberPrice, $promoPrice, $schedules]) {
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

            $activeScheduleKeys = [];

            foreach ($schedules as [$trainerName, $day, $startTime, $endTime]) {
                $trainer = Trainer::where('name', $trainerName)->first();
                $activeScheduleKeys[] = $this->scheduleKey((int) $day, $startTime, $trainer?->id);

                $gymClass->schedules()->updateOrCreate([
                    'day_of_week' => $day,
                    'start_time' => $startTime,
                    'trainer_id' => $trainer?->id,
                ], [
                    'end_time' => $endTime,
                    'capacity' => $capacity,
                    'is_active' => true,
                ]);
            }

            $obsoleteScheduleIds = $gymClass->schedules()
                ->get(['id', 'trainer_id', 'day_of_week', 'start_time'])
                ->reject(fn ($schedule): bool => in_array($this->scheduleKey((int) $schedule->day_of_week, (string) $schedule->start_time, $schedule->trainer_id), $activeScheduleKeys, true))
                ->pluck('id');

            if ($obsoleteScheduleIds->isNotEmpty()) {
                $gymClass->schedules()->whereIn('id', $obsoleteScheduleIds)->update(['is_active' => false]);
            }
        }
    }

    private function scheduleKey(int $day, string $startTime, mixed $trainerId): string
    {
        return $day.'|'.substr($startTime, 0, 5).'|'.(string) $trainerId;
    }
}
