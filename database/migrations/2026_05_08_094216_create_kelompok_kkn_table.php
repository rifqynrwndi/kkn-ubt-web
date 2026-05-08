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
        Schema::create('kelompok_kkn', function (Blueprint $table) {

        $table->id();

        $table->foreignId('desa_gelombang_id')
            ->constrained('desa_gelombang')
            ->cascadeOnDelete();

        $table->foreignId('dosen_pembimbing_lapangan_id')
            ->nullable()
            ->constrained('dosen_pembimbing_lapangan')
            ->nullOnDelete();

        $table->string('nama_kelompok');

        $table->integer('nomor_kelompok');

        $table->integer('kuota')->default(12);

        $table->enum('status', [
            'draft',
            'dibuka',
            'penuh',
            'ditutup',
        ])->default('draft');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompok_kkn');
    }
};
