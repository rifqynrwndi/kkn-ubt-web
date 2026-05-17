<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\Gelombang;
use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\WarFaculty;
use App\Models\WarLog;
use App\Models\WarSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarAdminController extends Controller
{
    public function index()
    {
        $wars = WarSession::with(['gelombang', 'faculties.fakultas'])
            ->withCount('participants')
            ->latest()
            ->get();

        $activeWar = WarSession::where('status', 'active')
            ->with(['gelombang', 'faculties.fakultas'])
            ->first();

        $gelombangs = Gelombang::latest()->get();
        $fakultas = Fakultas::orderBy('nama_fakultas')->get();

        return view('war-admin.index', compact('wars', 'activeWar', 'gelombangs', 'fakultas'));
    }

    public function show(WarSession $war)
    {
        $war->load([
            'gelombang',
            'faculties.fakultas',
            'participants.pesertaKkn.mahasiswa.prodi.fakultas',
            'participants.kelompokKkn',
        ]);

        $war->loadCount('participants');

        $fakultas = Fakultas::orderBy('nama_fakultas')->get();

        return view('war-admin.show', compact('war', 'fakultas'));
    }

    public function create()
    {
        $gelombangs = Gelombang::latest()->get();
        return view('war-admin.create', compact('gelombangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'gelombang_id' => 'required|exists:gelombang,id',
            'start_at'     => 'required|date',
            'end_at'       => 'required|date|after:start_at',
        ]);

        WarSession::create([
            'name'         => $request->name,
            'gelombang_id' => $request->gelombang_id,
            'start_at'     => $request->start_at,
            'end_at'       => $request->end_at,
            'status'       => 'scheduled',
        ]);

        return redirect()->route('admin.war.index')->with('success', 'Sesi WAR berhasil dibuat.');
    }

    public function update(Request $request, WarSession $war)
    {
        if ($war->status === 'active') {
            return back()->with('error', 'Tidak dapat mengedit sesi WAR yang sedang aktif.');
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at'   => 'required|date|after:start_at',
        ]);

        $war->update($request->only(['name', 'start_at', 'end_at']));

        return back()->with('success', 'Sesi WAR berhasil diperbarui.');
    }

    public function destroy(WarSession $war)
    {
        if ($war->status === 'active') {
            return back()->with('error', 'Tidak dapat menghapus sesi WAR yang sedang aktif.');
        }
        if ($war->participants()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus sesi WAR yang sudah memiliki peserta.');
        }

        $war->delete();

        return redirect()->route('admin.war.index')->with('success', 'Sesi WAR berhasil dihapus.');
    }

    public function setFacultyQuota(Request $request, WarSession $war)
    {
        $request->validate([
            'faculties'   => 'required|array',
            'faculties.*' => 'exists:fakultas,id',
        ]);

        DB::transaction(function () use ($request, $war) {
            foreach ($request->faculties as $fakultasId) {
                // Hitung kuota berdasarkan jumlah peserta KKN di gelombang ini untuk fakultas tersebut
                $quota = \App\Models\PesertaKkn::where('gelombang_id', $war->gelombang_id)
                    ->whereHas('mahasiswa.prodi', function ($query) use ($fakultasId) {
                        $query->where('fakultas_id', $fakultasId);
                    })
                    ->count();

                $warFaculty = WarFaculty::firstOrNew([
                    'war_session_id' => $war->id,
                    'fakultas_id'    => $fakultasId,
                ]);

                $warFaculty->quota = $quota;
                
                // Jika data baru, inisialisasi filled dan jadwal
                if (!$warFaculty->exists) {
                    $warFaculty->filled = 0;
                    $warFaculty->start_at = null;
                    $warFaculty->end_at = null;
                }
                
                $warFaculty->save();
            }
        });

        return back()->with('success', 'Fakultas berhasil ditambahkan dan kuota dihitung otomatis.');
    }

    public function setFacultySchedule(Request $request, WarSession $war)
    {
        $request->validate([
            'schedules'               => 'required|array',
            'schedules.*.fakultas_id' => 'required|exists:fakultas,id',
            'schedules.*.start_at'    => 'required|date',
            'schedules.*.end_at'      => 'required|date|after:schedules.*.start_at',
        ]);

        DB::transaction(function () use ($request, $war) {
            foreach ($request->schedules as $schedule) {
                WarFaculty::where('war_session_id', $war->id)
                    ->where('fakultas_id', $schedule['fakultas_id'])
                    ->update([
                        'start_at' => $schedule['start_at'],
                        'end_at'   => $schedule['end_at'],
                    ]);
            }
        });

        return back()->with('success', 'Jadwal per fakultas berhasil diatur.');
    }

    public function activate(WarSession $war)
    {
        if ($war->status === 'active') {
            return back()->with('error', 'Sesi WAR sudah aktif.');
        }

        DB::transaction(function () use ($war) {
            WarSession::query()->update(['status' => 'scheduled']);

            $war->update([
                'status'   => 'active',
                'start_at' => now(),
            ]);
        });

        return back()->with('success', 'Sesi WAR berhasil diaktifkan.');
    }

    public function stop(WarSession $war)
    {
        if ($war->status !== 'active') {
            return back()->with('error', 'Sesi WAR belum diaktifkan.');
        }

        if ($war->status === 'closed') {
            return back()->with('success', 'Sesi WAR sudah dalam status selesai.');
        }

        $war->update([
            'status' => 'closed',
            'end_at' => now(),
        ]);

        return back()->with('success', 'Sesi WAR berhasil dihentikan.');
    }

    public function reset(WarSession $war)
    {
        if ($war->status === 'active') {
            return back()->with('error', 'Hentikan sesi WAR terlebih dahulu sebelum melakukan reset.');
        }

        DB::transaction(function () use ($war) {

            // Kumpulkan ID peserta_kkn yang terdaftar di sesi ini
            $pesertaIds = \App\Models\WarParticipant::where('war_session_id', $war->id)
                ->pluck('peserta_kkn_id');

            // Hapus semua catatan WarParticipant untuk sesi ini
            \App\Models\WarParticipant::where('war_session_id', $war->id)->delete();

            // Null-kan kelompok_kkn_id untuk peserta yang terdaftar di sesi ini
            if ($pesertaIds->isNotEmpty()) {
                PesertaKkn::whereIn('id', $pesertaIds)
                    ->update(['kelompok_kkn_id' => null]);

                KelompokKkn::whereHas('pesertaKkn', function ($q) use ($pesertaIds) {
                    $q->whereIn('id', $pesertaIds);
                })->update(['ketua_peserta_id' => null]);
            }

            // Reset filled count di WarFaculty
            WarFaculty::where('war_session_id', $war->id)->update(['filled' => 0]);

            // Reset sesi ke scheduled
            $war->update(['status' => 'scheduled']);
        });

        return back()->with('success', 'Sesi WAR berhasil direset. Semua data peserta telah dihapus.');
    }

    public function monitor(WarSession $war)
    {
        $war->load(['gelombang', 'faculties.fakultas']);
        $war->loadCount('participants');

        $kelompoks = KelompokKkn::with([
                'desaGelombang.desa',
                'pesertaKkn.mahasiswa.prodi.fakultas',
            ])
            ->whereHas('desaGelombang', fn($q) => $q->where('gelombang_id', $war->gelombang_id))
            ->get();

        $totalPesertaGelombang = PesertaKkn::where('gelombang_id', $war->gelombang_id)->count();

        $fakultasStats = PesertaKkn::where('gelombang_id', $war->gelombang_id)
            ->with('mahasiswa.prodi.fakultas')
            ->get()
            ->groupBy(fn($p) => $p->mahasiswa->prodi->fakultas_id)
            ->map(function ($pesertas, $fakultasId) {
                $fakultas = $pesertas->first()->mahasiswa->prodi->fakultas;
                $total = $pesertas->count();
                $filled = $pesertas->filter(fn($p) => $p->kelompok_kkn_id !== null)->count();
                return [
                    'fakultas_id' => $fakultasId,
                    'nama'        => $fakultas->nama_fakultas ?? 'N/A',
                    'total'       => $total,
                    'filled'      => $filled,
                    'persen'      => $total > 0 ? round(($filled / $total) * 100) : 0,
                ];
            })
            ->values();

        $kelompokData = KelompokKkn::whereHas('desaGelombang', fn($q) =>
                $q->where('gelombang_id', $war->gelombang_id)
            )
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "penuh" THEN 1 ELSE 0 END) as penuh')
            ->first();

        $kelompokTersedia = ($kelompokData->total ?? 0) - ($kelompokData->penuh ?? 0);
        $kelompokPenuh    = $kelompokData->penuh ?? 0;

        return view('war-admin.monitor', compact(
            'war', 'kelompoks', 'fakultasStats', 'totalPesertaGelombang',
            'kelompokTersedia', 'kelompokPenuh',
        ));
    }

    public function monitorStats(WarSession $war)
    {
        $totalPeserta = $war->participants()->count();
        $totalPesertaGelombang = PesertaKkn::where('gelombang_id', $war->gelombang_id)->count();

        $kelompokData = KelompokKkn::whereHas('desaGelombang', fn($q) => 
                $q->where('gelombang_id', $war->gelombang_id)
            )
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "penuh" THEN 1 ELSE 0 END) as penuh')
            ->first();

        $fakultasStats = PesertaKkn::where('gelombang_id', $war->gelombang_id)
            ->with('mahasiswa.prodi.fakultas')
            ->get()
            ->groupBy(fn($p) => $p->mahasiswa->prodi->fakultas_id)
            ->map(function ($pesertas, $fakultasId) {
                $fakultas = $pesertas->first()->mahasiswa->prodi->fakultas;
                $total = $pesertas->count();
                $filled = $pesertas->filter(fn($p) => $p->kelompok_kkn_id !== null)->count();
                return [
                    'fakultas_id' => $fakultasId,
                    'nama'        => $fakultas->nama_fakultas ?? 'N/A',
                    'quota'       => $total,
                    'filled'      => $filled,
                    'persen'      => $total > 0 ? round(($filled / $total) * 100) : 0,
                ];
            })
            ->values();

        return response()->json([
            'total_peserta'           => $totalPeserta,
            'total_peserta_gelombang' => $totalPesertaGelombang,
            'kelompok'                => [
                'total'   => $kelompokData->total ?? 0,
                'penuh'   => $kelompokData->penuh ?? 0,
                'tersisa' => ($kelompokData->total ?? 0) - ($kelompokData->penuh ?? 0),
            ],
            'fakultas' => $fakultasStats,
        ]);
    }

    public function monitorLogs(Request $request, WarSession $war)
    {
        $limit = $request->input('limit', 20);

        $logs = WarLog::where('war_session_id', $war->id)
            ->with('pesertaKkn.mahasiswa')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($log) => [
                'id'      => $log->id,
                'peserta' => $log->pesertaKkn->mahasiswa->name ?? 'Unknown',
                'action'  => $log->action,
                'meta'    => json_decode($log->meta),
                'human'   => $log->created_at->diffForHumans(),
            ]);

        return response()->json(['logs' => $logs]);
    }

    public function monitorKelompoks(WarSession $war)
    {
        $kelompoks = KelompokKkn::whereHas('desaGelombang', fn($q) => 
                $q->where('gelombang_id', $war->gelombang_id)
            )
            ->withCount('pesertaKkn')
            ->get()
            ->map(fn($k) => [
                'id'     => $k->id,
                'terisi' => $k->peserta_kkn_count,
                'kuota'  => $k->kuota,
                'status' => $k->status,
            ]);

        return response()->json(['kelompoks' => $kelompoks]);
    }

    public function autoClose(): bool
    {
        $expired = WarSession::where('status', 'active')
            ->where('end_at', '<=', now())
            ->get();

        foreach ($expired as $war) {
            $war->update(['status' => 'closed']);

            logger()->info('WAR auto-closed', [
                'war_id' => $war->id,
                'name'   => $war->name,
                'end_at' => $war->end_at,
            ]);
        }

        return true;
    }
}