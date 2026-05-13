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
        Schema::create('kelompok_kuota', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kelompok_kkn_id')
                ->constrained('kelompok_kkn')
                ->cascadeOnDelete();

            $table->foreignId('fakultas_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->integer('kuota_laki')->default(0);
            $table->integer('kuota_perempuan')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompok_kuotas');
    }
};
