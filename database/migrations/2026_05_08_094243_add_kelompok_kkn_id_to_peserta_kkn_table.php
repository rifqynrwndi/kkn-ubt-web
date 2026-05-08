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
        Schema::table('peserta_kkn', function (Blueprint $table) {

            $table->foreignId('kelompok_kkn_id')
                ->nullable()
                ->after('gelombang_id')
                ->constrained('kelompok_kkn')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta_kkn', function (Blueprint $table) {
            //
        });
    }
};
