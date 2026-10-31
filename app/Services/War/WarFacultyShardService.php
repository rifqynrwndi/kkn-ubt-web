<?php

namespace App\Services\War;

use Illuminate\Support\Facades\DB;

class WarFacultyShardService
{
    private const SHARD_COUNT = 10;

    public function increment(int $warSessionId, int $fakultasId, int $pesertaKknId): void
    {
        $shardIndex = $this->resolveShardIndex($pesertaKknId);

        DB::table('war_faculty_shards')
            ->where('war_session_id', $warSessionId)
            ->where('fakultas_id', $fakultasId)
            ->where('shard_index', $shardIndex)
            ->increment('filled');
    }

    public function getFilled(int $warSessionId, int $fakultasId): int
    {
        return DB::table('war_faculty_shards')
            ->where('war_session_id', $warSessionId)
            ->where('fakultas_id', $fakultasId)
            ->sum('filled');
    }

    public function aggregate(int $warSessionId): void
    {
        $aggregates = DB::table('war_faculty_shards')
            ->where('war_session_id', $warSessionId)
            ->select('fakultas_id', DB::raw('SUM(filled) as total_filled'))
            ->groupBy('fakultas_id')
            ->get();

        foreach ($aggregates as $agg) {
            DB::table('war_faculties')
                ->where('war_session_id', $warSessionId)
                ->where('fakultas_id', $agg->fakultas_id)
                ->update(['filled' => $agg->total_filled]);
        }
    }

    public function ensureShardsExist(int $warSessionId, int $fakultasId): void
    {
        $existing = DB::table('war_faculty_shards')
            ->where('war_session_id', $warSessionId)
            ->where('fakultas_id', $fakultasId)
            ->pluck('shard_index')
            ->toArray();

        $missing = array_diff(range(0, self::SHARD_COUNT - 1), $existing);

        if (! empty($missing)) {
            $rows = array_map(
                fn ($i) => [
                    'war_session_id' => $warSessionId,
                    'fakultas_id' => $fakultasId,
                    'shard_index' => $i,
                    'filled' => 0,
                ],
                $missing
            );

            DB::table('war_faculty_shards')->insert($rows);
        }
    }

    public function reset(int $warSessionId): void
    {
        DB::table('war_faculty_shards')
            ->where('war_session_id', $warSessionId)
            ->update(['filled' => 0]);
    }

    private function resolveShardIndex(int $pesertaKknId): int
    {
        return $pesertaKknId % self::SHARD_COUNT;
    }
}
