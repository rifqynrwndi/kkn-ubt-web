<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelompok_kkn', function (Blueprint $table) {

            $table->foreignId('ketua_peserta_id')
                ->nullable()
                ->after('dosen_pembimbing_lapangan_id')
                ->constrained('peserta_kkn')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('kelompok_kkn', function (Blueprint $table) {

            $table->dropForeign([
                'ketua_peserta_id'
            ]);

            $table->dropColumn(
                'ketua_peserta_id'
            );

        });
    }
};
