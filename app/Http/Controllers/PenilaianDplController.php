<?php
namespace App\Http\Controllers;

use App\Models\KelompokKkn;
use App\Models\PenilaianKelompok;
use App\Models\PenilaianIndividu;
use App\Models\PenilaianKomponen;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenilaianDplController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:pembimbing']);
    }

    private function getDpl()
    {
        return auth()->user()->dosenPembimbingLapangan;
    }

    public function index(): View
    {
        $dpl = $this->getDpl();
        abort_if(!$dpl, 403);

        $kelompoks = KelompokKkn::with([
            'desaGelombang.desa.kecamatan',
            'pesertaKkn.mahasiswa.user',
        ])->where('dosen_pembimbing_lapangan_id', $dpl->id)
            ->orderBy('nama_kelompok')
            ->get();

        $komponenList = PenilaianKomponen::where('kategori', 'dpl')->orderBy('urutan')->get();

        $kelompoks->each(function ($k) use ($komponenList) {
            $penilaianIndividu = PenilaianIndividu::where('kelompok_kkn_id', $k->id)->get()->groupBy('peserta_kkn_id')->map(fn($g) => $g->keyBy('komponen_id'));
            $k->penilaianIndividu = $penilaianIndividu;
            $k->komponenList = $komponenList;

            $logbookKomponen = $komponenList->firstWhere('nama_komponen', 'Logbook');
            $desaKomponen = $komponenList->firstWhere('nama_komponen', 'Nilai Pelaksanaan KKN UBT');

            $logbookScores = $penilaianIndividu->flatten()->where('komponen_id', $logbookKomponen?->id)->pluck('nilai');
            $desaScores = $penilaianIndividu->flatten()->where('komponen_id', $desaKomponen?->id)->pluck('nilai');

            $k->dplScore = $logbookScores->isNotEmpty() ? round($logbookScores->avg(), 2) : null;
            $k->desaScore = $desaScores->isNotEmpty() ? round($desaScores->avg(), 2) : null;
        });

        return view('penilaian-dpl.index', compact('kelompoks', 'komponenList'));
    }

    public function show(KelompokKkn $kelompok): View
    {
        $dpl = $this->getDpl();
        abort_if($kelompok->dosen_pembimbing_lapangan_id !== optional($dpl)->id, 403);

        $kelompok->load([
            'pesertaKkn.mahasiswa.user',
            'desaGelombang.desa.kecamatan',
        ]);

        $komponenList = PenilaianKomponen::where('kategori', 'dpl')->orderBy('urutan')->get();
        $penilaianIndividu = PenilaianIndividu::where('kelompok_kkn_id', $kelompok->id)
            ->get()->groupBy('peserta_kkn_id')->map(fn($g) => $g->keyBy('komponen_id'));

        return view('penilaian-dpl.show', compact('kelompok', 'komponenList', 'penilaianIndividu'));
    }
}
