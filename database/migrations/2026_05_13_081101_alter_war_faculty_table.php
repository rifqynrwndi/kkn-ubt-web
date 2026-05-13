<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('war_faculties', function (Blueprint $table) {
            $table->timestamp('start_at')->nullable()->after('filled');
            $table->timestamp('end_at')->nullable()->after('start_at');
        });
    }

    public function down(): void
    {
        Schema::table('war_faculties', function (Blueprint $table) {
            $table->dropColumn(['start_at', 'end_at']);
        });
    }
};
