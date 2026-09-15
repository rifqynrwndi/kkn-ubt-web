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
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->string('dhs_path')->nullable()->after('is_biodata_complete');
            $table->enum('dhs_status', ['pending', 'verified', 'rejected'])->default('pending')->after('dhs_path');
            $table->foreignId('dhs_verified_by')->nullable()->constrained('users')->nullOnDelete()->after('dhs_status');
            $table->timestamp('dhs_verified_at')->nullable()->after('dhs_verified_by');
            $table->text('dhs_catatan')->nullable()->after('dhs_verified_at');
        });

        // Grandfather existing users: set dhs_status to 'verified'
        DB::table('mahasiswa')->update(['dhs_status' => 'verified']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropForeign(['dhs_verified_by']);
            $table->dropColumn([
                'dhs_path',
                'dhs_status',
                'dhs_verified_by',
                'dhs_verified_at',
                'dhs_catatan',
            ]);
        });
    }
};
