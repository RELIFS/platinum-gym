<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->string('midtrans_snap_token', 255)->nullable()->after('midtrans_payment_type');
            $table->string('midtrans_redirect_url', 500)->nullable()->after('midtrans_snap_token');
            $table->timestamp('expires_at')->nullable()->after('midtrans_redirect_url');
            $table->text('failure_reason')->nullable()->after('expires_at');

            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex(['expires_at']);
            $table->dropColumn([
                'midtrans_snap_token',
                'midtrans_redirect_url',
                'expires_at',
                'failure_reason',
            ]);
        });
    }
};
