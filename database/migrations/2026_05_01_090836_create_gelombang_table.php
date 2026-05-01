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
        Schema::create('gelombang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_gelombang');
            $table->year('tahun');
            $table->unique(['nama_gelombang', 'tahun']);
            $table->date('tgl_mulai');
            $table->date('tgl_akhir');

            $table->enum('status', [
                'persiapan',
                'pendaftaran',
                'berjalan',
                'selesai'
            ])->default('persiapan');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gelombang');
    }
};
