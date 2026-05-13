<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('war_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_session_id')->constrained('war_sessions')->cascadeOnDelete();
            $table->foreignId('peserta_kkn_id')->constrained('peserta_kkn')->cascadeOnDelete();

            $table->string('action'); // join / fail / timeout
            $table->text('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('war_logs');
    }
};
