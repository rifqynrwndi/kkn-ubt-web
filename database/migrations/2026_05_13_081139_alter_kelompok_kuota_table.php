<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelompok_kuota', function (Blueprint $table) {
            $table->unsignedTinyInteger('kuota')->default(2)->after('fakultas_id');
        });
    }

    public function down(): void
    {
        Schema::table('kelompok_kuota', function (Blueprint $table) {
            $table->dropColumn('kuota');
        });
    }
};
