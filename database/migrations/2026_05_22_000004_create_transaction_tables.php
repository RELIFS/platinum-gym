<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 12, 2);
            $table->string('status', 20);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index('end_date');
            $table->index('status');
        });

        Schema::create('member_package_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->unsignedSmallInteger('total_sessions');
            $table->unsignedSmallInteger('used_sessions')->default(0);
            $table->unsignedSmallInteger('remaining_sessions');
            $table->decimal('price', 12, 2);
            $table->date('started_at')->nullable();
            $table->date('expired_at')->nullable();
            $table->string('status', 20);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index('package_id');
            $table->index('trainer_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_code', 30)->unique();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('payable_type', 150);
            $table->unsignedBigInteger('payable_id');
            $table->string('method', 30);
            $table->decimal('amount', 12, 2);
            $table->string('status', 30);
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->string('midtrans_order_id', 100)->nullable()->unique();
            $table->string('midtrans_transaction_id', 100)->nullable();
            $table->string('midtrans_payment_type', 50)->nullable();
            $table->json('midtrans_raw_response')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id']);
            $table->index(['member_id', 'status']);
            $table->index(['method', 'status']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('invoice_number', 30)->unique();
            $table->date('issued_at');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('status', 20)->default('issued');
            $table->string('pdf_path')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('member_package_sessions');
        Schema::dropIfExists('memberships');
    }
};
