<?php

namespace App\Services;

use App\Models\PesertaKkn;

class DocumentVerificationService
{
    /**
     * Sync peserta's status_pendaftaran based on document verification states.
     *
     * Expects $peserta to have dokumenPendaftaran relation loaded.
     * Loads gelombang relationship if not already loaded.
     *
     * @return string The new status_pendaftaran value
     */
    public function syncPesertaStatus(PesertaKkn $peserta): string
    {
        $peserta->loadMissing('gelombang');

        $requiredDokumen = $peserta->gelombang->getRequiredDocumentTypesAttribute();

        $dokumen = $peserta->dokumenPendaftaran;

        $uploadedJenis = $dokumen
            ->pluck('jenis_dokumen')
            ->unique()
            ->values()
            ->toArray();

        if (count(array_intersect($requiredDokumen, $uploadedJenis)) < count($requiredDokumen)) {
            $peserta->update(['status_pendaftaran' => 'pending_documents']);

            return 'pending_documents';
        }

        $requiredDokumen = $dokumen->filter(fn ($d) => in_array($d->jenis_dokumen, $requiredDokumen));

        if ($requiredDokumen->contains('status_verifikasi', 'rejected')) {
            $peserta->update(['status_pendaftaran' => 'rejected']);

            return 'rejected';
        }

        if ($requiredDokumen->contains('status_verifikasi', 'revision_required')) {
            $peserta->update(['status_pendaftaran' => 'revision']);

            return 'revision';
        }

        if ($requiredDokumen->every(fn ($d) => $d->status_verifikasi === 'verified')) {
            $peserta->update(['status_pendaftaran' => 'approved']);

            return 'approved';
        }

        $peserta->update(['status_pendaftaran' => 'pending_verification']);

        return 'pending_verification';
    }
}
