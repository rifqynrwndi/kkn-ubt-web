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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->primary()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('npm')->unique();

            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('no_hp')->nullable();
            $table->string('foto')->nullable();

            $table->foreignId('prodi_id')
                ->nullable()
                ->constrained('program_studi')
                ->nullOnDelete();

            $table->string('nama_ortu')->nullable();
            $table->string('no_hp_ortu')->nullable();
            $table->text('alamat_ortu')->nullable();

            $table->boolean('is_biodata_complete')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
