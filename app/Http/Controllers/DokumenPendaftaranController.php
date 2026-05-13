<?php

namespace App\Http\Controllers;

use App\Models\DokumenPendaftaran;
use App\Models\File;
use App\Models\NotificationLog;
use App\Models\PesertaKkn;
use App\Models\User;
use App\Notifications\DokumenUploadedNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class DokumenPendaftaranController extends Controller
{
    private array $requiredDocuments = [
        'dhs',
        'surat_pernyataan',
        'surat_ortu',
        'surat_vaksin',
        'surat_dokter',
    ];

    public function index(): View
    {
        $peserta = PesertaKkn::where(
                'mahasiswa_id',
                auth()->id()
            )
            ->latest()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Belum Daftar KKN
        |--------------------------------------------------------------------------
        */
        if (! $peserta) {

            return view('dokumen-pendaftaran.index', [
                'peserta'           => null,
                'dokumen'           => collect(),
                'requiredDocuments' => [],
                'uploadedDocuments' => [],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Dokumen Mahasiswa
        |--------------------------------------------------------------------------
        */
        $dokumen = $peserta->dokumenPendaftaran()
            ->with([
                'file',
                'verifier',
            ])
            ->get();

        $requiredDocuments = DokumenPendaftaran::getDocumentLabels();

        $uploadedDocuments = $dokumen->keyBy(
            'jenis_dokumen'
        );

        return view('dokumen-pendaftaran.index', compact(
            'peserta',
            'dokumen',
            'requiredDocuments',
            'uploadedDocuments'
        ));
    }

    public function show($id)
    {
        $dokumen = DokumenPendaftaran::with([
                'file',
                'pesertaKkn',
            ])
            ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */
        if (
            auth()->id() !== $dokumen->pesertaKkn->mahasiswa_id &&
            ! auth()->user()->hasRole('superadmin')
        ) {

            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | File Not Found
        |--------------------------------------------------------------------------
        */
        if (
            ! $dokumen->file ||
            ! Storage::disk('local')->exists(
                $dokumen->file->path
            )
        ) {

            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('local')->response(
            $dokumen->file->path,
            $dokumen->file->original_name
        );
    }

    public function create(): View|RedirectResponse
    {
        $peserta = PesertaKkn::where(
                'mahasiswa_id',
                auth()->id()
            )
            ->latest()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Belum Daftar KKN
        |--------------------------------------------------------------------------
        */
        if (! $peserta) {

            return redirect()
                ->route('pendaftaran-kkn.index')
                ->with(
                    'error',
                    'Silakan daftar KKN terlebih dahulu.'
                );
        }

        $uploadedTypes = $peserta->dokumenPendaftaran()
            ->pluck('jenis_dokumen')
            ->toArray();

        $documents = DokumenPendaftaran::getDocumentLabels();

        $missingDocuments = collect(
                array_keys($documents)
            )
            ->diff($uploadedTypes)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Semua Dokumen Sudah Upload
        |--------------------------------------------------------------------------
        */
        if ($missingDocuments->isEmpty()) {

            return redirect()
                ->route('dokumen-pendaftaran.index')
                ->with(
                    'info',
                    'Semua dokumen sudah diupload.'
                );
        }

        $defaultDocument = $missingDocuments->first();

        return view('dokumen-pendaftaran.create', compact(
            'peserta',
            'documents',
            'uploadedTypes',
            'defaultDocument'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $peserta = PesertaKkn::where(
                'mahasiswa_id',
                auth()->id()
            )
            ->latest()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Belum Daftar KKN
        |--------------------------------------------------------------------------
        */
        if (! $peserta) {

            return redirect()
                ->route('pendaftaran-kkn.index')
                ->with(
                    'error',
                    'Silakan daftar KKN terlebih dahulu.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Sudah Approved
        |--------------------------------------------------------------------------
        */
        if ($peserta->status_pendaftaran === 'approved') {

            return back()->with(
                'error',
                'Dokumen sudah disetujui, tidak dapat diubah.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'jenis_dokumen' =>
                'required|string|in:dhs,surat_pernyataan,surat_ortu,surat_vaksin,surat_dokter',

            'file' =>
                'required|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Upload File
        |--------------------------------------------------------------------------
        */
        $path = $request->file('file')->store(
            'dokumen-pendaftaran',
            'local'
        );

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

        /*
        |--------------------------------------------------------------------------
        | Existing File
        |--------------------------------------------------------------------------
        */
        $existing = DokumenPendaftaran::where([
            'peserta_kkn_id' => $peserta->id,
            'jenis_dokumen'  => $request->jenis_dokumen,
        ])->first();

        if ($existing && $existing->file) {

            Storage::disk('local')->delete(
                $existing->file->path
            );

            $existing->file->delete();
        }

        /*
        |--------------------------------------------------------------------------
        | Save Dokumen
        |--------------------------------------------------------------------------
        */
        DokumenPendaftaran::updateOrCreate(
            [
                'peserta_kkn_id' => $peserta->id,
                'jenis_dokumen'  => $request->jenis_dokumen,
            ],
            [
                'file_id'           => $file->id,
                'status_verifikasi' => 'pending',
                'verified_by'       => null,
                'verified_at'       => null,
                'catatan_revisi'    => null,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Update Status Peserta
        |--------------------------------------------------------------------------
        */
        $this->updateStatusPeserta(
            $peserta
        );

        /*
        |--------------------------------------------------------------------------
        | Notification Admin
        |--------------------------------------------------------------------------
        */
        $admins = User::role('superadmin')->get();

        /*
        |--------------------------------------------------------------------------
        | Simpan Ke Notification Log
        |--------------------------------------------------------------------------
        */
        $log = NotificationLog::create([
            'title' => 'Dokumen Pendaftaran Baru',
            'message' => auth()->user()->name . ' mengupload dokumen ' . $request->jenis_dokumen,
            'type' => 'info',
            'recipients' => $admins->pluck('name')->toArray(),
            'action_url' => route('dokumen-pendaftaran.index'),
            'action_text' => 'Lihat Dokumen',
            'sent_by' => auth()->id(),
        ]);

        foreach ($admins as $admin) {

            $admin->notify(
                new DokumenUploadedNotification(
                    $peserta,
                    $log->id
                )
            );
        }

        return redirect()
            ->route('dokumen-pendaftaran.index')
            ->with(
                'success',
                'Dokumen berhasil diunggah.'
            );
    }

    public function destroy($id): RedirectResponse
    {
        $dokumen = DokumenPendaftaran::with([
                'file',
                'pesertaKkn',
            ])
            ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Delete File
        |--------------------------------------------------------------------------
        */
        if ($dokumen->file) {

            Storage::disk('local')->delete(
                $dokumen->file->path
            );

            $dokumen->file->delete();
        }

        $peserta = $dokumen->pesertaKkn;

        $dokumen->delete();

        /*
        |--------------------------------------------------------------------------
        | Update Status Peserta
        |--------------------------------------------------------------------------
        */
        $this->updateStatusPeserta(
            $peserta
        );

        return redirect()
            ->route('dokumen-pendaftaran.index')
            ->with(
                'success',
                'Dokumen berhasil dihapus.'
            );
    }

    private function updateStatusPeserta(
        PesertaKkn $peserta
    ): void {

        $uploadedTypes = $peserta->dokumenPendaftaran()
            ->pluck('jenis_dokumen')
            ->toArray();

        $missing = array_diff(
            $this->requiredDocuments,
            $uploadedTypes
        );

        /*
        |--------------------------------------------------------------------------
        | Belum Upload Sama Sekali
        |--------------------------------------------------------------------------
        */
        if (count($uploadedTypes) === 0) {

            $peserta->update([
                'status_pendaftaran' => 'draft',
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Dokumen Belum Lengkap
        |--------------------------------------------------------------------------
        */
        if (count($missing) > 0) {

            $peserta->update([
                'status_pendaftaran' => 'pending_documents',
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Lengkap Menunggu Verifikasi
        |--------------------------------------------------------------------------
        */
        $peserta->update([
            'status_pendaftaran' => 'pending_verification',
        ]);
    }
}
