<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->string('student_verification_status', 30)->default('unverified')->after('student_id_number');
            $table->timestamp('student_verified_at')->nullable()->after('student_verification_status');
            $table->string('student_verification_source', 60)->nullable()->after('student_verified_at');
            $table->text('student_verification_note')->nullable()->after('student_verification_source');
            $table->dropColumn(['height_cm', 'weight_kg']);

            $table->index('student_verification_status');
        });

        Schema::create('member_package_session_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_package_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gym_check_in_id')->nullable()->constrained()->nullOnDelete();
            $table->date('usage_date');
            $table->timestamp('used_at');
            $table->string('method', 30)->default('admin');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('request_key', 80)->nullable()->unique();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'usage_date']);
            $table->index('recorded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_package_session_usages');

        Schema::table('members', function (Blueprint $table): void {
            $table->dropIndex(['student_verification_status']);
            $table->dropColumn([
                'student_verification_status',
                'student_verified_at',
                'student_verification_source',
                'student_verification_note',
            ]);
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
        });
    }
};
