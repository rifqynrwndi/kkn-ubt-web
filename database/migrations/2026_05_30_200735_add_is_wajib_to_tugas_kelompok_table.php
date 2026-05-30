<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tugas_kelompok', function (Blueprint $table) {
            $table->boolean('is_wajib')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('tugas_kelompok', function (Blueprint $table) {
            $table->dropColumn('is_wajib');
        });
    }
};
