<?php

namespace App\Http\Controllers;

use App\Models\DesaGelombang;
use App\Models\DosenPembimbingLapangan;
use App\Models\KelompokKkn;
use App\Models\KelompokKuota;
use App\Models\PesertaKkn;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

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

                $q->where(
                    'kode_kelompok',
                    'like',
                    '%' . $request->search . '%'
                )
                ->orWhere(
                    'nama_kelompok',
                    'like',
                    '%' . $request->search . '%'
                );

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

        $kabupatens = \App\Models\Kecamatan::select('kabupaten')->distinct()->orderBy('kabupaten')->pluck('kabupaten');
        $kecamatans = collect();
        $selectedKabupaten = $request->get('kabupaten');

        if ($selectedKabupaten) {
            $kecamatans = \App\Models\Kecamatan::where('kabupaten', $selectedKabupaten)
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

            'desa_gelombang_id' =>
                'required|exists:desa_gelombang,id',

            'dosen_pembimbing_lapangan_id' =>
                'nullable|exists:dosen_pembimbing_lapangan,id',

            'kuota' =>
                'required|integer|min:1|max:20',

            'status' =>
                'required|in:draft,dibuka,ditutup',

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
            . ' - ' .
            $desaGelombang->gelombang->nama_gelombang;

        KelompokKkn::create($validated);

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

        $proposal = \App\Models\KelompokProposal::where('kelompok_kkn_id', $kelompok_kkn->id)->first();
        $statusService = app(\App\Services\StatusService::class);
        $statusStages = \App\Services\StatusService::STAGES;
        $statusCurrent = $statusService->getCurrentStage($kelompok_kkn);
        $statusHistory = $statusService->getHistory($kelompok_kkn);
        $tugasList = \App\Models\TugasKelompok::where('kelompok_kkn_id', $kelompok_kkn->id)->with(['submissions.pesertaKkn.mahasiswa.user'])->get()->groupBy('kategori');
        $logbookData = \App\Models\LogBook::where('kelompok_kkn_id', $kelompok_kkn->id)->with(['pesertaKkn.mahasiswa.user'])->latest('tanggal')->get()->groupBy('peserta_kkn_id');
        $komponenList = \App\Models\PenilaianKomponen::orderBy('urutan')->get();
        $penilaianData = \App\Models\PenilaianKelompok::where('kelompok_kkn_id', $kelompok_kkn->id)->with('komponen')->get()->keyBy('komponen_id');

        return view(
            'kelompok-kkn.show',
            compact('kelompok_kkn', 'proposal', 'statusStages', 'statusCurrent', 'statusHistory', 'tugasList', 'logbookData', 'komponenList', 'penilaianData')
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

            'desa_gelombang_id' =>
                'required|exists:desa_gelombang,id',

            'dosen_pembimbing_lapangan_id' =>
                'nullable|exists:dosen_pembimbing_lapangan,id',

            'kuota' =>
                'required|integer|min:1|max:20',

            'status' =>
                'required|in:draft,dibuka,ditutup,penuh',

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
            . ' - ' .
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
            'status' => 'dibuka'
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
            'status' => 'ditutup'
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
            ->when(request('search'), fn($q) => $q->whereHas('mahasiswa.user', fn($q) =>
                $q->where('name', 'like', '%'.request('search').'%')
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

        /*
        |--------------------------------------------------------------------------
        | Kelompok Full Check
        |--------------------------------------------------------------------------
        */

        if ($kelompok_kkn->is_full) {

            return back()->with(
                'error',
                'Kelompok sudah penuh.'
            );

        }

        $peserta = PesertaKkn::findOrFail(
            $validated['peserta_kkn_id']
        );

        /*
        |--------------------------------------------------------------------------
        | Prevent Double Group
        |--------------------------------------------------------------------------
        */

        if ($peserta->kelompok_kkn_id) {

            return back()->with(
                'error',
                'Mahasiswa sudah memiliki kelompok.'
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Assign Kelompok
        |--------------------------------------------------------------------------
        */

        // Rule checks (same as WAR system)
        $currentMembers = PesertaKkn::where('kelompok_kkn_id', $kelompok_kkn->id)
            ->with('mahasiswa.prodi')->get();

        if ($currentMembers->count() >= 12) {
            return back()->with('error', 'Kelompok sudah penuh (maks 12 orang).');
        }

        // Gender check
        $gender = $peserta->mahasiswa?->jenis_kelamin;
        $maxGender = $gender === 'L' ? 4 : 9;
        $genderCount = $currentMembers->filter(fn($m) => $m->mahasiswa?->jenis_kelamin === $gender)->count();
        if ($genderCount >= $maxGender) {
            return back()->with('error', "Kuota {$maxGender} {$gender} sudah penuh di kelompok ini.");
        }

        // Faculty quota check
        $fakId = $peserta->mahasiswa?->prodi?->fakultas_id;
        $fakKuota = KelompokKuota::where('kelompok_kkn_id', $kelompok_kkn->id)->where('fakultas_id', $fakId)->first();
        if ($fakKuota) {
            $fakCount = $currentMembers->filter(fn($m) => $m->mahasiswa?->prodi?->fakultas_id === $fakId)->count();
            if ($fakCount >= $fakKuota->kuota) {
                return back()->with('error', "Kuota fakultas sudah penuh di kelompok ini (maks {$fakKuota->kuota} orang).");
            }
        }

        // Prodi check
        $prodiId = $peserta->mahasiswa?->prodi_id;
        $prodiCountInFak = ProgramStudi::where('fakultas_id', $fakId)->count();
        if ($prodiCountInFak > 1) {
            $prodiCount = $currentMembers->filter(fn($m) => $m->mahasiswa?->prodi_id === $prodiId)->count();
            if ($prodiCount >= 1) {
                return back()->with('error', 'Program studi ini sudah ada di kelompok ini (maks 1 per prodi).');
            }
        }

        $peserta->kelompok_kkn_id = $kelompok_kkn->id;
        $peserta->save();

        /*
        |--------------------------------------------------------------------------
        | Auto Full Status
        |--------------------------------------------------------------------------
        */

        if (
            $kelompok_kkn->fresh()->pesertaKkn()->count()
            >=
            $kelompok_kkn->kuota
        ) {

            $kelompok_kkn->update([
                'status' => 'penuh'
            ]);

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

        /*
        |--------------------------------------------------------------------------
        | Ensure Member Belongs To Group
        |--------------------------------------------------------------------------
        */

        if (
            $peserta->kelompok_kkn_id
            !=
            $kelompok_kkn->id
        ) {

            return back()->with(
                'error',
                'Anggota tidak ditemukan pada kelompok ini.'
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Remove From Group
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($kelompok_kkn, $peserta) {

            $peserta->update([
                'kelompok_kkn_id' => null,
            ]);

            if ($kelompok_kkn->ketua_peserta_id === $peserta->id) {
                $kelompok_kkn->updateQuietly(['ketua_peserta_id' => null]);
            }

            \App\Models\WarParticipant::where('peserta_kkn_id', $peserta->id)->delete();

        });

        /*
        |--------------------------------------------------------------------------
        | Reopen Group If Needed
        |--------------------------------------------------------------------------
        */

        $kelompok_kkn->refresh();

        if ($kelompok_kkn->status === 'penuh' && $kelompok_kkn->terisi < $kelompok_kkn->kuota) {

            $kelompok_kkn->updateQuietly([
                'status' => 'dibuka'
            ]);

        }

        return back()->with(
            'success',
            'Anggota berhasil dihapus.'
        );
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
            'Ketua kelompok berhasil diubah menjadi ' . ($peserta->mahasiswa?->user?->name ?? 'Unknown') . '.'
        );
    }
}
