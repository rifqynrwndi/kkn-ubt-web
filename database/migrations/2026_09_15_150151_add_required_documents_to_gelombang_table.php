<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gelombang', function (Blueprint $table) {
            $table->json('required_documents')->nullable()->after('skip_dokumen');
        });

        // Set default: all 5 documents for existing gelombangs
        DB::table('gelombang')
            ->whereNull('required_documents')
            ->update([
                'required_documents' => json_encode([
                    'dhs', 'surat_pernyataan', 'surat_ortu', 'surat_vaksin', 'surat_dokter',
                ]),
            ]);
    }

    public function down(): void
    {
        Schema::table('gelombang', function (Blueprint $table) {
            $table->dropColumn('required_documents');
        });
    }
};
