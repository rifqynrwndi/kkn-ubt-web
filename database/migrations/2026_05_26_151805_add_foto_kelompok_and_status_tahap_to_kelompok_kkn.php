<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelompok_kkn', function (Blueprint $table) {
            $table->string('foto_kelompok')->nullable()->after('desa_gelombang_id');
            $table->unsignedTinyInteger('status_tahap')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('kelompok_kkn', function (Blueprint $table) {
            $table->dropColumn(['foto_kelompok', 'status_tahap']);
        });
    }
};
