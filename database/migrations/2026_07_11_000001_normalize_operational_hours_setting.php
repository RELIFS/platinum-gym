<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'operational_hours')
            ->update([
                'value' => json_encode([
                    'monday_saturday' => '08:00-22:00',
                    'sunday' => 'Tutup',
                ], JSON_THROW_ON_ERROR),
                'type' => 'json',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'operational_hours')
            ->update([
                'value' => json_encode([
                    'weekday' => '06:00-22:00',
                    'weekend' => '06:00-20:00',
                ], JSON_THROW_ON_ERROR),
                'type' => 'json',
                'updated_at' => now(),
            ]);
    }
};
