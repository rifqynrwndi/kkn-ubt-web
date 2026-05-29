<?php

namespace App\Services\War;

use App\Models\KelompokKkn;
use App\Models\KelompokKuota;
use App\Models\PesertaKkn;
use App\Models\WarFaculty;
use App\Models\WarLog;
use App\Models\WarParticipant;
use App\Models\WarSession;
use Illuminate\Support\Facades\DB;

class WarAllocationService
{
    public function __construct(
        private readonly WarRuleService $ruleService,
        private readonly WarLockService $lockService,
    ) {}

    public function allocate(WarSession $session, PesertaKkn $peserta, KelompokKkn $kelompok): WarParticipant
    {
        if (! $this->lockService->acquireUserLock($session->id, $peserta->id)) {
            throw new \RuntimeException('Request kamu sedang diproses. Mohon tunggu sebentar.');
        }

        try {
            $maxRetries = 3;
            $attempt = 0;

            while ($attempt < $maxRetries) {
                try {
                    return $this->executeTransaction($session, $peserta, $kelompok);
                } catch (\Illuminate\Database\QueryException $e) {
                    $attempt++;
                    if ($attempt >= $maxRetries || ! str_contains($e->getMessage(), 'Deadlock')) {
                        throw $e;
                    }
                    usleep(rand(10000, 50000)); // 10-50ms delay before retry
                }
            }
        } finally {
            $this->lockService->releaseUserLock($session->id, $peserta->id);
        }
    }

    private function executeTransaction(WarSession $session, PesertaKkn $peserta, KelompokKkn $kelompok): WarParticipant
    {
        return DB::transaction(function () use ($session, $peserta, $kelompok) {
            $kelompokLocked = KelompokKkn::lockForUpdate()->findOrFail($kelompok->id);

            $pesertaLocked = PesertaKkn::lockForUpdate()
                ->with(['mahasiswa.prodi.fakultas'])
                ->findOrFail($peserta->id);

            $this->assertPesertaBelumPunyaKelompok($pesertaLocked);
            $this->assertBelumJoinSession($session, $pesertaLocked);

            $fakultasId = $pesertaLocked->mahasiswa->prodi->fakultas_id;

            // Lock & re-check WarFaculty timing inside the transaction (authoritative check)
            $warFacultyLocked = WarFaculty::where('war_session_id', $session->id)
                ->where('fakultas_id', $fakultasId)
                ->lockForUpdate()
                ->firstOrFail();

            if (now()->gt($warFacultyLocked->end_at)) {
                throw new \RuntimeException('Giliran WAR untuk fakultas kamu sudah berakhir.');
            }

            $kelompokKuota = KelompokKuota::where('kelompok_kkn_id', $kelompokLocked->id)
                ->where('fakultas_id', $fakultasId)
                ->orderBy('id')
                ->lockForUpdate()
                ->firstOrFail();

            $currentMembers = PesertaKkn::where('kelompok_kkn_id', $kelompokLocked->id)
                ->with(['mahasiswa.prodi'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->toArray();

            $violations = $this->ruleService->checkAllRules(
                $kelompokLocked,
                $pesertaLocked,
                $currentMembers,
                $kelompokKuota,
            );

            if (! empty($violations)) {
                throw new \RuntimeException($violations[0]);
            }

            return $this->persistAllocation(
                $session,
                $pesertaLocked,
                $kelompokLocked,
                count($currentMembers),
            );
        }, 10);
    }

    private function persistAllocation(
        WarSession  $session,
        PesertaKkn  $peserta,
        KelompokKkn $kelompok,
        int         $membersBefore,
    ): WarParticipant {
        $peserta->update(['kelompok_kkn_id' => $kelompok->id]);

        $kelompok->generateKetua();

        $participant = WarParticipant::create([
            'war_session_id'  => $session->id,
            'peserta_kkn_id'  => $peserta->id,
            'kelompok_kkn_id' => $kelompok->id,
            'status'          => 'joined',
            'joined_at'       => now(),
        ]);

        if (($membersBefore + 1) >= WarRuleService::MAX_KELOMPOK_SIZE) {
            $kelompok->update(['status' => 'penuh']);
        }

        WarFaculty::where('war_session_id', $session->id)
            ->where('fakultas_id', $peserta->mahasiswa->prodi->fakultas_id)
            ->increment('filled');

        WarLog::create([
            'war_session_id' => $session->id,
            'peserta_kkn_id' => $peserta->id,
            'action'         => 'join_success',
            'meta'           => json_encode([
                'kelompok_id'   => $kelompok->id,
                'kelompok_nama' => $kelompok->nama_kelompok,
                'member_count'  => $membersBefore + 1,
                'ip'            => request()->ip(),
            ]),
        ]);

        return $participant;
    }

    private function assertPesertaBelumPunyaKelompok(PesertaKkn $peserta): void
    {
        if ($peserta->kelompok_kkn_id !== null) {
            throw new \RuntimeException('Kamu sudah terdaftar di kelompok lain.');
        }
    }

    private function assertBelumJoinSession(WarSession $session, PesertaKkn $peserta): void
    {
        $exists = WarParticipant::where('war_session_id', $session->id)
            ->where('peserta_kkn_id', $peserta->id)
            ->lockForUpdate()
            ->exists();

        if ($exists) {
            throw new \RuntimeException('Kamu sudah join di sesi WAR ini.');
        }
    }

}
