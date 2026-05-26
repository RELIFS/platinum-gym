<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30);
            $table->string('provider_user_id', 191);
            $table->string('provider_email', 150)->nullable();
            $table->string('provider_avatar', 500)->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id']);
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('member_code', 30)->unique();
            $table->string('gender', 20);
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact', 20)->nullable();
            $table->boolean('is_student')->default(false);
            $table->string('student_id_number', 50)->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->date('joined_at');
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('specialization', 150)->nullable();
            $table->text('bio')->nullable();
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->json('certifications')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->string('package_kind', 40);
            $table->string('type', 40);
            $table->string('category', 30)->nullable();
            $table->string('gender_restriction', 20)->default('all');
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('promo_price', 12, 2)->nullable();
            $table->timestamp('promo_starts_at')->nullable();
            $table->timestamp('promo_ends_at')->nullable();
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->unsignedSmallInteger('session_count')->nullable();
            $table->boolean('requires_active_membership')->default(false);
            $table->text('description')->nullable();
            $table->json('benefits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['package_kind', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
        Schema::dropIfExists('trainers');
        Schema::dropIfExists('members');
        Schema::dropIfExists('social_accounts');
    }
};
