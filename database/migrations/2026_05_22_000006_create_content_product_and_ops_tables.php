<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->decimal('price', 12, 2);
            $table->integer('stock')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
        });

        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150)->nullable();
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('discount_type', 20)->default('none');
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'starts_at', 'ends_at']);
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 120);
            $table->string('role', 120)->nullable();
            $table->text('content');
            $table->unsignedTinyInteger('rating');
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'sort_order']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->longText('value')->nullable();
            $table->string('type', 20);
            $table->string('group', 50)->default('general');
            $table->timestamps();
        });

        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_name')->nullable();
            $table->string('source', 20)->default('csv');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->string('status', 20);
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('export_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->string('entity', 80);
            $table->json('filters')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status', 20);
            $table->text('error_message')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['entity', 'type']);
        });

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('context', 50);
            $table->string('title', 180)->nullable();
            $table->string('model', 80)->default('gemini-1.5-flash');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->string('role', 20);
            $table->longText('content');
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamps();

            $table->index('conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('export_jobs');
        Schema::dropIfExists('import_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('promos');
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
