<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table): void {
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
            $table->timestamp('activated_at')->nullable()->after('status');
            $table->unsignedSmallInteger('duration_days_snapshot')->nullable()->after('price');
            $table->index('activated_at');
        });
    }

    public function down(): void
    {
        DB::table('memberships')
            ->whereNull('start_date')
            ->update(['start_date' => now()->toDateString()]);

        DB::table('memberships')
            ->whereNull('end_date')
            ->update(['end_date' => now()->toDateString()]);

        Schema::table('memberships', function (Blueprint $table): void {
            $table->dropIndex(['activated_at']);
            $table->dropColumn(['activated_at', 'duration_days_snapshot']);
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
        });
    }
};
