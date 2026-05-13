<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('war_faculties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fakultas_id')->constrained()->cascadeOnDelete();

            $table->integer('quota')->default(300);
            $table->integer('filled')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('war_faculties');
    }
};
