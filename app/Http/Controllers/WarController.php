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
        $warStatus   = null; // 'no_war' | 'not_registered' | 'not_approved' | 'already_joined' | 'ready'

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
        } else {
            $warStatus = 'no_war';
        }

        return view('war.index', compact(
            'activeWar',
            'scheduledWars',
            'peserta',
            'warStatus',
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | WAR ARENA — halaman rebutan kelompok
    |--------------------------------------------------------------------------
    | Hanya bisa diakses kalau war aktif dan peserta eligible.
    */
    public function arena(WarSession $session): \Illuminate\View\View
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
            ->where('status', '!=', 'penuh')
            ->orderBy('nama_kelompok')
            ->get();

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

        $kelompoks = KelompokKkn::with([
                'desaGelombang.desa',
                'pesertaKkn',
                'kuotaFakultas.fakultas',
            ])
            ->whereHas('desaGelombang', fn ($q) => $q->where('gelombang_id', $session->gelombang_id))
            ->orderBy('nama_kelompok')
            ->get()
            ->map(fn ($k) => [
                'id'          => $k->id,
                'nama'        => $k->nama_kelompok,
                'desa'        => $k->desaGelombang?->desa?->nama_desa,
                'terisi'      => $k->terisi,
                'kuota'       => $k->kuota,
                'sisa'        => $k->sisa_kuota,
                'is_full'     => $k->is_full,
                'status'      => $k->status,
            ]);

        return response()->json([
            'kelompoks'   => $kelompoks,
            'server_time' => now()->toISOString(),
        ]);
    }
}
