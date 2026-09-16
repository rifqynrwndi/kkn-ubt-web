<?php

namespace App\Http\Controllers;

use App\Models\DesaGelombang;
use App\Models\DosenPembimbingLapangan;
use App\Models\Kecamatan;
use App\Models\KelompokKkn;
use App\Models\KelompokProposal;
use App\Models\LaporanDpl;
use App\Models\LogBook;
use App\Models\PenilaianIndividu;
use App\Models\PenilaianKelompok;
use App\Models\PenilaianKomponen;
use App\Models\PesertaKkn;
use App\Models\TugasKelompok;
use App\Services\ExportService;
use App\Services\KelompokService;
use App\Services\ScoreService;
use App\Services\StatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KelompokKknController extends Controller
{
    public function index(Request $request): View
    {
        $query = KelompokKkn::with([
            'desaGelombang.desa.kecamatan',
            'desaGelombang.gelombang',
            'dosenPembimbingLapangan.user',
            'pesertaKkn',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $query->where(function ($q) use ($request) {
                $q->where('kode_kelompok', 'like', '%'.$request->search.'%')
                    ->orWhere('nama_kelompok', 'like', '%'.$request->search.'%')
                    ->orWhereHas('pesertaKkn.mahasiswa.user', fn ($uq) => $uq->where('name', 'like', '%'.$request->search.'%'))
                    ->orWhereHas('pesertaKkn.mahasiswa', fn ($mq) => $mq->where('npm', 'like', '%'.$request->search.'%'));
            });

        }

        /*
        |--------------------------------------------------------------------------
        | Filter Kabupaten
        |--------------------------------------------------------------------------
        */

        if ($request->filled('kabupaten')) {
            $query->whereHas('desaGelombang.desa.kecamatan', function ($q) use ($request) {
                $q->where('kabupaten', $request->kabupaten);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Kecamatan
        |--------------------------------------------------------------------------
        */

        if ($request->filled('kecamatan_id')) {
            $query->whereHas('desaGelombang.desa.kecamatan', function ($q) use ($request) {
                $q->where('id', $request->kecamatan_id);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );

        }

        $kabupatens = Kecamatan::select('kabupaten')->distinct()->orderBy('kabupaten')->pluck('kabupaten');
        $kecamatans = collect();
        $selectedKabupaten = $request->get('kabupaten');

        if ($selectedKabupaten) {
            $kecamatans = Kecamatan::where('kabupaten', $selectedKabupaten)
                ->orderBy('nama_kecamatan')
                ->get();
        }

        $kelompok = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'kelompok-kkn.index',
            compact('kelompok', 'kabupatens', 'kecamatans', 'selectedKabupaten')
        );
    }

    public function create(): View
    {
        $desaGelombang = DesaGelombang::with([
            'desa',
            'gelombang',
        ])->get();

        $dpl = DosenPembimbingLapangan::with('user')
            ->where('status', 'aktif')
            ->get();

        return view(
            'kelompok-kkn.create',
            compact(
                'desaGelombang',
                'dpl'
            )
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([

            'desa_gelombang_id' => 'required|exists:desa_gelombang,id',

            'dosen_pembimbing_lapangan_id' => 'nullable|exists:dosen_pembimbing_lapangan,id',

            'kuota' => 'required|integer|min:1|max:20',

            'status' => 'required|in:draft,dibuka,ditutup',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate Nama Kelompok
        |--------------------------------------------------------------------------
        */

        $desaGelombang = DesaGelombang::with([
            'desa',
            'gelombang',
        ])->findOrFail(
            $validated['desa_gelombang_id']
        );

        $validated['nama_kelompok'] =
            $desaGelombang->desa->nama_desa
            .' - '.
            $desaGelombang->gelombang->nama_gelombang;

        $kelompok = KelompokKkn::create($validated);

        app(KelompokService::class)->seedTugasTemplates($kelompok);

        return redirect()
            ->route('kelompok-kkn.index')
            ->with(
                'success',
                'Kelompok KKN berhasil dibuat.'
            );
    }

    public function show(
        KelompokKkn $kelompok_kkn
    ): View {

        $kelompok_kkn->load([
            'desaGelombang.desa',
            'desaGelombang.gelombang',
            'dosenPembimbingLapangan.user',
            'pesertaKkn.mahasiswa.user',
            'pesertaKkn.mahasiswa.prodi.fakultas',
            'desaGelombang.desa.kecamatan',
        ]);

        $proposal = KelompokProposal::where('kelompok_kkn_id', $kelompok_kkn->id)->first();
        $statusService = app(StatusService::class);
        $statusStages = StatusService::STAGES;
        $statusCurrent = $statusService->getCurrentStage($kelompok_kkn);
        $statusHistory = $statusService->getHistory($kelompok_kkn);
        $tugasList = TugasKelompok::where('kelompok_kkn_id', $kelompok_kkn->id)->with(['submissions.pesertaKkn.mahasiswa.user'])->get()->groupBy('kategori');
        $logbookData = LogBook::where('kelompok_kkn_id', $kelompok_kkn->id)->with(['pesertaKkn.mahasiswa.user'])->latest('tanggal')->get()->groupBy('peserta_kkn_id');
        $komponenList = PenilaianKomponen::orderBy('urutan')->get();
        $penilaianData = PenilaianKelompok::where('kelompok_kkn_id', $kelompok_kkn->id)->with('komponen')->get()->keyBy('komponen_id');

        $penilaianIndividu = PenilaianIndividu::where('kelompok_kkn_id', $kelompok_kkn->id)->get();

        $scores = app(ScoreService::class)->getScoreBreakdown($penilaianIndividu, $penilaianData, $komponenList);
        $dplScore = $scores['dpl'];
        $desaScore = $scores['desa'];
        $lppmScore = $scores['lppm'];
        $finalScore = $scores['total'];
        $laporans = LaporanDpl::where('kelompok_kkn_id', $kelompok_kkn->id)->latest()->get()->groupBy('jenis');

        return view(
            'kelompok-kkn.show',
            compact('kelompok_kkn', 'proposal', 'statusStages', 'statusCurrent', 'statusHistory', 'tugasList', 'logbookData', 'komponenList', 'penilaianData', 'penilaianIndividu', 'desaScore', 'dplScore', 'lppmScore', 'finalScore', 'laporans')
        );
    }

    public function edit(
        KelompokKkn $kelompok_kkn
    ): View {

        $desaGelombang = DesaGelombang::with([
            'desa',
            'gelombang',
        ])->get();

        $dpl = DosenPembimbingLapangan::with('user')
            ->where('status', 'aktif')
            ->get();

        return view(
            'kelompok-kkn.edit',
            compact(
                'kelompok_kkn',
                'desaGelombang',
                'dpl'
            )
        );
    }

    public function update(
        Request $request,
        KelompokKkn $kelompok_kkn
    ): RedirectResponse {

        $validated = $request->validate([

            'desa_gelombang_id' => 'required|exists:desa_gelombang,id',

            'dosen_pembimbing_lapangan_id' => 'nullable|exists:dosen_pembimbing_lapangan,id',

            'kuota' => 'required|integer|min:1|max:20',

            'status' => 'required|in:draft,dibuka,ditutup,penuh',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Nama Kelompok
        |--------------------------------------------------------------------------
        */

        $desaGelombang = DesaGelombang::with([
            'desa',
            'gelombang',
        ])->findOrFail(
            $validated['desa_gelombang_id']
        );

        $validated['nama_kelompok'] =
            $desaGelombang->desa->nama_desa
            .' - '.
            $desaGelombang->gelombang->nama_gelombang;

        /*
        |--------------------------------------------------------------------------
        | Auto Full Check
        |--------------------------------------------------------------------------
        */

        if (
            $kelompok_kkn->pesertaKkn()->count()
            >=
            $validated['kuota']
        ) {

            $validated['status'] = 'penuh';

        }

        $kelompok_kkn->update($validated);

        if (! empty($validated['dosen_pembimbing_lapangan_id']) && $kelompok_kkn->status_tahap === 2) {
            app(StatusService::class)->onDplAssigned($kelompok_kkn);
        }

        return redirect()
            ->route('kelompok-kkn.index')
            ->with(
                'success',
                'Kelompok KKN berhasil diperbarui.'
            );
    }

    public function destroy(
        KelompokKkn $kelompok_kkn
    ): RedirectResponse {

        if ($kelompok_kkn->pesertaKkn()->exists()) {

            return back()->with(
                'error',
                'Kelompok tidak dapat dihapus karena sudah memiliki anggota.'
            );

        }

        $kelompok_kkn->delete();

        return redirect()
            ->route('kelompok-kkn.index')
            ->with(
                'success',
                'Kelompok KKN berhasil dihapus.'
            );
    }

    public function buka(
        KelompokKkn $kelompok_kkn
    ): RedirectResponse {

        if ($kelompok_kkn->is_full) {

            return back()->with(
                'error',
                'Kelompok sudah penuh.'
            );

        }

        $kelompok_kkn->update([
            'status' => 'dibuka',
        ]);

        return back()->with(
            'success',
            'Kelompok berhasil dibuka.'
        );
    }

    public function tutup(
        KelompokKkn $kelompok_kkn
    ): RedirectResponse {

        $kelompok_kkn->update([
            'status' => 'ditutup',
        ]);

        return back()->with(
            'success',
            'Kelompok berhasil ditutup.'
        );
    }

    public function createAnggota(KelompokKkn $kelompok_kkn): View
    {
        $gelombangId = $kelompok_kkn->desaGelombang->gelombang_id;

        $peserta = PesertaKkn::with('mahasiswa.user', 'mahasiswa.prodi.fakultas')
            ->where('gelombang_id', $gelombangId)
            ->whereNull('kelompok_kkn_id')
            ->when(request('search'), fn ($q) => $q->whereHas('mahasiswa.user', fn ($q) => $q->where('name', 'like', '%'.request('search').'%')
            ))
            ->paginate(20)
            ->withQueryString();

        return view(
            'kelompok-kkn.tambah-anggota',
            compact(
                'kelompok_kkn',
                'peserta'
            )
        );
    }

    public function tambahAnggota(Request $request, KelompokKkn $kelompok_kkn): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'peserta_kkn_id' => [
                'required',
                'exists:peserta_kkn,id',
            ],
        ]);

        $peserta = PesertaKkn::findOrFail(
            $validated['peserta_kkn_id']
        );

        if ($peserta->kelompok_kkn_id) {
            return back()->with(
                'error',
                'Mahasiswa sudah memiliki kelompok.'
            );
        }

        $peserta->kelompok_kkn_id = $kelompok_kkn->id;
        $peserta->status_pendaftaran = 'approved';
        $peserta->save();

        if ($kelompok_kkn->fresh()->pesertaKkn()->count() >= $kelompok_kkn->kuota) {
            $kelompok_kkn->update(['status' => 'penuh']);
        }

        return redirect()
            ->route('kelompok-kkn.show', $kelompok_kkn)
            ->with(
                'success',
                'Anggota berhasil ditambahkan.'
            );
    }

    public function hapusAnggota(KelompokKkn $kelompok_kkn, PesertaKkn $peserta): RedirectResponse
    {
        if ($peserta->kelompok_kkn_id !== $kelompok_kkn->id) {
            return back()->with('error', 'Anggota tidak ditemukan pada kelompok ini.');
        }

        app(KelompokService::class)->removeAnggota($kelompok_kkn, $peserta);

        return back()->with('success', 'Anggota berhasil dihapus.');
    }

    public function setKetua(KelompokKkn $kelompok_kkn, PesertaKkn $peserta): RedirectResponse
    {
        abort_if(
            $peserta->kelompok_kkn_id !== $kelompok_kkn->id,
            403,
            'Peserta ini bukan anggota kelompok ini.'
        );

        $kelompok_kkn->update([
            'ketua_peserta_id' => $peserta->id,
        ]);

        return back()->with(
            'success',
            'Ketua kelompok berhasil diubah menjadi '.($peserta->mahasiswa?->user?->name ?? 'Unknown').'.'
        );
    }

    public function exportXlsx()
    {
        app(ExportService::class)->exportKelompokXlsx();
    }

    public function laporanStore(Request $request, KelompokKkn $kelompok_kkn): RedirectResponse
    {
        $isKetua = $kelompok_kkn->ketua_peserta_id && auth()->user()->pesertaKkn?->where('kelompok_kkn_id', $kelompok_kkn->id)->first()?->id === $kelompok_kkn->ketua_peserta_id;
        abort_unless($isKetua || auth()->user()->hasRole('superadmin'), 403, 'Hanya ketua kelompok atau admin yang dapat mengunggah laporan.');

        $request->validate([
            'jenis' => 'required|in:monev,artikel,haki',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10240',
        ]);

        $data = [
            'kelompok_kkn_id' => $kelompok_kkn->id,
            'dpl_id' => $kelompok_kkn->dosen_pembimbing_lapangan_id,
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

    public function laporanDestroy(KelompokKkn $kelompok_kkn, LaporanDpl $laporan): RedirectResponse
    {
        $isKetua = $kelompok_kkn->ketua_peserta_id && auth()->user()->pesertaKkn?->where('kelompok_kkn_id', $kelompok_kkn->id)->first()?->id === $kelompok_kkn->ketua_peserta_id;
        abort_unless($isKetua || auth()->user()->hasRole('superadmin'), 403, 'Hanya ketua kelompok atau admin yang dapat menghapus laporan.');

        if ($laporan->file_path) {
            Storage::disk('public')->delete($laporan->file_path);
        }
        $laporan->delete();

        return back()->with('success', 'Laporan dihapus.');
    }
}
