<?php

namespace App\Services\War;

use Illuminate\Support\Facades\Cache;

class WarLockService
{
    private const LOCK_TTL = 10;
    private const PREFIX   = 'war_lock:';

    public function acquireUserLock(int $warSessionId, int $pesertaKknId): bool
    {
        return Cache::add(self::PREFIX . "user:{$warSessionId}:{$pesertaKknId}", 1, self::LOCK_TTL);
    }

    public function releaseUserLock(int $warSessionId, int $pesertaKknId): void
    {
        Cache::forget(self::PREFIX . "user:{$warSessionId}:{$pesertaKknId}");
    }
}
