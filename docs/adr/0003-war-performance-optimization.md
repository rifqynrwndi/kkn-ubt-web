# ADR 0003: WAR Performance Optimization

## Status

Accepted

## Context

WAR sessions with 500+ concurrent users cause 502 errors. Root causes:

1. N+1 query in `KelompokKkn::getTerisiAttribute()` — fires COUNT even when eager-loaded
2. Cache keys `war:arena:{id}` and `war:kelompok:{id}` are invalidated but never populated
3. `kelompokList()` AJAX endpoint has no throttle middleware
4. Cache backend is MySQL (same DB as data queries)

## Decision

### Fix 1: N+1 Accessor

Replace `$this->pesertaKkn()->count()` with `$this->pesertaKkn->count()` in `getTerisiAttribute()`. Uses eager-loaded collection instead of firing new query.

### Fix 2: Populate Read Cache

```php
Cache::remember("war:kelompok:{$session->id}", 2, function () use ($session) {
    return KelompokKkn::where('desa_gelombang_id', $session->desa_gelombang_id)
        ->with([...])
        ->get();
});
```

TTL: 2-3 seconds. Invalidated on join/leave in `WarAllocationService`.

### Fix 3: Throttle AJAX

Add `throttle:30,1` to `kelompokList()` and `status()` routes.

### Fix 4: Redis Cache

Set `CACHE_STORE=redis` in `.env`. Cache locks and read cache use Redis. Sessions can stay on database.

## Consequences

- `arena()` queries drop from ~79 to < 8
- `kelompokList()` queries drop from ~102 to < 3
- 500 concurrent polls: ~17,000 queries/sec → < 500 queries/sec
- Redis required on VPS (already defined in `config/database.php`)
