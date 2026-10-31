<?php

namespace App\Services\War;

use App\Models\KelompokKkn;
use App\Models\KelompokKuota;
use App\Models\PesertaKkn;
use App\Models\WarFaculty;
use App\Models\WarLog;
use App\Models\WarParticipant;
use App\Models\WarSession;
use App\Support\WarCacheKeys;
use Illuminate\Contracts\Cache\TaggableStore;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WarAllocationService
{
    public function __construct(
        private readonly WarRuleService $ruleService,
        private readonly WarLockService $lockService,
        private readonly WarFacultyShardService $shardService,
    ) {}

    public function allocate(WarSession $session, PesertaKkn $peserta, KelompokKkn $kelompok, string $clientIp): WarParticipant
    {
        if (! $this->lockService->acquireUserLock($session->id, $peserta->id)) {
            throw new \RuntimeException('Request kamu sedang diproses. Mohon tunggu sebentar.');
        }

        try {
            $maxRetries = 3;
            $attempt = 0;

            while ($attempt < $maxRetries) {
                try {
                    return $this->executeTransaction($session, $peserta, $kelompok, $clientIp);
                } catch (QueryException $e) {
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

    private function executeTransaction(WarSession $session, PesertaKkn $peserta, KelompokKkn $kelompok, string $clientIp): WarParticipant
    {
        return DB::transaction(function () use ($session, $peserta, $kelompok, $clientIp) {
            $fakultasId = $peserta->mahasiswa->prodi->fakultas_id;

            // Ensure shards exist for this fakultas
            $this->shardService->ensureShardsExist($session->id, $fakultasId);

            // Lock & re-check WarFaculty timing inside the transaction (authoritative check)
            $warFacultyLocked = WarFaculty::where('war_session_id', $session->id)
                ->where('fakultas_id', $fakultasId)
                ->lockForUpdate()
                ->firstOrFail();

            if (now()->gt($warFacultyLocked->end_at)) {
                throw new \RuntimeException('Giliran WAR untuk fakultas kamu sudah berakhir.');
            }

            // Use firstOrCreate (atomic with unique constraint) instead of create + firstOrFail
            $kelompokKuota = KelompokKuota::firstOrCreate(
                [
                    'kelompok_kkn_id' => $kelompok->id,
                    'fakultas_id' => $fakultasId,
                ],
                [
                    'kuota' => $kelompok->kuota,
                    'kuota_laki' => 0,
                    'kuota_perempuan' => 0,
                ]
            );

            // Load current members with lock (for rule checks)
            $currentMembers = PesertaKkn::where('kelompok_kkn_id', $kelompok->id)
                ->with(['mahasiswa.prodi'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->toArray();

            $violations = $this->ruleService->checkAllRules(
                $kelompok,
                $peserta,
                $currentMembers,
                $kelompokKuota,
            );

            if (! empty($violations)) {
                throw new \RuntimeException($violations[0]);
            }

            return $this->persistAllocation(
                $session,
                $peserta,
                $kelompok,
                count($currentMembers),
                $clientIp,
                $fakultasId,
            );
        }, 10);
    }

    private function persistAllocation(
        WarSession $session,
        PesertaKkn $peserta,
        KelompokKkn $kelompok,
        int $membersBefore,
        string $clientIp,
        int $fakultasId,
    ): WarParticipant {
        // Validate before persisting
        $this->assertPesertaBelumPunyaKelompok($peserta);
        $this->assertBelumJoinSession($session, $peserta);

        $peserta->update(['kelompok_kkn_id' => $kelompok->id]);

        $kelompok->generateKetua();

        $participant = WarParticipant::create([
            'war_session_id' => $session->id,
            'peserta_kkn_id' => $peserta->id,
            'kelompok_kkn_id' => $kelompok->id,
            'status' => 'joined',
            'joined_at' => now(),
        ]);

        $newMemberCount = $membersBefore + 1;

        if ($newMemberCount >= WarRuleService::MAX_KELOMPOK_SIZE) {
            $kelompok->update(['status' => 'penuh']);
        }

        // Increment sharded counter (async aggregation updates WarFaculty.filled)
        $this->shardService->increment($session->id, $fakultasId, $peserta->id);

        WarLog::create([
            'war_session_id' => $session->id,
            'peserta_kkn_id' => $peserta->id,
            'action' => 'join_success',
            'meta' => json_encode([
                'kelompok_id' => $kelompok->id,
                'kelompok_nama' => $kelompok->nama_kelompok,
                'member_count' => $newMemberCount,
                'ip' => $clientIp,
            ]),
        ]);

        // Invalidate cache via tags (if supported) or keys
        $store = Cache::getStore();
        if ($store instanceof TaggableStore && config('war.cache_tags_enabled', true)) {
            Cache::tags(WarCacheKeys::tag($session->id))->flush();
        } else {
            Cache::forget(WarCacheKeys::arena($session->id));
            Cache::forget(WarCacheKeys::kelompok($session->id));
            Cache::forget(WarCacheKeys::list($session->id));
        }

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
