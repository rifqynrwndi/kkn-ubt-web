<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dosen_pembimbing_lapangan', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('dosen_pembimbing_lapangan', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
