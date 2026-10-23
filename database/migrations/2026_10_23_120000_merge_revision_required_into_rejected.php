<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Konversi dokumen revision_required -> rejected
        DB::table('dokumen_pendaftaran')
            ->where('status_verifikasi', 'revision_required')
            ->update(['status_verifikasi' => 'rejected']);

        // 2. Perketat enum dokumen menjadi 3 nilai
        DB::statement(
            "ALTER TABLE dokumen_pendaftaran MODIFY status_verifikasi ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending'"
        );

        // 3. Hitung ulang peserta yang masih berstatus 'revision'
        $pesertas = DB::table('peserta_kkn')
            ->where('status_pendaftaran', 'revision')
            ->get(['id', 'gelombang_id']);

        foreach ($pesertas as $peserta) {
            $gelombang = DB::table('gelombang')
                ->where('id', $peserta->gelombang_id)
                ->first();

            $required = is_string($gelombang->required_documents ?? null)
                ? json_decode($gelombang->required_documents, true)
                : null;
            $required = is_array($required) ? $required : [
                'surat_pernyataan', 'surat_ortu', 'surat_dokter',
            ];

            $uploaded = DB::table('dokumen_pendaftaran')
                ->where('peserta_kkn_id', $peserta->id)
                ->pluck('jenis_dokumen')
                ->unique()
                ->all();

            $status = 'pending_verification';

            if (count(array_diff($required, $uploaded)) > 0) {
                $status = 'pending_documents';
            } elseif (DB::table('dokumen_pendaftaran')
                ->where('peserta_kkn_id', $peserta->id)
                ->whereIn('jenis_dokumen', $required)
                ->where('status_verifikasi', 'rejected')
                ->exists()) {
                $status = 'rejected';
            }

            DB::table('peserta_kkn')
                ->where('id', $peserta->id)
                ->update(['status_pendaftaran' => $status]);
        }
    }

    public function down(): void
    {
        // Kembalikan skema enum, data rejected tidak bisa dipulihkan
        DB::statement(
            "ALTER TABLE dokumen_pendaftaran MODIFY status_verifikasi ENUM('pending','verified','revision_required','rejected') NOT NULL DEFAULT 'pending'"
        );
    }
};
