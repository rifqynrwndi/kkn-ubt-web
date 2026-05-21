<?php

namespace App\Http\Controllers;

use App\Models\KelompokKkn;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DplController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:pembimbing']);
    }

    private function getDpl()
    {
        return auth()->user()->dosenPembimbingLapangan;
    }

    /*
    |--------------------------------------------------------------------------
    | LIST KELOMPOK BINAAN
    |--------------------------------------------------------------------------
    */
    public function kelompokIndex(): View
    {
        $dpl = $this->getDpl();

        if (! $dpl) {
            abort(403, 'Anda tidak terdaftar sebagai DPL.');
        }

        $kelompoks = KelompokKkn::with([
                'desaGelombang.desa.kecamatan',
                'desaGelombang.gelombang',
                'pesertaKkn',
            ])
            ->where('dosen_pembimbing_lapangan_id', $dpl->id)
            ->withCount('pesertaKkn')
            ->orderBy('nama_kelompok')
            ->get();

        return view('dpl.kelompok-index', compact('dpl', 'kelompoks'));
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL KELOMPOK + ANGGOTA
    |--------------------------------------------------------------------------
    */
    public function kelompokShow(KelompokKkn $kelompok): View
    {
        $dpl = $this->getDpl();

        abort_if($kelompok->dosen_pembimbing_lapangan_id !== optional($dpl)->id, 403);

        $kelompok->load([
            'desaGelombang.desa.kecamatan',
            'desaGelombang.gelombang',
            'pesertaKkn.mahasiswa.user',
            'pesertaKkn.mahasiswa.prodi.fakultas',
            'ketua.mahasiswa.user',
        ]);

        return view('dpl.kelompok-show', compact('kelompok'));
    }

    /*
    |--------------------------------------------------------------------------
    | BIODATA MAHASISWA
    |--------------------------------------------------------------------------
    */
    public function mahasiswaShow($pesertaId): View
    {
        $dpl = $this->getDpl();

        $peserta = \App\Models\PesertaKkn::with([
                'mahasiswa.user',
                'mahasiswa.prodi.fakultas',
                'kelompokKkn',
                'dokumenPendaftaran',
            ])
            ->findOrFail($pesertaId);

        // Pastikan mahasiswa ini di kelompok binaan DPL
        abort_if(
            $peserta->kelompokKkn->dosen_pembimbing_lapangan_id !== optional($dpl)->id,
            403
        );

        return view('dpl.mahasiswa-show', compact('peserta'));
    }
}
