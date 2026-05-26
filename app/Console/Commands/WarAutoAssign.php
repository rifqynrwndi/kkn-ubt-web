<?php
namespace App\Console\Commands;

use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\WarSession;
use App\Services\War\WarService;
use Illuminate\Console\Command;

class WarAutoAssign extends Command
{
    protected $signature = 'war:auto-assign {session_id : WAR session ID} {--fakultas= : Specific faculty ID (optional)}';
    protected $description = 'Auto-assign remaining unplaced mahasiswa to random available kelompok when faculty WAR time ends';

    public function handle(WarService $warService)
    {
        $session = WarSession::findOrFail($this->argument('session_id'));
        $fakultasFilter = $this->option('fakultas');
        $gelId = $session->gelombang_id;

        // Get all eligible peserta (approved, no kelompok, in this gelombang)
        $query = PesertaKkn::where('gelombang_id', $gelId)
            ->where('status_pendaftaran', 'approved')
            ->whereNull('kelompok_kkn_id')
            ->with(['mahasiswa.prodi.fakultas']);

        if ($fakultasFilter) {
            $query->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $fakultasFilter));
        }

        $pesertas = $query->get();
        $total = $pesertas->count();

        if ($total === 0) {
            $this->info('Tidak ada mahasiswa yang perlu di-assign.');
            return;
        }

        $this->info("Total mahasiswa yang belum punya kelompok: {$total}");

        $success = 0;
        $fail = 0;
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($pesertas as $peserta) {
            $fakId = $peserta->mahasiswa->prodi->fakultas_id;

            // Pick a random available kelompok
            $kelompok = KelompokKkn::whereHas('desaGelombang', fn($q) => $q->where('gelombang_id', $gelId))
                ->where('status', '!=', 'penuh')
                ->inRandomOrder()
                ->first();

            if (!$kelompok) {
                $this->warn("\nTidak ada kelompok tersedia. Berhenti.");
                $fail += ($total - $success - $fail);
                break;
            }

            try {
                $result = $warService->joinKelompok($session, $kelompok->id, $peserta->mahasiswa_id);
                if ($result['success']) {
                    $success++;
                } else {
                    $fail++;
                }
            } catch (\Throwable $e) {
                $fail++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Auto-assign selesai!");
        $this->info("Berhasil: {$success}");
        $this->info("Gagal: {$fail}");
    }
}
