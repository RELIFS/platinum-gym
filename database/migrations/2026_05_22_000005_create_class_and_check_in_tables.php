<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->string('class_type', 40);
            $table->string('access_type', 40);
            $table->string('required_package_type', 40)->nullable();
            $table->unsignedSmallInteger('capacity')->default(25);
            $table->decimal('member_price', 12, 2)->nullable();
            $table->decimal('non_member_price', 12, 2)->nullable();
            $table->decimal('promo_price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_type', 'is_active']);
        });

        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 50)->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['gym_class_id', 'day_of_week', 'start_time']);
            $table->index('trainer_id');
        });

        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('class_schedules')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->date('session_date');
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20);
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'member_id', 'session_date']);
            $table->index(['schedule_id', 'session_date', 'status']);
            $table->index(['member_id', 'session_date']);
        });

        Schema::create('class_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->nullable()->constrained('class_enrollments')->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('class_schedules')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->timestamp('attended_at');
            $table->string('method', 20)->default('qr');
            $table->string('status', 20)->default('present');
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['schedule_id', 'member_id', 'attendance_date']);
            $table->index(['member_id', 'attendance_date']);
        });

        Schema::create('gym_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membership_id')->nullable()->constrained()->nullOnDelete();
            $table->date('check_in_date');
            $table->timestamp('check_in_at');
            $table->timestamp('check_out_at')->nullable();
            $table->string('method', 20)->default('qr');
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['member_id', 'check_in_date']);
            $table->index('check_in_at');
            $table->index('scanned_by');
        });

        Schema::create('qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type', 150);
            $table->unsignedBigInteger('tokenable_id');
            $table->string('token', 64)->unique();
            $table->string('purpose', 30);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tokenable_type', 'tokenable_id']);
            $table->index(['purpose', 'is_revoked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_tokens');
        Schema::dropIfExists('gym_check_ins');
        Schema::dropIfExists('class_attendances');
        Schema::dropIfExists('class_enrollments');
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('gym_classes');
    }
};
