<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gelombang', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | SKIP DOKUMEN FLAG
            |--------------------------------------------------------------------------
            | Jika true, mahasiswa di gelombang ini tidak perlu upload dokumen.
            | Status pendaftaran langsung di-set 'approved' saat register.
            | Dipakai untuk gelombang yang datanya sudah diimport dari sistem lama.
            */
            $table->boolean('skip_dokumen')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('gelombang', function (Blueprint $table) {
            $table->dropColumn('skip_dokumen');
        });
    }
};
