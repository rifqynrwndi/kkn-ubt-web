<?php

namespace App\Http\Controllers;

use App\Models\DokumenPendaftaran;
use App\Models\File;
use App\Models\PesertaKkn;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class DokumenPendaftaranController extends Controller
{
    private array $requiredDocuments = [
        'ktm',
        'transkrip',
        'surat_sehat',
        'pas_foto',
    ];

    public function index(): View
    {
        $peserta = PesertaKkn::where('mahasiswa_id', auth()->id())
            ->latest()
            ->firstOrFail();

        $dokumen = $peserta->dokumenPendaftaran()
            ->with('file')
            ->get();

        return view('dokumen-pendaftaran.index', compact('peserta', 'dokumen'));
    }

    public function show($id)
    {
        $dokumen = DokumenPendaftaran::with('file', 'pesertaKkn')->findOrFail($id);

        if (
            auth()->id() !== $dokumen->pesertaKkn->mahasiswa_id &&
            !auth()->user()->hasRole('superadmin')
        ) {
            abort(403);
        }

        if (!$dokumen->file || !Storage::disk('local')->exists($dokumen->file->path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('local')->response(
            $dokumen->file->path,
            $dokumen->file->original_name
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'jenis_dokumen' => 'required|string|in:ktm,transkrip,surat_sehat,pas_foto',
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $peserta = PesertaKkn::where('mahasiswa_id', auth()->id())
            ->latest()
            ->firstOrFail();

        $path = $request->file('file')->store('dokumen-pendaftaran', 'local');

        $file = File::create([
            'user_id'       => auth()->id(),
            'name'          => $request->file('file')->hashName(),
            'original_name' => $request->file('file')->getClientOriginalName(),
            'path'          => $path,
            'mime_type'     => $request->file('file')->getMimeType(),
            'size'          => $request->file('file')->getSize(),
            'extension'     => $request->file('file')->getClientOriginalExtension(),
            'folder'        => 'dokumen-pendaftaran',
            'is_public'     => false,
        ]);

        $existing = DokumenPendaftaran::where([
            'peserta_kkn_id' => $peserta->id,
            'jenis_dokumen'  => $request->jenis_dokumen,
        ])->first();

        if ($existing && $existing->file) {
            Storage::disk('local')->delete($existing->file->path);
            $existing->file->delete();
        }

        DokumenPendaftaran::updateOrCreate(
            [
                'peserta_kkn_id' => $peserta->id,
                'jenis_dokumen'  => $request->jenis_dokumen,
            ],
            [
                'file_id'            => $file->id,
                'status_verifikasi'  => 'pending',
                'verified_by'        => null,
                'verified_at'        => null,
                'catatan_revisi'     => null,
            ]
        );

        $this->updateStatusPeserta($peserta);

        return back()->with('success', 'Dokumen berhasil diupload.');
    }

    public function destroy($id): RedirectResponse
    {
        $dokumen = DokumenPendaftaran::with('file', 'pesertaKkn')->findOrFail($id);

        if ($dokumen->file) {
            Storage::disk('local')->delete($dokumen->file->path);
            $dokumen->file->delete();
        }

        $peserta = $dokumen->pesertaKkn;

        $dokumen->delete();

        $this->updateStatusPeserta($peserta);

        return back()->with('success', 'Dokumen dihapus.');
    }

    private function updateStatusPeserta(PesertaKkn $peserta): void
    {
        $uploadedTypes = $peserta->dokumenPendaftaran()
            ->pluck('jenis_dokumen')
            ->toArray();

        $missing = array_diff($this->requiredDocuments, $uploadedTypes);

        if (count($uploadedTypes) === 0) {
            $peserta->update([
                'status_pendaftaran' => 'draft',
            ]);
            return;
        }

        if (count($missing) > 0) {
            $peserta->update([
                'status_pendaftaran' => 'pending_documents',
            ]);
            return;
        }

        $peserta->update([
            'status_pendaftaran' => 'pending_verification',
        ]);
    }
}
