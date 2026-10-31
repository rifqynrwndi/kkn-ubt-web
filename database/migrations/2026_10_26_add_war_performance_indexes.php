<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // war_faculties: composite index for every join lookup
        Schema::table('war_faculties', function (Blueprint $table) {
            $table->index(['war_session_id', 'fakultas_id'], 'idx_war_faculty_session_fakultas');
        });

        // war_logs: ordering for monitor logs endpoint
        Schema::table('war_logs', function (Blueprint $table) {
            $table->index(['war_session_id', 'created_at'], 'idx_war_logs_session_created');
        });

        // war_participants: monitor stats ordering
        Schema::table('war_participants', function (Blueprint $table) {
            $table->index(['war_session_id', 'joined_at'], 'idx_war_participants_session_joined');
        });

        // kelompok_kuota: prevent double-create race (Candidate 1)
        Schema::table('kelompok_kuota', function (Blueprint $table) {
            $table->unique(['kelompok_kkn_id', 'fakultas_id'], 'uk_kelompok_kuota_kelompok_fakultas');
        });
    }

    public function down(): void
    {
        Schema::table('war_faculties', function (Blueprint $table) {
            $table->dropIndex('idx_war_faculty_session_fakultas');
        });

        Schema::table('war_logs', function (Blueprint $table) {
            $table->dropIndex('idx_war_logs_session_created');
        });

        Schema::table('war_participants', function (Blueprint $table) {
            $table->dropIndex('idx_war_participants_session_joined');
        });

        Schema::table('kelompok_kuota', function (Blueprint $table) {
            $table->dropUnique('uk_kelompok_kuota_kelompok_fakultas');
        });
    }
};
