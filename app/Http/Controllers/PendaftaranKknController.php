<?php

namespace App\Http\Controllers;

use App\Models\Gelombang;
use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PendaftaranKknController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Gelombang Aktif
        |--------------------------------------------------------------------------
        */
        $gelombangAktif = Gelombang::whereIn('status', [
                'pendaftaran',
                'berjalan',
                'selesai',
            ])
            ->latest()
            ->first();

        $pendaftaran = null;
        $kelompok = null;

        if ($gelombangAktif) {

            /*
            |--------------------------------------------------------------------------
            | Data Pendaftaran Mahasiswa
            |--------------------------------------------------------------------------
            */
            $pendaftaran = PesertaKkn::with([
                    'gelombang',
                    'kelompokKkn',
                ])
                ->where('mahasiswa_id', $user->id)
                ->where('gelombang_id', $gelombangAktif->id)
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Kelompok Mahasiswa
            |--------------------------------------------------------------------------
            */
            if ($pendaftaran?->kelompok_kkn_id) {

                $kelompok = KelompokKkn::with([
                        'desaGelombang.desa.kecamatan',
                        'desaGelombang.gelombang',
                        'dosenPembimbingLapangan.user',
                        'pesertaKkn.mahasiswa.user',
                        'ketua',
                    ])
                    ->find($pendaftaran->kelompok_kkn_id);
            }
        }

        return view('pendaftaran-kkn.index', [
            'gelombang'   => $gelombangAktif,
            'pendaftaran' => $pendaftaran,
            'kelompok'    => $kelompok,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Halaman Pilih Gelombang
    |--------------------------------------------------------------------------
    */
    public function gelombang(): View
    {
        $gelombangs = Gelombang::where('status', 'pendaftaran')
            ->latest()
            ->get();

        return view('pendaftaran-kkn.gelombang', [
            'gelombangs' => $gelombangs,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Halaman Plotting Kelompok
    |--------------------------------------------------------------------------
    */
    public function plotting(): View
    {
        $user = auth()->user();

        $pendaftaran = PesertaKkn::with([
                'mahasiswa.prodi.fakultas',
                'gelombang',
                'dokumenPendaftaran',
            ])
            ->where('mahasiswa_id', $user->id)
            ->latest()
            ->first();

        abort_if(! $pendaftaran, 403);

        /*
        |-----------------------------------------
        | CEK DOKUMEN WAJIB UPLOAD
        |-----------------------------------------
        */
        $requiredDocs = [
            'dhs',
            'surat_pernyataan',
            'surat_ortu',
            'surat_vaksin',
            'surat_dokter',
        ];

        $uploadedDocs = $pendaftaran->dokumenPendaftaran
            ->pluck('jenis_dokumen')
            ->toArray();

        $isUploadComplete =
            count(array_diff($requiredDocs, $uploadedDocs)) === 0;

        /*
        |-----------------------------------------
        | CEK STATUS VERIFIKASI
        |-----------------------------------------
        */
        $isVerifiedComplete = $pendaftaran->dokumenPendaftaran
            ->whereIn('jenis_dokumen', $requiredDocs)
            ->every(fn ($doc) => $doc->status_verifikasi === 'verified');

        /*
        |-----------------------------------------
        | STATE FLAG (Bypass jika skip_dokumen)
        |-----------------------------------------
        */
        $skipDokumen = $pendaftaran->gelombang->skip_dokumen ?? false;
        
        $documentUploadComplete = $skipDokumen ? true : $isUploadComplete;
        $documentVerified = $skipDokumen ? true : $isVerifiedComplete;

        /*
        |-----------------------------------------
        | DEFAULT KELOMPOK
        |-----------------------------------------
        */
        $kelompoks = collect();

        if ($documentUploadComplete && $documentVerified && !$pendaftaran->kelompok_kkn_id) {

            $kelompoks = KelompokKkn::with([
                    'desaGelombang.desa.kecamatan',
                    'dosenPembimbingLapangan.user',
                    'pesertaKkn.mahasiswa.prodi',
                ])
                ->whereHas('desaGelombang', function ($q) use ($pendaftaran) {
                    $q->where('gelombang_id', $pendaftaran->gelombang_id);
                })
                ->where('status', '!=', 'penuh')
                ->get();
        }

        return view('pendaftaran-kkn.plotting', compact(
            'pendaftaran',
            'kelompoks',
            'documentUploadComplete',
            'documentVerified'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil Kelompok
    |--------------------------------------------------------------------------
    */
    public function ambilKelompok(
        KelompokKkn $kelompok
    ): RedirectResponse {

        $user = auth()->user();

        $pendaftaran = PesertaKkn::with([
                'mahasiswa.prodi.fakultas',
            ])
            ->where('mahasiswa_id', $user->id)
            ->latest()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Validasi Pendaftaran
        |--------------------------------------------------------------------------
        */
        if (! $pendaftaran) {

            return back()->with(
                'error',
                'Anda belum terdaftar.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sudah Memiliki Kelompok
        |--------------------------------------------------------------------------
        */
        if ($pendaftaran->kelompok_kkn_id) {

            return back()->with(
                'error',
                'Anda sudah memiliki kelompok.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Kelompok Penuh
        |--------------------------------------------------------------------------
        */
        if ($kelompok->is_full) {

            return back()->with(
                'error',
                'Kelompok sudah penuh.'
            );
        }

        $mahasiswa = $pendaftaran->mahasiswa;

        $jenisKelamin = $mahasiswa->jenis_kelamin;
        $fakultasId   = $mahasiswa->prodi?->fakultas_id;

        /*
        |--------------------------------------------------------------------------
        | Validasi Kuota Fakultas
        |--------------------------------------------------------------------------
        */
        $kuota = $kelompok->kuotaFakultas()
            ->where('fakultas_id', $fakultasId)
            ->first();

        if (! $kuota) {

            return back()->with(
                'error',
                'Kuota fakultas tidak tersedia.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Kuota Gender
        |--------------------------------------------------------------------------
        */
        if (
            $jenisKelamin === 'L' &&
            $kuota->sisa_laki <= 0
        ) {

            return back()->with(
                'error',
                'Kuota laki-laki sudah penuh.'
            );
        }

        if (
            $jenisKelamin === 'P' &&
            $kuota->sisa_perempuan <= 0
        ) {

            return back()->with(
                'error',
                'Kuota perempuan sudah penuh.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Kelompok
        |--------------------------------------------------------------------------
        */
        $pendaftaran->update([
            'kelompok_kkn_id' => $kelompok->id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Auto Set Ketua Jika Belum Ada
        |--------------------------------------------------------------------------
        */
        if (! $kelompok->ketua_id) {

            $kelompok->update([
                'ketua_id' => $pendaftaran->mahasiswa_id,
            ]);
        }

        return redirect()
            ->route('pendaftaran-kkn.index')
            ->with(
                'success',
                'Berhasil masuk kelompok KKN.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Daftar Gelombang KKN
    |--------------------------------------------------------------------------
    */
    public function store(): RedirectResponse
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Validasi Email
        |--------------------------------------------------------------------------
        */
        if (! $user->hasVerifiedEmail()) {

            return back()->with(
                'error',
                'Verifikasi email terlebih dahulu.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Biodata
        |--------------------------------------------------------------------------
        */
        if (! $user->hasCompletedBiodata()) {

            return back()->with(
                'error',
                'Lengkapi biodata terlebih dahulu.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil Gelombang Aktif
        |--------------------------------------------------------------------------
        */
        $gelombangAktif = Gelombang::where(
                'status',
                'pendaftaran'
            )
            ->latest()
            ->first();

        if (! $gelombangAktif) {

            return back()->with(
                'error',
                'Tidak ada gelombang pendaftaran aktif.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Cek Sudah Daftar
        |--------------------------------------------------------------------------
        */
        $alreadyRegistered = PesertaKkn::where(
                'mahasiswa_id',
                $user->id
            )
            ->where(
                'gelombang_id',
                $gelombangAktif->id
            )
            ->exists();

        if ($alreadyRegistered) {

            return back()->with(
                'error',
                'Anda sudah terdaftar pada gelombang ini.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Pendaftaran
        |--------------------------------------------------------------------------
        */
        $statusPendaftaran = $gelombangAktif->skip_dokumen ? 'approved' : 'draft';
        $verifiedAt = $gelombangAktif->skip_dokumen ? now() : null;

        PesertaKkn::create([
            'mahasiswa_id'       => $user->id,
            'gelombang_id'       => $gelombangAktif->id,
            'status_pendaftaran' => $statusPendaftaran,
            'submitted_at'       => now(),
            'verified_at'        => $verifiedAt,
        ]);

        $msg = $gelombangAktif->skip_dokumen 
            ? 'Berhasil mendaftar KKN. Anda telah otomatis disetujui untuk gelombang ini.'
            : 'Berhasil mendaftar KKN. Silakan lengkapi dokumen pendaftaran.';

        return redirect()
            ->route('pendaftaran-kkn.index')
            ->with('success', $msg);
    }
}
