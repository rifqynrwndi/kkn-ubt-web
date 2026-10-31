<?php

namespace App\Services\War;

use Illuminate\Support\Facades\Lock;

class WarLockService
{
    private const USER_LOCK_TTL = 10;

    private const SESSION_LOCK_TTL = 30;

    public function acquireUserLock(int $warSessionId, int $pesertaKknId): bool
    {
        return Lock::store('redis')->block(
            "war:user:{$warSessionId}:{$pesertaKknId}",
            self::USER_LOCK_TTL,
            fn () => true,
        );
    }

    public function releaseUserLock(int $warSessionId, int $pesertaKknId): void
    {
        Lock::store('redis')->release("war:user:{$warSessionId}:{$pesertaKknId}");
    }

    public function acquireSessionLock(int $warSessionId): bool
    {
        return Lock::store('redis')->block(
            "war:session:{$warSessionId}",
            self::SESSION_LOCK_TTL,
            fn () => true,
        );
    }

    public function releaseSessionLock(int $warSessionId): void
    {
        Lock::store('redis')->release("war:session:{$warSessionId}");
    }
}
