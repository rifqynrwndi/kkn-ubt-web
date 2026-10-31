<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('war_faculty_shards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fakultas_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('shard_index'); // 0-9
            $table->unsignedInteger('filled')->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['war_session_id', 'fakultas_id', 'shard_index'], 'uk_war_faculty_shards_session_fakultas_shard');
            $table->index(['war_session_id', 'fakultas_id'], 'idx_war_faculty_shards_session_fakultas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('war_faculty_shards');
    }
};
