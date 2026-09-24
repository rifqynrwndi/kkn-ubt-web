<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->string('birth_place')->nullable()->after('no_hp');
            $table->date('birth_date')->nullable()->after('birth_place');
        });

        Schema::table('gelombang', function (Blueprint $table) {
            $table->string('telegram_group_url')->nullable()->after('required_documents');
        });

        // Force all existing users to re-complete biodata with new fields
        DB::table('mahasiswa')->update(['is_biodata_complete' => false]);
    }

    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn(['birth_place', 'birth_date']);
        });

        Schema::table('gelombang', function (Blueprint $table) {
            $table->dropColumn('telegram_group_url');
        });
    }
};
