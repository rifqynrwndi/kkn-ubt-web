# ADR 0001: Unify WAR Join Validation Through WarRuleService

## Status

Accepted

## Context

Join validation is duplicated in 3 places:

1. `WarController::arena()` (lines 159-178) — inline checks
2. `WarController::kelompokList()` (lines 331-353) — inline checks
3. `PendaftaranKknController::ambilKelompok()` (lines 233-314) — inline checks, omits prodi limit

`WarRuleService` already implements all 4 rules (`checkKelompokFull`, `checkGenderQuota`, `checkFakultasPerKelompok`, `checkProdiQuota`) but controllers ignore it. `PendaftaranKknController` doesn't check prodi limits at all — likely a bug.

The display context needs a boolean (can/can't join). The allocation context needs violation strings + row locking. Both use the same rules.

## Decision

Add `checkCanJoin(KelompokKkn $kelompok, PesertaKkn $peserta, ?KelompokKuota $kuota = null, bool $checkProdi = true): bool` to `WarRuleService`.

- Internally loads members from `$kelompok->pesertaKkn` (eager-loaded)
- Loads `$kuota` from `$kelompok->kuotaFakultas` if not provided
- Calls the same 4 rule methods used by `checkAllRules()`
- Also checks `$kelompok->status !== 'penuh'`
- Returns `true` if all pass, `false` otherwise

`WarAllocationService` continues using `checkAllRules()` for violation strings inside the transaction. `WarController` and `PendaftaranKknController` use `checkCanJoin()` for display.

The `checkProdi` flag lets `PendaftaranKknController` skip prodi checks (matching current behavior) while `WarController` includes them.

## Consequences

- Single source of truth for join rules
- `PendaftaranKknController` gains prodi limit check when `$checkProdi = true` (future option)
- No TOCTOU gap — display and allocation use the same rule methods
- Easy to add new rules later (add to `checkAllRules()` + `checkCanJoin()`)
