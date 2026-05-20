<?php

namespace App\Http\Controllers;

use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\WarParticipant;
use App\Models\WarSession;
use App\Services\War\WarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarController extends Controller
{
    public function __construct(
        private readonly WarService $warService,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | WAR LOBBY — halaman utama mahasiswa
    |--------------------------------------------------------------------------
    | Mahasiswa melihat war session yang sedang aktif / jadwal mendatang.
    */
    public function index(): \Illuminate\View\View
    {
        $activeWar = WarSession::where('status', 'active')
            ->with(['gelombang', 'faculties.fakultas'])
            ->first();

        $scheduledWars = WarSession::where('status', 'scheduled')
            ->with(['gelombang'])
            ->orderBy('start_at')
            ->get();

        $mahasiswa = auth()->user()->mahasiswa;

        /*
        |--------------------------------------------------------------------------
        | CEK STATUS PESERTA
        |--------------------------------------------------------------------------
        | Apakah mahasiswa sudah terdaftar & approved di gelombang war aktif?
        */
        $peserta     = null;
        $warStatus   = null;
        $warStats    = null;

        if ($activeWar) {
            $peserta = PesertaKkn::where('mahasiswa_id', $mahasiswa?->user_id)
                ->where('gelombang_id', $activeWar->gelombang_id)
                ->with(['kelompokKkn.desaGelombang.desa'])
                ->first();

            if (! $peserta) {
                $warStatus = 'not_registered';
            } elseif ($peserta->status_pendaftaran !== 'approved') {
                $warStatus = 'not_approved';
            } elseif ($peserta->kelompok_kkn_id !== null) {
                $warStatus = 'already_joined';
            } else {
                $warStatus = 'ready';
            }

            $kelompokStats = KelompokKkn::whereHas('desaGelombang',
                fn ($q) => $q->where('gelombang_id', $activeWar->gelombang_id)
            )
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "penuh" THEN 1 ELSE 0 END) as penuh')
            ->first();

            $warStats = [
                'total_peserta'  => $activeWar->participants()->count(),
                'kelompok_sisa'  => ($kelompokStats->total ?? 0) - ($kelompokStats->penuh ?? 0),
                'kelompok_penuh' => $kelompokStats->penuh ?? 0,
            ];
        } else {
            $warStatus = 'no_war';
        }

        return view('war.index', compact(
            'activeWar',
            'scheduledWars',
            'peserta',
            'warStatus',
            'warStats',
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | WAR ARENA — halaman rebutan kelompok
    |--------------------------------------------------------------------------
    | Hanya bisa diakses kalau war aktif dan peserta eligible.
    */
    public function arena(WarSession $session): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI SESSION AKTIF
        |--------------------------------------------------------------------------
        */
        abort_if($session->status !== 'active', 403, 'WAR session ini tidak aktif.');

        $mahasiswa = auth()->user()->mahasiswa;

        $peserta = PesertaKkn::where('mahasiswa_id', $mahasiswa?->user_id)
            ->where('gelombang_id', $session->gelombang_id)
            ->with(['mahasiswa.prodi.fakultas'])
            ->first();

        /*
        |--------------------------------------------------------------------------
        | GATE CHECK
        |--------------------------------------------------------------------------
        */
        abort_if(! $peserta, 403, 'Kamu tidak terdaftar di gelombang ini.');
        abort_if($peserta->status_pendaftaran !== 'approved', 403, 'Status pendaftaran kamu belum disetujui.');

        /*
        |--------------------------------------------------------------------------
        | REDIRECT JIKA SUDAH PUNYA KELOMPOK
        |--------------------------------------------------------------------------
        */
        if ($peserta->kelompok_kkn_id !== null) {
            return redirect()->route('war.joined', $session)
                ->with('info', 'Kamu sudah terdaftar di kelompok.');
        }

        /*
        |--------------------------------------------------------------------------
        | DATA KELOMPOK
        |--------------------------------------------------------------------------
        */
        $kelompoks = KelompokKkn::with([
                'desaGelombang.desa.kecamatan',
                'pesertaKkn.mahasiswa.prodi.fakultas',
                'kuotaFakultas.fakultas',
            ])
            ->whereHas('desaGelombang', fn ($q) => $q->where('gelombang_id', $session->gelombang_id))
            ->orderBy('nama_kelompok')
            ->get()
            ->each(function ($k) use ($peserta) {
                $fakultasId = $peserta->mahasiswa->prodi->fakultas_id;
                $prodiId    = $peserta->mahasiswa->prodi_id;

                $kuotaFakultas = $k->kuotaFakultas->where('fakultas_id', $fakultasId)->first();
                $fakCount = $k->pesertaKkn->filter(fn($p) =>
                    $p->mahasiswa?->prodi?->fakultas_id === $fakultasId
                )->count();
                $prodiCount = $k->pesertaKkn->filter(fn($p) =>
                    $p->mahasiswa?->prodi_id === $prodiId
                )->count();

                $fakOver = $kuotaFakultas && $fakCount >= $kuotaFakultas->kuota;
                $prodiOver = $prodiCount >= \App\Services\War\WarRuleService::MAX_SAME_PRODI;

                $k->can_join = !$k->is_full && !$fakOver && !$prodiOver;
            })
            ->sortBy(function ($k) {
                $kab = $k->desaGelombang->desa->kecamatan->kabupaten ?? 'Z';
                if ($k->is_full) return $kab . '-2';
                if (!$k->can_join) return $kab . '-1';
                return $kab . '-0';
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | JADWAL FAKULTAS
        |--------------------------------------------------------------------------
        */
        $warFaculty = $session->faculties()
            ->where('fakultas_id', $peserta->mahasiswa->prodi->fakultas_id)
            ->first();

        return view('war.arena', compact(
            'session',
            'peserta',
            'kelompoks',
            'warFaculty',
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | ALREADY JOINED PAGE
    |--------------------------------------------------------------------------
    | Halaman konfirmasi setelah berhasil join kelompok.
    */
    public function joined(WarSession $session): \Illuminate\View\View
    {
        $mahasiswa = auth()->user()->mahasiswa;

        $peserta = PesertaKkn::where('mahasiswa_id', $mahasiswa?->user_id)
            ->where('gelombang_id', $session->gelombang_id)
            ->with([
                'kelompokKkn.desaGelombang.desa.kecamatan',
                'kelompokKkn.dosenPembimbingLapangan.user',
                'kelompokKkn.pesertaKkn.mahasiswa.user',
                'kelompokKkn.pesertaKkn.mahasiswa.prodi.fakultas',
            ])
            ->firstOrFail();

        abort_if($peserta->kelompok_kkn_id === null, 404, 'Kamu belum bergabung ke kelompok manapun.');

        $participant = WarParticipant::where('war_session_id', $session->id)
            ->where('peserta_kkn_id', $peserta->id)
            ->first();

        return view('war.joined', compact('session', 'peserta', 'participant'));
    }

    /*
    |--------------------------------------------------------------------------
    | JOIN KELOMPOK — core action (AJAX/POST)
    |--------------------------------------------------------------------------
    | Endpoint utama war engine. Dipanggil saat mahasiswa klik tombol JOIN.
    | Thin controller — semua logic di WarService.
    */
    public function join(Request $request, WarSession $session, int $kelompokId): JsonResponse
    {
        $mahasiswa = $request->user()->mahasiswa;

        abort_if(! $mahasiswa, 403, 'Data mahasiswa tidak ditemukan.');

        try {
            $result = $this->warService->joinKelompok(
                session:     $session,
                kelompokId:  $kelompokId,
                mahasiswaId: $mahasiswa->user_id,
            );

            return response()->json($result);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            logger()->error('WAR join error', [
                'session_id'  => $session->id,
                'kelompok_id' => $kelompokId,
                'user_id'     => $request->user()->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS CHECK — AJAX polling
    |--------------------------------------------------------------------------
    | Frontend poll ini setiap N detik untuk tahu apakah war masih aktif
    | dan apakah mahasiswa sudah dapat kelompok.
    */
    public function status(Request $request, WarSession $session): JsonResponse
    {
        $mahasiswa = $request->user()->mahasiswa;

        $peserta = PesertaKkn::where('mahasiswa_id', $mahasiswa?->id)
            ->where('gelombang_id', $session->gelombang_id)
            ->first();

        $hasKelompok = $peserta && $peserta->kelompok_kkn_id !== null;

        return response()->json([
            'war_status'   => $session->status,
            'has_kelompok' => $hasKelompok,
            'kelompok_id'  => $peserta?->kelompok_kkn_id,
            'server_time'  => now()->toISOString(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | KELOMPOK LIST — AJAX untuk refresh live count
    |--------------------------------------------------------------------------
    | Dipakai frontend untuk refresh daftar kelompok tanpa full reload.
    */
    public function kelompokList(WarSession $session): JsonResponse
    {
        abort_if($session->status !== 'active', 403, 'WAR tidak aktif.');

        $mahasiswa = auth()->user()->mahasiswa;
        $fakultasId = $mahasiswa?->prodi?->fakultas_id;
        $prodiId    = $mahasiswa?->prodi_id;

        $kelompoks = KelompokKkn::with([
                'desaGelombang.desa',
                'pesertaKkn.mahasiswa.prodi',
                'kuotaFakultas',
            ])
            ->whereHas('desaGelombang', fn ($q) => $q->where('gelombang_id', $session->gelombang_id))
            ->orderBy('nama_kelompok')
            ->get()
            ->map(function ($k) use ($fakultasId, $prodiId) {
                $kuotaFakultas = $k->kuotaFakultas->where('fakultas_id', $fakultasId)->first();
                $fakCount = $k->pesertaKkn->filter(fn($p) => $p->mahasiswa?->prodi?->fakultas_id === $fakultasId)->count();
                $prodiCount = $k->pesertaKkn->filter(fn($p) => $p->mahasiswa?->prodi_id === $prodiId)->count();

                $fakOver = $kuotaFakultas && $fakCount >= $kuotaFakultas->kuota;
                $prodiOver = $prodiCount >= \App\Services\War\WarRuleService::MAX_SAME_PRODI;

                return [
                    'id'          => $k->id,
                    'nama'        => $k->nama_kelompok,
                    'desa'        => $k->desaGelombang?->desa?->nama_desa,
                    'terisi'      => $k->terisi,
                    'kuota'       => $k->kuota,
                    'sisa'        => $k->sisa_kuota,
                    'is_full'     => $k->is_full,
                    'status'      => $k->status,
                    'can_join'    => !$k->is_full && !$fakOver && !$prodiOver,
                ];
            })
            ->sortBy(function ($k) {
                if ($k['is_full']) return 2;
                if (!$k['can_join']) return 1;
                return 0;
            })
            ->values();

        return response()->json([
            'kelompoks'   => $kelompoks,
            'server_time' => now()->toISOString(),
        ]);
    }
}
