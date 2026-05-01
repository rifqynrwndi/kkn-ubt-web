<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peserta_kkn', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mahasiswa_id')
                ->constrained('mahasiswa', 'user_id')
                ->cascadeOnDelete();

            $table->foreignId('gelombang_id')
                ->constrained('gelombang')
                ->cascadeOnDelete();

            $table->enum('status_pendaftaran', [
                'draft',
                'submitted',
                'revision_required',
                'verified',
                'rejected'
            ])->default('draft');

            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->text('catatan_admin')->nullable();

            $table->timestamps();

            $table->unique(['mahasiswa_id', 'gelombang_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_kkn');
    }
};
