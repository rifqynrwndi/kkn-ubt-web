<?php

namespace App\Services\War;

use Illuminate\Support\Facades\Cache;

class WarLockService
{
    private const USER_LOCK_TTL = 10;

    private const SESSION_LOCK_TTL = 30;

    public function acquireUserLock(int $warSessionId, int $pesertaKknId): bool
    {
        $lock = Cache::lock("war:user:{$warSessionId}:{$pesertaKknId}", self::USER_LOCK_TTL);

        return $lock->block(0, fn () => true);
    }

    public function releaseUserLock(int $warSessionId, int $pesertaKknId): void
    {
        $lock = Cache::lock("war:user:{$warSessionId}:{$pesertaKknId}", self::USER_LOCK_TTL);
        $lock->release();
    }

    public function acquireSessionLock(int $warSessionId): bool
    {
        $lock = Cache::lock("war:session:{$warSessionId}", self::SESSION_LOCK_TTL);

        return $lock->block(0, fn () => true);
    }

    public function releaseSessionLock(int $warSessionId): void
    {
        $lock = Cache::lock("war:session:{$warSessionId}", self::SESSION_LOCK_TTL);
        $lock->release();
    }
}
