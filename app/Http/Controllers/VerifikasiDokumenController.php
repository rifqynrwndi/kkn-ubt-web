<?php

namespace App\Http\Controllers;

use App\Models\DokumenPendaftaran;
use App\Models\PesertaKkn;
use Illuminate\Http\Request;
use App\Notifications\DokumenVerifiedNotification;
use Illuminate\Support\Facades\DB;
use App\Notifications\BulkDokumenVerifiedNotification;

class VerifikasiDokumenController extends Controller
{
    public function index(Request $request)
    {
        $gelombangId = $request->get('gelombang_id');

        $gelombangs = \App\Models\Gelombang::orderBy('tahun', 'desc')
            ->orderBy('nama_gelombang')
            ->get();

        $pesertaList = collect();

        if ($gelombangId) {
            $statusFilter = $request->get('status');

            $pesertaList = PesertaKkn::with([
                'mahasiswa.user',
                'gelombang',
                'dokumenPendaftaran'
            ])
            ->where('gelombang_id', $gelombangId)
            ->when($statusFilter, fn($q) => $q->where('status_pendaftaran', $statusFilter))
            ->when(!$statusFilter, fn($q) => $q->whereIn('status_pendaftaran', [
                'draft', 'pending_documents', 'pending_verification',
                'revision', 'approved', 'rejected', 'expired'
            ]))
            ->latest()
            ->paginate(20)
            ->appends(['gelombang_id' => $gelombangId, 'status' => $statusFilter]);
        }

        return view('verifikasi-dokumen.index', compact('pesertaList', 'gelombangs', 'gelombangId'));
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

        $dokumen->pesertaKkn->mahasiswa->user
            ->notify(new DokumenVerifiedNotification($dokumen));

        return redirect()->route('verifikasi-dokumen.show', $dokumen->pesertaKkn->id)
            ->with('success', 'Status verifikasi dokumen berhasil diperbarui.');
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

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'peserta_ids' => 'required|array',
            'peserta_ids.*' => 'exists:peserta_kkn,id',
        ]);

        DB::transaction(function () use ($request) {

            $pesertaList = PesertaKkn::with([
                'dokumenPendaftaran',
                'mahasiswa.user'
            ])
            ->whereIn('id', $request->peserta_ids)
            ->get();

            foreach ($pesertaList as $peserta) {

                foreach ($peserta->dokumenPendaftaran as $dokumen) {

                    if ($dokumen->status_verifikasi !== 'verified') {

                        $dokumen->update([
                            'status_verifikasi' => 'verified',
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                            'catatan_revisi' => null,
                        ]);

                    }

                }

                $this->syncPesertaStatus($peserta);

                $peserta->mahasiswa->user->notify(
                    new BulkDokumenVerifiedNotification($peserta)
                );
            }

        });

        return back()->with(
            'success',
            'Peserta berhasil diverifikasi secara massal.'
        );
    }

    public function bulkUpdate(Request $request, $pesertaId)
    {
        $request->validate([
            'documents' => 'required|array',
            'documents.*.status_verifikasi' =>
                'required|in:verified,revision_required,rejected',

            'documents.*.catatan_revisi' =>
                'nullable|string',
        ]);

        DB::transaction(function () use ($request, $pesertaId) {

            $peserta = PesertaKkn::with([
                'dokumenPendaftaran',
                'mahasiswa.user'
            ])->findOrFail($pesertaId);

            foreach ($request->documents as $dokumenId => $data) {

                $dokumen = DokumenPendaftaran::where(
                    'id',
                    $dokumenId
                )
                ->where(
                    'peserta_kkn_id',
                    $peserta->id
                )
                ->first();

                if (!$dokumen) {
                    continue;
                }

                $dokumen->update([
                    'status_verifikasi' =>
                        $data['status_verifikasi'],

                    'catatan_revisi' =>
                        $data['catatan_revisi'] ?? null,

                    'verified_by' => auth()->id(),

                    'verified_at' => now(),
                ]);

            }

            $this->syncPesertaStatus(
                $peserta->fresh()
            );

            $peserta->mahasiswa->user->notify(
                new BulkDokumenVerifiedNotification(
                    $peserta->fresh()
                )
            );

        });

        return back()->with(
            'success',
            'Semua perubahan verifikasi berhasil disimpan.'
        );
    }
}
