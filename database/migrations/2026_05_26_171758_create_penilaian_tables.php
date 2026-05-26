<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_komponen', function (Blueprint $table) {
            $table->id();
            $table->string('nama_komponen');
            $table->text('deskripsi')->nullable();
            $table->enum('kategori', ['dpl', 'lppm']);
            $table->unsignedInteger('bobot')->comment('Weight percentage');
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('penilaian_kelompok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->foreignId('komponen_id')->constrained('penilaian_komponen');
            $table->decimal('nilai', 5, 2)->nullable();
            $table->foreignId('input_by')->nullable()->constrained('users');
            $table->timestamp('input_at')->nullable();
            $table->timestamps();
            $table->unique(['kelompok_kkn_id', 'komponen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_kelompok');
        Schema::dropIfExists('penilaian_komponen');
    }
};
