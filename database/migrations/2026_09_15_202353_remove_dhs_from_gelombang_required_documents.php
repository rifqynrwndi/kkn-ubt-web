<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $gelombangs = DB::table('gelombang')
            ->whereNotNull('required_documents')
            ->get();

        foreach ($gelombangs as $gelombang) {
            $docs = json_decode($gelombang->required_documents, true);
            if (is_array($docs)) {
                $docs = array_values(array_diff($docs, ['dhs']));
                DB::table('gelombang')
                    ->where('id', $gelombang->id)
                    ->update(['required_documents' => json_encode($docs)]);
            }
        }
    }

    public function down(): void
    {
        // Cannot reliably restore DHS to previous state
    }
};
