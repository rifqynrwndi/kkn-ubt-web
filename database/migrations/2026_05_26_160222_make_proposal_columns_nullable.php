<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelompok_proposal', function (Blueprint $table) {
            $table->text('pendahuluan')->nullable()->change();
            $table->text('tujuan')->nullable()->change();
            $table->text('manfaat')->nullable()->change();
            $table->text('rancangan_program')->nullable()->change();
            $table->text('solusi_ide')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kelompok_proposal', function (Blueprint $table) {
            $table->text('pendahuluan')->nullable(false)->change();
            $table->text('tujuan')->nullable(false)->change();
            $table->text('manfaat')->nullable(false)->change();
            $table->text('rancangan_program')->nullable(false)->change();
            $table->text('solusi_ide')->nullable(false)->change();
        });
    }
};
