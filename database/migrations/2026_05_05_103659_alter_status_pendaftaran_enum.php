<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("
            ALTER TABLE peserta_kkn
            MODIFY status_pendaftaran ENUM(
                'draft',
                'pending_documents',
                'pending_verification',
                'revision',
                'approved',
                'rejected',
                'expired'
            ) DEFAULT 'draft'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
