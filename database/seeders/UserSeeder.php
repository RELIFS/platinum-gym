<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@platinumgympadang.com'],
            ['name' => 'Admin Platinum Gym', 'password' => 'password', 'status' => 'active', 'email_verified_at' => now()]
        )->assignRole('admin');

        User::updateOrCreate(
            ['email' => 'owner@platinumgympadang.com'],
            ['name' => 'Owner Platinum Gym', 'password' => 'password', 'status' => 'active', 'email_verified_at' => now()]
        )->assignRole('owner');
    }
}
