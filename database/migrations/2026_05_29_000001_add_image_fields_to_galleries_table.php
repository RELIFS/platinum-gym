<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            if (! Schema::hasColumn('galleries', 'image_path')) {
                $table->string('image_path')->nullable();
            }

            if (! Schema::hasColumn('galleries', 'image_alt')) {
                $table->string('image_alt', 180)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            if (Schema::hasColumn('galleries', 'image_alt')) {
                $table->dropColumn('image_alt');
            }

            if (Schema::hasColumn('galleries', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
