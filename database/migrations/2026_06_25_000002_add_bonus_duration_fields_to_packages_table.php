<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table): void {
            $table->unsignedSmallInteger('base_duration_days')->nullable()->after('duration_days');
            $table->unsignedSmallInteger('bonus_duration_days')->default(0)->after('base_duration_days');
            $table->string('bonus_label', 80)->nullable()->after('bonus_duration_days');
        });

        DB::table('packages')
            ->where('package_kind', 'membership')
            ->whereNotNull('duration_days')
            ->update([
                'base_duration_days' => DB::raw('duration_days'),
                'bonus_duration_days' => 0,
            ]);
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table): void {
            $table->dropColumn(['base_duration_days', 'bonus_duration_days', 'bonus_label']);
        });
    }
};
