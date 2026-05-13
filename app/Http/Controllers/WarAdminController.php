<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\Gelombang;
use App\Models\KelompokKkn;
use App\Models\KelompokKuota;
use App\Models\KuotaFakultasDesa;
use App\Models\WarFaculty;
use App\Models\WarSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarAdminController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD WAR
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $wars = WarSession::with([
                'gelombang',
                'faculties.fakultas',
            ])
            ->withCount('participants')
            ->latest()
            ->get();

        $activeWar = WarSession::where('status', 'active')
            ->with([
                'gelombang',
                'faculties.fakultas',
            ])
            ->first();

        $gelombangs = Gelombang::latest()->get();

        $fakultas = Fakultas::orderBy('nama_fakultas')->get();

        return view('war-admin.index', compact(
            'wars',
            'activeWar',
            'gelombangs',
            'fakultas',
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL WAR SESSION
    |--------------------------------------------------------------------------
    */
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

        $kelompoks = KelompokKkn::with([
                'desaGelombang.desa',
                'pesertaKkn',
            ])
            ->whereHas('desaGelombang', fn ($q) => $q->where('gelombang_id', $war->gelombang_id))
            ->orderBy('nama_kelompok')
            ->get();

        return view('war-admin.show', compact('war', 'fakultas', 'kelompoks'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE WAR SESSION
    |--------------------------------------------------------------------------
    */
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

        return back()->with('success', 'Sesi WAR berhasil dibuat.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE WAR SESSION
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, WarSession $war)
    {
        abort_if($war->status === 'active', 422, 'Tidak bisa edit WAR yang sedang aktif.');

        $request->validate([
            'name'         => 'required|string|max:255',
            'start_at'     => 'required|date',
            'end_at'       => 'required|date|after:start_at',
        ]);

        $war->update([
            'name'     => $request->name,
            'start_at' => $request->start_at,
            'end_at'   => $request->end_at,
        ]);

        return back()->with('success', 'Sesi WAR berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE WAR SESSION
    |--------------------------------------------------------------------------
    */
    public function destroy(WarSession $war)
    {
        abort_if($war->status === 'active', 422, 'Tidak bisa hapus WAR yang sedang aktif.');
        abort_if($war->participants()->exists(), 422, 'Tidak bisa hapus WAR yang sudah ada peserta.');

        $war->delete();

        return back()->with('success', 'Sesi WAR berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | SET FAKULTAS KUOTA WAR
    |--------------------------------------------------------------------------
    | Menentukan fakultas mana yang ikut war dan berapa total kuotanya.
    | Kuota dihitung otomatis dari kelompok_kuota di gelombang tersebut.
    */
    public function setFacultyQuota(Request $request, WarSession $war)
    {
        $request->validate([
            'faculties'   => 'required|array',
            'faculties.*' => 'exists:fakultas,id',
        ]);

        DB::transaction(function () use ($request, $war) {
            foreach ($request->faculties as $fakultasId) {

                /*
                |--------------------------------------------------------------------------
                | HITUNG TOTAL KUOTA DARI SEMUA KELOMPOK GELOMBANG INI
                |--------------------------------------------------------------------------
                */
                $totalQuota = KelompokKuota::where('fakultas_id', $fakultasId)
                    ->whereHas('kelompokKkn.desaGelombang', fn ($q) =>
                        $q->where('gelombang_id', $war->gelombang_id)
                    )
                    ->sum(DB::raw('kuota_laki + kuota_perempuan'));

                WarFaculty::updateOrCreate(
                    [
                        'war_session_id' => $war->id,
                        'fakultas_id'    => $fakultasId,
                    ],
                    [
                        'quota'    => $totalQuota,
                        'filled'   => 0,
                        'start_at' => null,
                        'end_at'   => null,
                    ]
                );
            }
        });

        return back()->with('success', 'Kuota fakultas WAR berhasil diatur.');
    }

    /*
    |--------------------------------------------------------------------------
    | SET JADWAL PER FAKULTAS
    |--------------------------------------------------------------------------
    | Setiap fakultas bisa punya jadwal giliran berbeda.
    | Digunakan untuk sistem gelombang per-fakultas.
    */
    public function setFacultySchedule(Request $request, WarSession $war)
    {
        $request->validate([
            'schedules'                => 'required|array',
            'schedules.*.fakultas_id'  => 'required|exists:fakultas,id',
            'schedules.*.start_at'     => 'required|date',
            'schedules.*.end_at'       => 'required|date|after:schedules.*.start_at',
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

        return back()->with('success', 'Jadwal per-fakultas berhasil diatur.');
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVATE WAR
    |--------------------------------------------------------------------------
    */
    public function activate(WarSession $war)
    {
        abort_if($war->status === 'active', 422, 'WAR sudah aktif.');

        DB::transaction(function () use ($war) {

            /*
            |--------------------------------------------------------------------------
            | NONAKTIFKAN WAR LAIN (hanya 1 WAR boleh aktif)
            |--------------------------------------------------------------------------
            */
            WarSession::query()->update(['status' => 'scheduled']);

            $war->update([
                'status'   => 'active',
                'start_at' => now(),
            ]);
        });

        return back()->with('success', 'Sesi WAR berhasil diaktifkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | STOP WAR
    |--------------------------------------------------------------------------
    */
    public function stop(WarSession $war)
    {
        abort_if($war->status !== 'active', 422, 'WAR tidak sedang aktif.');

        $war->update([
            'status' => 'closed',
            'end_at' => now(),
        ]);

        return back()->with('success', 'Sesi WAR berhasil dihentikan.');
    }

    /*
    |--------------------------------------------------------------------------
    | RESET WAR
    |--------------------------------------------------------------------------
    | Mengembalikan WAR ke status scheduled.
    | Hanya bisa jika WAR belum ada peserta, atau admin ingin force reset.
    */
    public function reset(WarSession $war)
    {
        abort_if($war->status === 'active', 422, 'Hentikan WAR terlebih dahulu sebelum reset.');

        DB::transaction(function () use ($war) {

            $war->update(['status' => 'scheduled']);

            /*
            |--------------------------------------------------------------------------
            | RESET TAKEN COUNT DI FAKULTAS
            |--------------------------------------------------------------------------
            */
            WarFaculty::where('war_session_id', $war->id)
                ->update(['filled' => 0]);
        });

        return back()->with('success', 'Sesi WAR berhasil direset ke scheduled.');
    }

    /*
    |--------------------------------------------------------------------------
    | LIVE MONITORING PAGE — view
    |--------------------------------------------------------------------------
    */
    public function monitor(WarSession $war)
    {
        $war->load([
            'gelombang',
            'faculties.fakultas',
        ]);

        $war->loadCount('participants');

        /*
        |--------------------------------------------------------------------------
        | DATA KELOMPOK (untuk initial render)
        |--------------------------------------------------------------------------
        */
        $kelompoks = KelompokKkn::with([
                'desaGelombang.desa.kecamatan',
                'dosenPembimbingLapangan.user',
                'pesertaKkn.mahasiswa.prodi.fakultas',
                'kuotaFakultas.fakultas',
            ])
            ->whereHas('desaGelombang', fn ($q) => $q->where('gelombang_id', $war->gelombang_id))
            ->get();

        /*
        |--------------------------------------------------------------------------
        | KUOTA DESA
        |--------------------------------------------------------------------------
        */
        $kuotaDesa = KuotaFakultasDesa::with([
                'desaGelombang.desa',
                'fakultas',
            ])
            ->whereHas('desaGelombang', fn ($q) => $q->where('gelombang_id', $war->gelombang_id))
            ->get();

        return view('war-admin.monitor', compact('war', 'kelompoks', 'kuotaDesa'));
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO CLOSE — dipanggil oleh scheduler
    |--------------------------------------------------------------------------
    | Menutup war session yang sudah melewati end_at.
    | Dipanggil dari: App\Console\Commands\WarAutoClose atau Scheduler.
    */
    public function autoClose(): bool
    {
        $expired = WarSession::where('status', 'active')
            ->where('end_at', '<=', now())
            ->get();

        foreach ($expired as $war) {
            $war->update(['status' => 'closed']);

            logger()->info('WAR auto-closed', [
                'war_id'  => $war->id,
                'name'    => $war->name,
                'end_at'  => $war->end_at,
            ]);
        }

        return true;
    }
}
