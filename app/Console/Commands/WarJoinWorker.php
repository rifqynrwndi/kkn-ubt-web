<?php

namespace App\Console\Commands;

use App\Models\KelompokKkn;
use App\Models\WarSession;
use App\Services\War\WarService;
use Illuminate\Console\Command;

class WarJoinWorker extends Command
{
    protected $signature = 'war:join-worker {session_id} {kelompok_id} {mahasiswa_id}';
    protected $description = 'Worker background untuk simulasi join WAR';

    public function handle(WarService $warService)
    {
        $sessionId = $this->argument('session_id');
        $kelompokId = $this->argument('kelompok_id');
        $mahasiswaId = $this->argument('mahasiswa_id');

        try {
            $session = WarSession::findOrFail($sessionId);
            
            $result = $warService->joinKelompok($session, $kelompokId, $mahasiswaId);

            if ($result['success']) {
                echo "SUCCESS";
                return 0;
            } else {
                echo "FAILED: " . ($result['message'] ?? 'Unknown');
                return 1;
            }

        } catch (\Throwable $e) {
            echo "ERROR: " . $e->getMessage();
            return 1;
        }
    }
}
