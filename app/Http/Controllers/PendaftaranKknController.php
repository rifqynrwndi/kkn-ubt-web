<?php

namespace App\Http\Controllers;

use App\Models\Gelombang;
use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Services\War\WarRuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PendaftaranKknController extends Controller
{
    public function __construct(
        private readonly WarRuleService $ruleService,
    ) {}
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
                    'ketua.mahasiswa.user',
                ])
                    ->find($pendaftaran->kelompok_kkn_id);
            }
        }

        return view('pendaftaran-kkn.index', [
            'gelombang' => $gelombangAktif,
            'pendaftaran' => $pendaftaran,
            'kelompok' => $kelompok,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Detail Kelompok Saya
    |--------------------------------------------------------------------------
    */
    public function kelompokSaya()
    {
        $user = auth()->user();

        $peserta = PesertaKkn::with([
            'kelompokKkn.desaGelombang.desa.kecamatan',
            'kelompokKkn.dosenPembimbingLapangan.user',
            'kelompokKkn.pesertaKkn.mahasiswa.user',
            'kelompokKkn.pesertaKkn.mahasiswa.prodi.fakultas',
        ])
            ->where('mahasiswa_id', $user->id)
            ->whereNotNull('kelompok_kkn_id')
            ->whereDoesntHave('gelombang.warSessions', fn ($q) => $q->whereIn('status', ['scheduled', 'active']))
            ->first();

        if (! $peserta) {
            session()->flash('info', 'Anda belum tergabung dalam kelompok KKN. Silakan menunggu penempatan oleh admin atau ikuti proses WAR KKN.');

            return redirect()->route('home');
        }

        return view('war.joined', [
            'session' => null,
            'peserta' => $peserta,
            'participant' => null,
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
        $requiredDocs = $pendaftaran->gelombang->getRequiredDocumentTypesAttribute();

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

        if ($documentUploadComplete && $documentVerified && ! $pendaftaran->kelompok_kkn_id) {

            $kelompoks = KelompokKkn::with([
                'desaGelombang.desa.kecamatan',
                'dosenPembimbingLapangan.user',
                'pesertaKkn.mahasiswa.prodi',
            ])
                ->whereHas('desaGelombang', function ($q) use ($pendaftaran) {
                    $q->where('gelombang_id', $pendaftaran->gelombang_id);
                })
                ->where('status', '!=', 'penuh')
                ->get()
                ->map(function ($k) use ($pendaftaran) {
                    $k->can_join = $this->ruleService->checkCanJoin($k, $pendaftaran, checkProdi: true);

                    return $k;
                });
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

        if (! $pendaftaran) {
            return back()->with('error', 'Anda belum terdaftar.');
        }

        if ($pendaftaran->kelompok_kkn_id) {
            return back()->with('error', 'Anda sudah memiliki kelompok.');
        }

        if ($pendaftaran->status_pendaftaran !== 'approved') {
            return back()->with('error', 'Pendaftaran belum disetujui.');
        }

        if ($pendaftaran->gelombang_id !== $kelompok->desaGelombang->gelombang_id) {
            return back()->with('error', 'Kelompok ini tidak termasuk gelombang Anda.');
        }

        if ($pendaftaran->gelombang->status !== 'pendaftaran') {
            return back()->with('error', 'Gelombang pendaftaran sudah ditutup.');
        }

        return DB::transaction(function () use ($kelompok, $pendaftaran) {
            $kelompok->pesertaKkn()->lockForUpdate()->get();

            $canJoin = $this->ruleService->checkCanJoin($kelompok, $pendaftaran, checkProdi: true);

            if (! $canJoin) {
                return back()->with('error', 'Anda tidak dapat bergabung ke kelompok ini.');
            }

            $pendaftaran->update([
                'kelompok_kkn_id' => $kelompok->id,
            ]);

            $kelompok->generateKetua();

            return redirect()
                ->route('pendaftaran-kkn.index')
                ->with('success', 'Berhasil masuk kelompok KKN.');
        });
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
        $alreadyInAny = PesertaKkn::where('mahasiswa_id', $user->id)->exists();
        if ($alreadyInAny) {
            $existing = PesertaKkn::where('mahasiswa_id', $user->id)->first();
            $msg = $existing->gelombang_id === $gelombangAktif->id
                ? 'Anda sudah terdaftar pada gelombang ini.'
                : 'Anda sudah terdaftar di gelombang "'.($existing->gelombang->nama_gelombang ?? 'lain').'". Tidak dapat mendaftar di gelombang ini.';

            return back()->with('error', $msg);
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Pendaftaran
        |--------------------------------------------------------------------------
        */
        $statusPendaftaran = $gelombangAktif->skip_dokumen ? 'approved' : 'draft';
        $verifiedAt = $gelombangAktif->skip_dokumen ? now() : null;

        PesertaKkn::create([
            'mahasiswa_id' => $user->id,
            'gelombang_id' => $gelombangAktif->id,
            'status_pendaftaran' => $statusPendaftaran,
            'submitted_at' => now(),
            'verified_at' => $verifiedAt,
        ]);

        $msg = $gelombangAktif->skip_dokumen
            ? 'Berhasil mendaftar KKN. Anda telah otomatis disetujui untuk gelombang ini.'
            : 'Berhasil mendaftar KKN. Silakan lengkapi dokumen pendaftaran.';

        return redirect()
            ->route('pendaftaran-kkn.index')
            ->with('success', $msg);
    }
}
