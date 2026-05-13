<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('war_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_session_id')->constrained('war_sessions')->cascadeOnDelete();
            $table->foreignId('peserta_kkn_id')->constrained('peserta_kkn')->cascadeOnDelete();
            $table->foreignId('kelompok_kkn_id')->nullable()->constrained('kelompok_kkn')->nullOnDelete();

            $table->enum('status', [
                'waiting',
                'joined',
                'failed',
            ])->default('waiting');

            $table->timestamp('joined_at')->nullable();

            $table->timestamps();

            $table->unique(['war_session_id', 'peserta_kkn_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('war_participants');
    }
};
