<?php

namespace App\Console\Commands;

use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\WarSession;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process as SymfonyProcess;

class WarStressTest extends Command
{
    protected $signature = 'war:stress-test
                            {session_id : WAR session ID}
                            {--users=100 : Jumlah user simulasi}
                            {--kelompoks=5 : Jumlah kelompok target}
                            {--cleanup : Reset peserta & kelompok sebelum test}
                            {--timeout=300 : Process timeout per worker (detik)}';
    protected $description = 'Simulasi banyak user mengambil banyak kelompok secara bersamaan untuk menguji Concurrency/Locking berskala besar.';

    public function handle()
    {
        $sessionId = $this->argument('session_id');
        $userCount = (int) $this->option('users');
        $kelompokCount = (int) $this->option('kelompoks');
        $timeout = (int) $this->option('timeout');

        $session = WarSession::findOrFail($sessionId);

        if ($this->option('cleanup')) {
            $this->info('Cleaning up...');
            \App\Models\WarParticipant::where('war_session_id', $sessionId)->delete();
            $this->info('  - WarParticipant: cleared');

            PesertaKkn::where('gelombang_id', $session->gelombang_id)
                ->update(['kelompok_kkn_id' => null]);
            $this->info('  - PesertaKkn.kelompok_kkn_id: reset');

            KelompokKkn::whereHas('desaGelombang', fn($q) => $q->where('gelombang_id', $session->gelombang_id))
                ->update(['status' => 'dibuka', 'ketua_peserta_id' => null]);
            $this->info('  - KelompokKkn: reset');

            \App\Models\WarFaculty::where('war_session_id', $sessionId)
                ->update(['filled' => 0, 'quota' => 500]);
            $this->info('  - WarFaculty: reset');

            // Pastikan WAR active
            $session->update([
                'status'   => 'active',
                'start_at' => now(),
                'end_at'   => now()->addHours(4),
            ]);
            $this->info('  - WarSession: active (4 jam)');

            \App\Models\WarFaculty::where('war_session_id', $sessionId)
                ->update([
                    'start_at' => now()->subHour(),
                    'end_at'   => now()->addHours(4),
                ]);
            $this->info('  - WarFaculty schedules: reset');

            $this->newLine();
        }
        
        // Ambil beberapa kelompok secara acak dari gelombang yang sama
        $targetKelompoks = KelompokKkn::whereHas('desaGelombang', fn($q) => $q->where('gelombang_id', $session->gelombang_id))
            ->inRandomOrder()
            ->take($kelompokCount)
            ->get();

        if ($targetKelompoks->isEmpty()) {
            $this->error("Tidak ada kelompok yang ditemukan di gelombang {$session->gelombang_id}");
            return;
        }

        $this->info("=== MEGA STRESS TEST WAR KKN ===");
        $this->info("Sesi     : {$session->name}");
        $this->info("Target   : {$targetKelompoks->count()} Kelompok");
        $this->info("Attackers: {$userCount} User menembak secara bersamaan!");
        $this->newLine();

        if (!$this->option('no-interaction') && !$this->confirm('Lanjutkan eksekusi brutal ini?', true)) {
            return;
        }

        $pesertas = PesertaKkn::where('gelombang_id', $session->gelombang_id)
            ->whereNull('kelompok_kkn_id')
            ->take($userCount)
            ->get();

        if ($pesertas->count() < $userCount) {
            $this->info("Hanya ada {$pesertas->count()} peserta. Membuat " . ($userCount - $pesertas->count()) . " peserta palsu...");

            $prodiIds = \App\Models\ProgramStudi::pluck('id')->toArray();

            for ($i = $pesertas->count(); $i < $userCount; $i++) {
                $ts = time();
                $fakeUser = \App\Models\User::create([
                    'name' => "Bot {$i}",
                    'email' => "bot{$i}_{$ts}@wartest.local",
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);
                $fakeUser->assignRole('mahasiswa');

                $fakeMhs = \App\Models\Mahasiswa::create([
                    'user_id' => $fakeUser->id,
                    'npm' => "BOT" . $ts . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'jenis_kelamin' => rand(1, 10) <= 3 ? 'L' : 'P',
                    'prodi_id' => $prodiIds[array_rand($prodiIds)],
                ]);

                $fakePeserta = PesertaKkn::create([
                    'mahasiswa_id' => $fakeMhs->user_id,
                    'gelombang_id' => $session->gelombang_id,
                    'status_pendaftaran' => 'approved',
                ]);

                $pesertas->push($fakePeserta);
            }
        }

        $this->info("Ditemukan {$pesertas->count()} peserta. Memulai MEGA SERANGAN...");

        $processes = [];
        $php = PHP_BINARY;
        $artisan = base_path('artisan');

        foreach ($pesertas as $peserta) {
            // Pilih satu kelompok acak dari target kelompok untuk bot ini
            $targetKelompok = $targetKelompoks->random();

            $process = new SymfonyProcess([$php, $artisan, 'war:join-worker', $session->id, $targetKelompok->id, $peserta->mahasiswa_id]);
            $process->setTimeout($timeout);
            $process->start();
            $processes[] = [
                'process' => $process,
                'peserta' => $peserta->mahasiswa_id,
                'kelompok_id' => $targetKelompok->id,
                'kelompok_nama' => $targetKelompok->nama_kelompok
            ];
        }

        $this->info('Menunggu semua proses selesai... (ini mungkin butuh beberapa detik)');

        $successCount = 0;
        $failCount = 0;
        $failsByReason = [];

        foreach ($processes as $p) {
            $process = $p['process'];
            $process->wait();

            $output = trim($process->getOutput());
            $error = trim($process->getErrorOutput());

            if ($process->isSuccessful()) {
                $this->line("<info>✓ [User {$p['peserta']} -> {$p['kelompok_nama']}]</info> {$output}");
                $successCount++;
            } else {
                $this->line("<error>✗ [User {$p['peserta']} -> {$p['kelompok_nama']}]</error> {$output}");
                $failCount++;
                
                // Kumpulkan alasan kegagalan
                $reason = str_replace('FAILED: ', '', $output);
                $failsByReason[$reason] = ($failsByReason[$reason] ?? 0) + 1;
            }
        }

        $this->newLine();
        $this->info("=== HASIL AKHIR MEGA STRESS TEST ===");
        $this->info("Total Tembakan : " . count($processes));
        $this->info("Berhasil       : {$successCount}");
        $this->error("Gagal          : {$failCount}");

        if ($failCount > 0) {
            $this->warn("\nRincian Kegagalan:");
            foreach ($failsByReason as $reason => $count) {
                $this->line("- {$count}x : {$reason}");
            }
        }

        $this->newLine();
        $this->info("=== STATUS KELOMPOK TARGET ===");
        foreach ($targetKelompoks as $tk) {
            $terisi = $tk->fresh()->pesertaKkn()->count();
            $this->line("Kelompok {$tk->nama_kelompok} : <comment>{$terisi} / {$tk->kuota}</comment> terisi.");
            if ($terisi > $tk->kuota) {
                $this->error("  -> [BAHAYA] OVERQUOTA DETECTED!");
            }
        }
    }
}
