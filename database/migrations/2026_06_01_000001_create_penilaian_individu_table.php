<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_individu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->foreignId('peserta_kkn_id')->constrained('peserta_kkn')->cascadeOnDelete();
            $table->foreignId('komponen_id')->constrained('penilaian_komponen')->cascadeOnDelete();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->foreignId('input_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['kelompok_kkn_id', 'peserta_kkn_id', 'komponen_id'], 'penilaian_individu_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_individu');
    }
};
