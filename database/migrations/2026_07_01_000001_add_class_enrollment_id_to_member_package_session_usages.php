<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_package_session_usages', function (Blueprint $table): void {
            $table->foreignId('class_enrollment_id')
                ->nullable()
                ->after('gym_check_in_id')
                ->constrained('class_enrollments')
                ->nullOnDelete();

            $table->unique('class_enrollment_id', 'member_package_session_usages_class_enrollment_id_unique');
            $table->index(['member_package_session_id', 'class_enrollment_id'], 'mpsu_session_enrollment_index');
        });
    }

    public function down(): void
    {
        Schema::table('member_package_session_usages', function (Blueprint $table): void {
            $table->dropUnique('member_package_session_usages_class_enrollment_id_unique');
            $table->dropIndex('mpsu_session_enrollment_index');
            $table->dropConstrainedForeignId('class_enrollment_id');
        });
    }
};
