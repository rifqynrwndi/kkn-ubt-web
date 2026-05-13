<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('war_sessions', function (Blueprint $table) {

            // hapus is_active
            $table->dropColumn('is_active');

            // tambah status
            $table->enum('status', [
                'scheduled',
                'active',
                'closed'
            ])->default('scheduled');

            // relasi gelombang
            $table->foreignId('gelombang_id')
                ->nullable()
                ->after('name')
                ->constrained('gelombang')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('war_sessions', function (Blueprint $table) {

            $table->dropForeign(['gelombang_id']);

            $table->dropColumn([
                'gelombang_id',
                'status'
            ]);

            $table->boolean('is_active')
                ->default(false);

        });
    }
};
