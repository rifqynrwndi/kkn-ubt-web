<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_kelompok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->enum('kategori', ['tugas_kelompok', 'luaran_wajib', 'luaran_lain', 'laporan'])->default('tugas_kelompok');
            $table->string('nama_tugas');
            $table->text('deskripsi')->nullable();
            $table->date('periode_mulai')->nullable();
            $table->date('periode_akhir')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->index('kategori');
        });

        Schema::create('tugas_submission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_kelompok_id')->constrained('tugas_kelompok')->cascadeOnDelete();
            $table->foreignId('peserta_kkn_id')->constrained('peserta_kkn');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedInteger('file_size')->nullable();
            $table->enum('status', ['menunggu', 'diterima', 'ditolak', 'revisi'])->default('menunggu');
            $table->text('komentar_dpl')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index('peserta_kkn_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_submission');
        Schema::dropIfExists('tugas_kelompok');
    }
};
