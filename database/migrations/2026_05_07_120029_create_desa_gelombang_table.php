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
        Schema::create('desa_gelombang', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gelombang_id')
                ->constrained('gelombang')
                ->cascadeOnDelete();

            $table->foreignId('desa_id')
                ->constrained('desa')
                ->cascadeOnDelete();

            $table->integer('kuota_total')
                ->default(12);

            $table->enum('status', [
                'draft',
                'dibuka',
                'ditutup',
                'penuh'
            ])->default('draft');

            $table->foreignId('dosen_pembimbing_lapangan_id')
                ->nullable()
                ->constrained('dosen_pembimbing_lapangan')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desa_gelombang');
    }
};
