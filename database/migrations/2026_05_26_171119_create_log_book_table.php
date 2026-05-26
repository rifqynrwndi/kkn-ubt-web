<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_book', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_kkn_id')->constrained('peserta_kkn')->cascadeOnDelete();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->boolean('is_validated')->default(false);
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->index('peserta_kkn_id');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_book');
    }
};
