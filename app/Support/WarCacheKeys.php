<?php

namespace App\Support;

class WarCacheKeys
{
    public static function arena(int $sessionId): string
    {
        return "war:arena:{$sessionId}";
    }

    public static function kelompok(int $sessionId): string
    {
        return "war:kelompok:{$sessionId}";
    }

    public static function list(int $sessionId): string
    {
        return "war:list:{$sessionId}";
    }

    public static function stats(int $sessionId): string
    {
        return "war:stats:{$sessionId}";
    }

    public static function tag(int $sessionId): string
    {
        return "war:{$sessionId}";
    }

    public static function ttl(): int
    {
        return config('war.cache_ttl', 300);
    }
}
