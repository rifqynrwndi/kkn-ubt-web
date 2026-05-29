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
    protected $description = 'Auto-assign unplaced mahasiswa per faculty when their WAR jadwal has ended';

    public function handle(WarService $warService)
    {
        $expiredFaculties = WarFaculty::whereHas('warSession', fn($q) => $q->where('status', 'active'))
            ->where('end_at', '<=', now())
            ->with(['warSession', 'fakultas'])
            ->get();

        if ($expiredFaculties->isEmpty()) {
            $this->info('Tidak ada fakultas dengan jadwal WAR yang sudah habis.');
            return;
        }

        $processedSessionIds = [];

        foreach ($expiredFaculties as $wf) {
            $session = $wf->warSession;
            $fakultasId = $wf->fakultas_id;
            $gelId = $session->gelombang_id;

            $namaFak = $wf->fakultas?->nama_fakultas ?? $fakultasId;
            $this->line("Fakultas: {$namaFak}, Sesi: {$session->name}");

            $pesertas = PesertaKkn::where('gelombang_id', $gelId)
                ->where('status_pendaftaran', 'approved')
                ->whereNull('kelompok_kkn_id')
                ->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $fakultasId))
                ->with(['mahasiswa.prodi.fakultas'])
                ->get();

            if ($pesertas->isEmpty()) {
                $this->line("  Tidak ada mahasiswa yang perlu di-assign.");
            } else {
                $this->info("  Mahasiswa perlu assign: {$pesertas->count()}");

                $success = 0;
                foreach ($pesertas as $peserta) {
                    $kelompok = KelompokKkn::whereHas('desaGelombang', fn($q) => $q->where('gelombang_id', $gelId))
                        ->where('status', '!=', 'penuh')
                        ->inRandomOrder()
                        ->first();

                    if (!$kelompok) {
                        $this->warn("  Tidak ada kelompok tersedia.");
                        break;
                    }

                    try {
                        $result = $warService->joinKelompok($session, $kelompok->id, $peserta->mahasiswa_id);
                        if ($result['success']) $success++;
                    } catch (\Throwable) {}
                }

                $this->info("  Berhasil di-assign: {$success}");
            }

            $processedSessionIds[] = $session->id;
        }

        // Close sessions where all faculties have expired
        foreach (array_unique($processedSessionIds) as $sessionId) {
            $remaining = WarFaculty::where('war_session_id', $sessionId)
                ->where('end_at', '>', now())
                ->exists();

            if (! $remaining) {
                WarSession::where('id', $sessionId)->update(['status' => 'closed']);
                $this->info("Sesi #{$sessionId} ditutup (semua jadwal fakultas sudah habis).");
            }
        }

        $this->newLine();
        $this->info('Proses expired faculties selesai.');
    }
}
