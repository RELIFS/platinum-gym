<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->string('student_proof_path')->nullable()->after('student_id_number');
            $table->timestamp('student_proof_uploaded_at')->nullable()->after('student_proof_path');

            $table->index('student_proof_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->dropIndex(['student_proof_uploaded_at']);
            $table->dropColumn(['student_proof_path', 'student_proof_uploaded_at']);
        });
    }
};
