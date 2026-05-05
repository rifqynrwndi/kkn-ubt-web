<?php

namespace App\Http\Controllers;

use App\Models\DokumenPendaftaran;
use App\Models\PesertaKkn;
use Illuminate\Http\Request;

class VerifikasiDokumenController extends Controller
{
    public function index()
    {
        $pesertaList = PesertaKkn::with([
            'mahasiswa.user',
            'gelombang',
            'dokumenPendaftaran'
        ])
        ->whereIn('status_pendaftaran', [
            'draft',
            'pending_documents',
            'pending_verification',
            'revision_required',
            'approved',
            'rejected',
            'expired'
        ])
        ->latest()
        ->paginate(20);

        return view('verifikasi-dokumen.index', compact('pesertaList'));
    }

    public function show($id)
    {
        $peserta = PesertaKkn::with([
            'mahasiswa.user',
            'gelombang',
            'dokumenPendaftaran.file'
        ])->findOrFail($id);

        return view('verifikasi-dokumen.show', compact('peserta'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status_verifikasi' => 'required|in:verified,revision_required,rejected',
            'catatan_revisi' => 'nullable|string'
        ]);

        $dokumen = DokumenPendaftaran::findOrFail($id);

        $dokumen->update([
            'status_verifikasi' => $request->status_verifikasi,
            'catatan_revisi' => $request->catatan_revisi,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $this->syncPesertaStatus($dokumen->pesertaKkn);

        return back()->with('success', 'Dokumen berhasil diverifikasi.');
    }

    private function syncPesertaStatus(PesertaKkn $peserta)
    {
        $requiredDokumen = DokumenPendaftaran::REQUIRED_DOCUMENTS;

        $dokumen = $peserta->dokumenPendaftaran;

        $uploadedJenis = $dokumen
            ->pluck('jenis_dokumen')
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Belum Upload Semua Dokumen
        |--------------------------------------------------------------------------
        */
        if (count($uploadedJenis) < count($requiredDokumen)) {
            $peserta->update([
                'status_pendaftaran' => 'pending_documents'
            ]);
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Ada Dokumen Ditolak
        |--------------------------------------------------------------------------
        */
        if ($dokumen->contains('status_verifikasi', 'rejected')) {
            $peserta->update([
                'status_pendaftaran' => 'rejected'
            ]);
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Ada Dokumen Perlu Revisi
        |--------------------------------------------------------------------------
        */
        if ($dokumen->contains('status_verifikasi', 'revision_required')) {
            $peserta->update([
                'status_pendaftaran' => 'revision'
            ]);
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Semua Verified
        |--------------------------------------------------------------------------
        */
        if ($dokumen->every(fn($d) => $d->status_verifikasi === 'verified')) {
            $peserta->update([
                'status_pendaftaran' => 'approved'
            ]);
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Default
        |--------------------------------------------------------------------------
        */
        $peserta->update([
            'status_pendaftaran' => 'pending_verification'
        ]);
    }
}
