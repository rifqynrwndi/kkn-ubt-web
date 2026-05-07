<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_pembimbing_lapangan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('nidn')->nullable();

            $table->foreignId('fakultas_id')
                ->nullable()
                ->constrained('fakultas')
                ->nullOnDelete();

            $table->string('no_hp')->nullable();

            $table->enum('jenis_kelamin', [
                'laki_laki',
                'perempuan'
            ])->nullable();

            $table->text('alamat')->nullable();

            $table->enum('status', [
                'aktif',
                'nonaktif'
            ])->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_pembimbing_lapangan');
    }
};
