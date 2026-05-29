<?php
namespace App\Console\Commands;

use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\WarFaculty;
use App\Models\WarSession;
use App\Services\War\WarService;
use Illuminate\Console\Command;

class WarProcessExpiredFaculties extends Command
{
    protected $signature = 'war:process-expired-faculties';
    protected $description = 'Tunggu semua fakultas selesai WAR, baru assign sisa peserta ke kelompok random';

    public function handle(WarService $warService)
    {
        // Cari active session yang SEMUA fakultasnya sudah expired
        $sessions = WarSession::where('status', 'active')
            ->whereHas('faculties')
            ->whereDoesntHave('faculties', fn($q) => $q->where('end_at', '>', now()))
            ->with(['gelombang', 'faculties'])
            ->get();

        if ($sessions->isEmpty()) {
            $this->info('Belum ada session WAR yang semua fakultasnya selesai.');
            return;
        }

        foreach ($sessions as $session) {
            $gelId = $session->gelombang_id;
            $this->line("Sesi: {$session->name}");

            // Ambil SEMUA peserta approved yang belum punya kelompok (semua fakultas)
            $pesertas = PesertaKkn::where('gelombang_id', $gelId)
                ->where('status_pendaftaran', 'approved')
                ->whereNull('kelompok_kkn_id')
                ->with(['mahasiswa.prodi.fakultas'])
                ->get();

            if ($pesertas->isEmpty()) {
                $this->line("  Tidak ada mahasiswa yang perlu di-assign.");
                $session->update(['status' => 'closed']);
                $this->info("  Sesi ditutup.");
                continue;
            }

            $total = $pesertas->count();
            $this->info("  Mahasiswa perlu assign (semua fakultas): {$total}");

            // Round-robin per fakultas agar komposisi kelompok beragam
            $byFakultas = $pesertas->groupBy(fn($p) => $p->mahasiswa->prodi->fakultas_id);
            $fakultasNames = $byFakultas->map(fn($g, $fid) => $g->first()->mahasiswa->prodi->fakultas->nama_fakultas ?? "Fakultas #{$fid}");

            $success = 0;

            // Loop round-robin sampai semua fakultas habis
            $hasRemaining = true;
            while ($hasRemaining) {
                $hasRemaining = false;

                foreach ($byFakultas as $fakId => $group) {
                    if ($group->isEmpty()) continue;

                    $hasRemaining = true;
                    $peserta = $group->shift();

                    $kelompok = KelompokKkn::whereHas('desaGelombang', fn($q) => $q->where('gelombang_id', $gelId))
                        ->where('status', '!=', 'penuh')
                        ->withCount('pesertaKkn')
                        ->orderBy('peserta_kkn_count')
                        ->first();

                    if (!$kelompok) {
                        $this->warn("  Kelompok habis. Assign terhenti ({$success} berhasil).");
                        break 2;
                    }

                    try {
                        $result = $warService->joinKelompok($session, $kelompok->id, $peserta->mahasiswa_id);
                        if ($result['success']) $success++;
                    } catch (\Throwable) {}
                }
            }

            $session->update(['status' => 'closed']);
            $this->info("  Assign selesai: {$success}/{$total} berhasil. Sesi ditutup.");
        }

        $this->newLine();
        $this->info('Proses expired faculties selesai.');
    }
}
