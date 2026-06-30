<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_book', function (Blueprint $table) {
            $table->enum('status', ['menunggu', 'tervalidasi', 'ditolak'])->default('menunggu')->after('is_validated');
            $table->text('komentar_dpl')->nullable()->after('status');
        });

        // Migrate existing data: is_validated=true -> status=tervalidasi
        DB::table('log_book')->where('is_validated', true)->update(['status' => 'tervalidasi']);
    }

    public function down(): void
    {
        Schema::table('log_book', function (Blueprint $table) {
            $table->dropColumn(['status', 'komentar_dpl']);
        });
    }
};
