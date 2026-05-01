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
        Schema::create('dokumen_pendaftaran', function (Blueprint $table) {
            $table->id();

            $table->foreignId('peserta_kkn_id')
                ->constrained('peserta_kkn')
                ->cascadeOnDelete();

            $table->foreignId('file_id')
                ->constrained('files')
                ->cascadeOnDelete();

            $table->string('jenis_dokumen');

            $table->unique(['peserta_kkn_id', 'jenis_dokumen']);

            $table->enum('status_verifikasi', [
                'pending',
                'verified',
                'revision_required',
                'rejected'
            ])->default('pending');

            $table->text('catatan_revisi')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_pendaftaran');
    }
};
