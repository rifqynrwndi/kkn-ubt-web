<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('war_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // WAR KKN 2026
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('war_sessions');
    }
};
