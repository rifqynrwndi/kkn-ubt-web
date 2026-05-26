<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelompok_proposal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->text('pendahuluan');
            $table->text('tujuan');
            $table->text('manfaat');
            $table->text('hasil_observasi')->nullable();
            $table->text('rancangan_program');
            $table->text('solusi_ide');
            $table->enum('status', ['draft', 'diajukan', 'disetujui', 'ditolak'])->default('draft');
            $table->foreignId('submitted_by')->nullable()->constrained('peserta_kkn');
            $table->timestamp('submitted_at')->nullable();
            $table->text('komentar_dpl')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelompok_proposal');
    }
};
