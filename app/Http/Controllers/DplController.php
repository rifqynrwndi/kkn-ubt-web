<?php

namespace App\Http\Controllers;

use App\Models\KelompokKkn;
use App\Models\LaporanDpl;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            ->withCount(['tugasKelompok as total_tugas'])
            ->withCount(['tugasKelompok as submitted_tugas' => fn($q) => $q->whereHas('submissions')])
            ->orderBy('nama_kelompok')
            ->get();

        $wajibTasks = \App\Models\TugasKelompok::where('is_wajib', true)
            ->whereHas('kelompokKkn', fn($q) => $q->where('dosen_pembimbing_lapangan_id', $dpl->id))
            ->get();

        $wn = ['Program Kerja','Video Profil Desa','Draft Artikel','Laporan Program KKN'];
        $semuaTasks = \App\Models\TugasKelompok::whereIn('nama_tugas', $wn)
            ->whereHas('kelompokKkn', fn($q) => $q->where('dosen_pembimbing_lapangan_id', $dpl->id))
            ->get();

        $kelompoks->each(function ($k) use ($wn) {
            $k->load(['tugasKelompok' => fn($q) => $q->whereIn('nama_tugas', $wn)->with('submissions')]);
        });

        return view('dpl.kelompok-index', compact('dpl', 'kelompoks', 'semuaTasks'));
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

        $proposal = \App\Models\KelompokProposal::where('kelompok_kkn_id', $kelompok->id)->first();
        $statusService = app(\App\Services\StatusService::class);
        $statusStages = \App\Services\StatusService::STAGES;
        $statusCurrent = $statusService->getCurrentStage($kelompok);
        $statusHistory = $statusService->getHistory($kelompok);
        $tugasList = \App\Models\TugasKelompok::where('kelompok_kkn_id', $kelompok->id)->with(['submissions.pesertaKkn.mahasiswa.user'])->get()->groupBy('kategori');
        $logbookData = \App\Models\LogBook::where('kelompok_kkn_id', $kelompok->id)->with(['pesertaKkn.mahasiswa.user'])->latest('tanggal')->get()->groupBy('peserta_kkn_id');
        $komponenList = \App\Models\PenilaianKomponen::orderBy('urutan')->get();
        $penilaianData = \App\Models\PenilaianKelompok::where('kelompok_kkn_id', $kelompok->id)->with('komponen')->get()->keyBy('komponen_id');
        $penilaianIndividu = \App\Models\PenilaianIndividu::where('kelompok_kkn_id', $kelompok->id)->get()->groupBy('peserta_kkn_id')->map(fn($g) => $g->keyBy('komponen_id'));
        $laporans = \App\Models\LaporanDpl::where('kelompok_kkn_id', $kelompok->id)->latest()->get()->groupBy('jenis');

        return view('dpl.kelompok-show', compact('kelompok', 'proposal', 'statusStages', 'statusCurrent', 'statusHistory', 'tugasList', 'logbookData', 'komponenList', 'penilaianData', 'penilaianIndividu', 'laporans'));
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
            ! $peserta->kelompokKkn
            || $peserta->kelompokKkn->dosen_pembimbing_lapangan_id !== optional($dpl)->id,
            403
        );

        return view('dpl.mahasiswa-show', compact('peserta'));
    }

    public function profileEdit(): View
    {
        $dpl = $this->getDpl();
        abort_if(!$dpl, 403);
        return view('dpl.profile-edit', compact('dpl'));
    }

    public function profileUpdate(Request $request): RedirectResponse
    {
        $dpl = $this->getDpl();
        abort_if(!$dpl, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $dpl->user->id,
            'no_hp' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:laki_laki,perempuan',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $dpl->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $dplData = [
            'no_hp' => $request->no_hp,
            'jenis_kelamin' => $request->jenis_kelamin,
        ];

        if ($request->hasFile('foto')) {
            if ($dpl->foto) {
                Storage::disk('public')->delete($dpl->foto);
            }
            $dplData['foto'] = $request->file('foto')->store('foto-dpl', 'public');
        }

        $dpl->update($dplData);

        return redirect()->route('dpl.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function laporanStore(Request $request, KelompokKkn $kelompok): RedirectResponse
    {
        abort_if($kelompok->dosen_pembimbing_lapangan_id !== $this->getDpl()?->id, 403);

        $request->validate([
            'jenis' => 'required|in:monev,artikel,haki',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10240',
        ]);

        $data = [
            'kelompok_kkn_id' => $kelompok->id,
            'dpl_id' => $this->getDpl()->id,
            'jenis' => $request->jenis,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
        ];

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('laporan-dpl', 'public');
            $data['file_name'] = $request->file('file')->getClientOriginalName();
        }

        LaporanDpl::create($data);

        return back()->with('success', 'Laporan berhasil diupload.');
    }

    public function laporanDestroy(LaporanDpl $laporan): RedirectResponse
    {
        abort_if($laporan->dpl_id !== $this->getDpl()?->id, 403);
        if ($laporan->file_path) Storage::disk('public')->delete($laporan->file_path);
        $laporan->delete();
        return back()->with('success', 'Laporan dihapus.');
    }
}
