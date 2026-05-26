<?php

namespace Database\Seeders;

use App\Models\Trainer;
use Illuminate\Database\Seeder;

class TrainerSeeder extends Seeder
{
    public function run(): void
    {
        $trainers = [
            ['Coach Linda', 'Personal Trainer'],
            ['Coach Iqbal', 'Personal Trainer'],
            ['Coach Arie', 'Muaythai'],
            ['Coach Adi', 'Muaythai'],
            ['Coach Ola', 'Aerobic'],
            ['Coach Irgo', 'Aerobic'],
            ['Zin Nila', 'Zumba'],
            ['Coach Ajeng', 'Poundfit'],
        ];

        foreach ($trainers as [$name, $specialization]) {
            Trainer::updateOrCreate(['name' => $name], ['specialization' => $specialization, 'is_active' => true]);
        }
    }
}
