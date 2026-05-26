<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelompok_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_kkn_id')->constrained('kelompok_kkn')->cascadeOnDelete();
            $table->unsignedTinyInteger('status_lama');
            $table->unsignedTinyInteger('status_baru');
            $table->text('keterangan')->nullable();
            $table->foreignId('changed_by')->constrained('users');
            $table->string('changed_by_role', 50);
            $table->timestamps();

            $table->index('kelompok_kkn_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelompok_status_history');
    }
};
