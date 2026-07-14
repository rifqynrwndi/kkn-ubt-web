<?php
namespace App\Http\Controllers;

use App\Models\KelompokKkn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class KelompokController extends Controller
{
    private function getPeserta()
    {
        $mahasiswa = auth()->user()->mahasiswa;
        if (! $mahasiswa) return null;

        return \App\Models\PesertaKkn::where('mahasiswa_id', $mahasiswa->user_id)
            ->whereNotNull('kelompok_kkn_id')
            ->whereDoesntHave('gelombang.warSessions', fn($q) => $q->whereIn('status', ['scheduled', 'active']))
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

        $proposal = \App\Models\KelompokProposal::where('kelompok_kkn_id', $kelompok->id)->first();

        $statusService = app(\App\Services\StatusService::class);
        $statusCurrent = $statusService->getCurrentStage($kelompok);
        $statusHistory = $statusService->getHistory($kelompok);
        $statusStages  = \App\Services\StatusService::STAGES;
        $isAdmin = auth()->user()->hasRole('superadmin');

        $tugasList = \App\Models\TugasKelompok::where('kelompok_kkn_id', $kelompok->id)
            ->with(['submissions.pesertaKkn.mahasiswa.user'])->get()
            ->groupBy('kategori');

        $logbookData = \App\Models\LogBook::where('kelompok_kkn_id', $kelompok->id)
            ->with(['pesertaKkn.mahasiswa.user'])
            ->latest('tanggal')
            ->get()
            ->groupBy('peserta_kkn_id');

        $members = $kelompok->pesertaKkn->map(fn($p) => ['id'=>$p->id,'name'=>$p->mahasiswa->user->name]);

        $komponenList = \App\Models\PenilaianKomponen::orderBy('urutan')->get();
        $penilaianData = \App\Models\PenilaianKelompok::where('kelompok_kkn_id', $kelompok->id)
            ->with('komponen')->get()->keyBy('komponen_id');

        $desaScore = $penilaianData->first(fn($v) => $v->komponen->nama_komponen === 'Nilai Pelaksanaan KKN UBT')?->nilai;
        $dplScore = $penilaianData->first(fn($v) => $v->komponen->nama_komponen === 'Logbook')?->nilai;
        $lppmScore = $this->calcScore($komponenList->where('kategori','lppm'), $penilaianData);
        $finalScore = $desaScore && $dplScore && $lppmScore
            ? round(($desaScore * 0.30 + $dplScore * 0.50 + $lppmScore * 0.20), 2)
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
        if ($totalBobot === 0) return null;
        $totalNilai = $komponenList->sum(function ($k) use ($penilaianData) {
            return ($penilaianData[$k->id]->nilai ?? 0) * $k->bobot;
        });
        return $totalNilai > 0 ? round($totalNilai / $totalBobot, 2) : null;
    }
}
