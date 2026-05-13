<?php

namespace App\Http\Controllers;

use App\Models\KelompokKkn;
use App\Models\WarLog;
use App\Models\WarParticipant;
use App\Models\WarSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarMonitorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MONITOR DASHBOARD — view
    |--------------------------------------------------------------------------
    | Admin monitor page (Blade). Data awal diload di sini,
    | lalu update realtime via AJAX polling ke endpoint di bawah.
    */
    public function show(WarSession $session): \Illuminate\View\View
    {
        $session->load([
            'gelombang',
            'faculties.fakultas',
        ]);

        $session->loadCount('participants');

        return view('war-admin.monitor', compact('session'));
    }

    /*
    |--------------------------------------------------------------------------
    | LIVE STATS — AJAX
    |--------------------------------------------------------------------------
    | Polling endpoint untuk statistik ringkas war session.
    | Dipakai admin dashboard untuk update angka secara realtime.
    */
    public function stats(WarSession $session): JsonResponse
    {
        $session->load([
            'faculties.fakultas',
            'gelombang',
        ]);

        $session->loadCount('participants');

        /*
        |--------------------------------------------------------------------------
        | KELOMPOK STATS
        |--------------------------------------------------------------------------
        */
        $kelompoks = KelompokKkn::withCount('pesertaKkn')
            ->whereHas('desaGelombang', fn ($q) => $q->where('gelombang_id', $session->gelombang_id))
            ->get();

        $kelompokStats = [
            'total'  => $kelompoks->count(),
            'penuh'  => $kelompoks->where('status', 'penuh')->count(),
            'tersisa'=> $kelompoks->where('status', '!=', 'penuh')->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | FAKULTAS QUOTA PROGRESS
        |--------------------------------------------------------------------------
        */
        $fakultasStats = $session->faculties->map(fn ($wf) => [
            'nama'    => $wf->fakultas?->nama_fakultas ?? 'N/A',
            'quota'   => $wf->quota,
            'filled'  => $wf->filled,
            'sisa'    => $wf->sisa,
            'persen'  => $wf->quota > 0
                ? round(($wf->filled / $wf->quota) * 100, 1)
                : 0,
            'status'  => $wf->status_jadwal,
        ]);

        return response()->json([
            'war_status'       => $session->status,
            'total_peserta'    => $session->participants_count,
            'kelompok'         => $kelompokStats,
            'fakultas'         => $fakultasStats,
            'server_time'      => now()->toISOString(),
            'end_at'           => $session->end_at?->toISOString(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LIVE KELOMPOK FEED — AJAX
    |--------------------------------------------------------------------------
    | Daftar kelompok beserta jumlah anggota saat ini.
    | Di-refresh periodic oleh admin dashboard.
    */
    public function kelompoks(WarSession $session): JsonResponse
    {
        $kelompoks = KelompokKkn::with([
                'desaGelombang.desa',
                'pesertaKkn.mahasiswa.prodi.fakultas',
            ])
            ->whereHas('desaGelombang', fn ($q) => $q->where('gelombang_id', $session->gelombang_id))
            ->orderBy('nama_kelompok')
            ->get()
            ->map(fn ($k) => [
                'id'       => $k->id,
                'nama'     => $k->nama_kelompok,
                'desa'     => $k->desaGelombang?->desa?->nama_desa ?? '-',
                'terisi'   => $k->pesertaKkn->count(),
                'kuota'    => $k->kuota,
                'is_full'  => $k->is_full,
                'status'   => $k->status,
                'anggota'  => $k->pesertaKkn->map(fn ($p) => [
                    'nama'      => $p->mahasiswa?->user?->name ?? '-',
                    'prodi'     => $p->mahasiswa?->prodi?->nama_prodi ?? '-',
                    'fakultas'  => $p->mahasiswa?->prodi?->fakultas?->nama_fakultas ?? '-',
                    'gender'    => $p->mahasiswa?->jenis_kelamin ?? '-',
                ]),
            ]);

        return response()->json([
            'kelompoks'   => $kelompoks,
            'server_time' => now()->toISOString(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LIVE ACTIVITY LOG — AJAX
    |--------------------------------------------------------------------------
    | Log terakhir N aksi WAR. Dipakai untuk kolom "Latest Activity"
    | di admin monitor dashboard.
    */
    public function logs(Request $request, WarSession $session): JsonResponse
    {
        $limit = min((int) $request->input('limit', 30), 100);

        $logs = WarLog::where('war_session_id', $session->id)
            ->with(['pesertaKkn.mahasiswa'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($log) => [
                'id'        => $log->id,
                'action'    => $log->action,
                'peserta'   => $log->pesertaKkn?->mahasiswa?->user?->name ?? 'N/A',
                'meta'      => is_string($log->meta) ? json_decode($log->meta, true) : $log->meta,
                'created'   => $log->created_at->toISOString(),
                'human'     => $log->created_at->diffForHumans(),
            ]);

        return response()->json([
            'logs'        => $logs,
            'server_time' => now()->toISOString(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LIVE PARTICIPANTS — AJAX
    |--------------------------------------------------------------------------
    | Daftar mahasiswa yang sudah berhasil join sesi WAR ini.
    */
    public function participants(Request $request, WarSession $session): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 25), 100);

        $participants = WarParticipant::where('war_session_id', $session->id)
            ->with([
                'pesertaKkn.mahasiswa.prodi.fakultas',
                'kelompokKkn.desaGelombang.desa',
            ])
            ->latest('joined_at')
            ->paginate($perPage);

        return response()->json([
            'data' => collect($participants->items())->map(fn ($p) => [
                'peserta_id'    => $p->peserta_kkn_id,
                'nama'          => $p->pesertaKkn?->mahasiswa?->user?->name ?? '-',
                'prodi'         => $p->pesertaKkn?->mahasiswa?->prodi?->nama_prodi ?? '-',
                'fakultas'      => $p->pesertaKkn?->mahasiswa?->prodi?->fakultas?->nama_fakultas ?? '-',
                'kelompok'      => $p->kelompokKkn?->nama_kelompok ?? '-',
                'desa'          => $p->kelompokKkn?->desaGelombang?->desa?->nama_desa ?? '-',
                'joined_at'     => $p->joined_at?->toISOString(),
                'joined_human'  => $p->joined_at?->diffForHumans(),
            ]),
            'total'       => $participants->total(),
            'per_page'    => $participants->perPage(),
            'current_page'=> $participants->currentPage(),
            'last_page'   => $participants->lastPage(),
            'server_time' => now()->toISOString(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT LOG — download CSV
    |--------------------------------------------------------------------------
    */
    public function exportLog(WarSession $session): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'war-log-' . $session->id . '-' . now()->format('YmdHis') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($session) {
            $handle = fopen('php://output', 'w');

            /*
            |--------------------------------------------------------------------------
            | HEADER ROW
            |--------------------------------------------------------------------------
            */
            fputcsv($handle, [
                'ID', 'Peserta', 'Prodi', 'Fakultas', 'Action',
                'Kelompok', 'IP', 'Waktu',
            ]);

            WarLog::where('war_session_id', $session->id)
                ->with(['pesertaKkn.mahasiswa.prodi.fakultas'])
                ->orderBy('id')
                ->chunk(200, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        $meta = is_string($log->meta)
                            ? json_decode($log->meta, true)
                            : (array) $log->meta;

                        fputcsv($handle, [
                            $log->id,
                            $log->pesertaKkn?->mahasiswa?->user?->name ?? '-',
                            $log->pesertaKkn?->mahasiswa?->prodi?->nama_prodi ?? '-',
                            $log->pesertaKkn?->mahasiswa?->prodi?->fakultas?->nama_fakultas ?? '-',
                            $log->action,
                            $meta['kelompok_nama'] ?? '-',
                            $meta['ip'] ?? '-',
                            $log->created_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
