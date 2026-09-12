<?php

namespace App\Http\Controllers;

use App\Models\KelompokProposal;
use App\Models\LogBook;
use App\Models\PenilaianIndividu;
use App\Models\PenilaianKelompok;
use App\Models\PenilaianKomponen;
use App\Models\PesertaKkn;
use App\Models\TugasKelompok;
use App\Services\StatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KelompokController extends Controller
{
    private function getPeserta()
    {
        $mahasiswa = auth()->user()->mahasiswa;
        if (! $mahasiswa) {
            return null;
        }

        return PesertaKkn::where('mahasiswa_id', $mahasiswa->user_id)
            ->whereNotNull('kelompok_kkn_id')
            ->whereDoesntHave('gelombang.warSessions', fn ($q) => $q->whereIn('status', ['scheduled', 'active']))
            ->with(['kelompokKkn.desaGelombang.desa.kecamatan', 'kelompokKkn.desaGelombang.gelombang'])
            ->first();
    }

    public function index(): View|RedirectResponse
    {
        $peserta = $this->getPeserta();

        if (! $peserta) {
            session()->flash('info', 'Anda belum tergabung dalam kelompok KKN. Silakan menunggu penempatan oleh admin atau ikuti proses WAR KKN.');

            return redirect()->route('home');
        }

        $kelompok = $peserta->kelompokKkn;
        $isKetua = $kelompok->ketua_peserta_id === $peserta->id;
        $isDpl = $kelompok->dosen_pembimbing_lapangan_id === auth()->user()->dosenPembimbingLapangan?->id;

        $kelompok->load([
            'pesertaKkn.mahasiswa.user',
            'pesertaKkn.mahasiswa.prodi.fakultas',
            'dosenPembimbingLapangan.user',
            'ketua.mahasiswa.user',
        ]);

        $proposal = KelompokProposal::where('kelompok_kkn_id', $kelompok->id)->first();

        $statusService = app(StatusService::class);
        $statusCurrent = $statusService->getCurrentStage($kelompok);
        $statusHistory = $statusService->getHistory($kelompok);
        $statusStages = StatusService::STAGES;
        $isAdmin = auth()->user()->hasRole('superadmin');

        $tugasList = TugasKelompok::where('kelompok_kkn_id', $kelompok->id)
            ->with(['submissions.pesertaKkn.mahasiswa.user'])->get()
            ->groupBy('kategori');

        $logbookData = LogBook::where('kelompok_kkn_id', $kelompok->id)
            ->with(['pesertaKkn.mahasiswa.user'])
            ->latest('tanggal')
            ->get()
            ->groupBy('peserta_kkn_id');

        $members = $kelompok->pesertaKkn->map(fn ($p) => ['id' => $p->id, 'name' => $p->mahasiswa->user->name]);

        $komponenList = PenilaianKomponen::orderBy('urutan')->get();
        $penilaianData = PenilaianKelompok::where('kelompok_kkn_id', $kelompok->id)
            ->with('komponen')->get()->keyBy('komponen_id');

        $penilaianIndividu = PenilaianIndividu::where('kelompok_kkn_id', $kelompok->id)->get();

        $dplKomponen = $komponenList->firstWhere('nama_komponen', 'Nilai DPL');
        $dplScores = $penilaianIndividu->where('komponen_id', $dplKomponen?->id)->pluck('nilai');
        $dplScore = $dplScores->isNotEmpty() ? round($dplScores->avg(), 2) : null;

        $desaKomponen = $komponenList->firstWhere('nama_komponen', 'Nilai Desa');
        $desaScores = $penilaianIndividu->where('komponen_id', $desaKomponen?->id)->pluck('nilai');
        $desaScore = $desaScores->isNotEmpty() ? round($desaScores->avg(), 2) : null;

        $lppmScore = $penilaianData->first(fn ($v) => $v->komponen->nama_komponen === 'Nilai LPPM')?->nilai;

        $finalScore = (! is_null($dplScore) && ! is_null($desaScore) && ! is_null($lppmScore))
            ? round(($dplScore * 0.40 + $desaScore * 0.30 + $lppmScore * 0.30), 2)
            : null;

        return view('kelompok.index', compact(
            'kelompok', 'peserta', 'isKetua', 'isDpl', 'proposal',
            'statusCurrent', 'statusHistory', 'statusStages', 'isAdmin', 'tugasList',
            'logbookData', 'members', 'desaScore', 'dplScore', 'lppmScore', 'finalScore'
        ));
    }

    public function uploadPhoto(Request $request): RedirectResponse
    {
        $peserta = $this->getPeserta();
        abort_if(! $peserta, 404);

        $kelompok = $peserta->kelompokKkn;
        abort_if($kelompok->ketua_peserta_id !== $peserta->id, 403, 'Hanya ketua kelompok yang dapat mengunggah foto.');

        $request->validate([
            'foto_kelompok' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($kelompok->foto_kelompok) {
            Storage::disk('public')->delete($kelompok->foto_kelompok);
        }

        $path = $request->file('foto_kelompok')->store('foto-kelompok', 'public');
        $kelompok->update(['foto_kelompok' => $path]);

        return back()->with('success', 'Foto kelompok berhasil diperbarui.');
    }

    private function calcScore($komponenList, $penilaianData): ?float
    {
        $totalBobot = $komponenList->sum('bobot');
        if ($totalBobot === 0) {
            return null;
        }
        $totalNilai = $komponenList->sum(function ($k) use ($penilaianData) {
            return ($penilaianData[$k->id]->nilai ?? 0) * $k->bobot;
        });

        return $totalNilai > 0 ? round($totalNilai / $totalBobot, 2) : null;
    }
}
