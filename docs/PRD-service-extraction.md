# PRD: WAR Service Extraction + Performance Optimization

## Problem Statement

The KKN management system has three critical issues:

1. **Duplicated join validation** — Three controllers (`WarController::arena()`, `WarController::kelompokList()`, `PendaftaranKknController::ambilKelompok()`) each implement their own join validation logic, ignoring the existing `WarRuleService`. `PendaftaranKknController` omits prodi limits entirely. This creates maintenance risk and inconsistent behavior.

2. **684-line God Controller** — `KelompokKknController` handles CRUD, scoring, export, template seeding, and membership management. It contains dead code (`calcScore()` with wrong weights), duplicated export logic (93 lines repeated across 3 controllers), and hardcoded tugas templates.

3. **WAR system crashes under load** — 500+ concurrent users cause 502 errors due to N+1 queries (~79 per page load), no read caching, unlimited AJAX polling (~17,000 queries/sec), and MySQL as cache backend.

4. **WAR scheduling disadvantaging large faculties** — Last time, small faculties were scheduled first with 30-minute slots. FKIP (500+ students) got the same 30 minutes, causing many students to miss out. Faculty slot duration should reflect student count.

## Solution

Unify join validation through `WarRuleService`, extract three service classes from `KelompokKknController`, and optimize WAR performance with caching, throttling, and Redis.

## User Stories

1. As a **mahasiswa**, I want to join a kelompok during WAR without seeing a 502 error, so that I can participate in KKN.
2. As a **mahasiswa**, I want to see accurate availability status on all kelompok cards, so that I can make informed join decisions.
3. As a **mahasiswa**, I want the prodi limit check to apply consistently whether I join via WAR or via `PendaftaranKknController`, so that the rules are fair.
4. As a **superadmin**, I want to export kelompok data to Excel without duplicate code, so that I can generate reports quickly.
5. As a **superadmin**, I want to export tugas data to Excel with student details, so that I can track task completion.
6. As a **superadmin**, I want to export mahasiswa data to Excel, so that I can manage enrollment.
7. As a **DPL**, I want to see accurate scores calculated from the correct 40/30/30 weights, so that grading is consistent.
8. As a **DPL**, I want to remove a student from my kelompok without breaking the ketua assignment, so that membership management is smooth.
9. As a **developer**, I want a single source of truth for join validation rules, so that adding new rules doesn't require editing 3 controllers.
10. As a **developer**, I want services extracted into testable units, so that I can write unit tests without HTTP overhead.
11. As a **developer**, I want WAR page loads under 8 queries, so that the system handles 1000 concurrent users.
12. As a **developer**, I want AJAX polling throttled to 30 req/min, so that the database isn't overwhelmed.
13. As a **developer**, I want Redis-backed caching with 2-3s TTL, so that read operations are fast and cache invalidation is atomic.
14. As a **developer**, I want dead code (`calcScore()`) removed, so that the codebase is clean.
15. As a **developer**, I want tugas template seeding extracted to a service, so that the 7 templates are defined in one place.
16. As a **developer**, I want membership removal (hapusAnggota) in a service with proper transaction handling, so that ketua reassignment is consistent.
17. As a **developer**, I want `checkCanJoin()` to accept an optional `KelompokKuota` parameter, so that `PendaftaranKknController` can pass pre-computed values without changing its data flow.
18. As a **developer**, I want `checkCanJoin()` to have a configurable `$checkProdi` flag, so that non-WAR joins can skip prodi limits if needed.
19. As a **developer**, I want the same rule methods used by both display (`checkCanJoin`) and allocation (`checkAllRules`), so that there's no TOCTOU gap.
20. As a **developer**, I want unit tests for all three new services, so that regressions are caught early.
21. As a **developer**, I want feature tests for controllers to remain passing, so that integration behavior is preserved.
22. As a **superadmin**, I want to verify a mahasiswa's email from the admin panel, so that students without email access can still use the system.
23. As a **mahasiswa**, I want my email verification status shown clearly, so that I know if I need to verify.
24. As a **developer**, I want the PR for email verification merged to main, so that the feature is deployed.
25. As a **developer**, I want Redis installed and configured on the VPS, so that cache operations don't compete with MySQL.
26. As a **developer**, I want `CACHE_STORE=redis` in `.env`, so that Laravel uses Redis for caching.
27. As a **developer**, I want cache locks and read cache to use Redis, so that join operations and page loads are fast.
28. As a **mahasiswa**, I want FKIP to have a longer WAR slot (1-1.5 hours) since there are 500+ students, so that I have a fair chance to join a kelompok.
29. As a **mahasiswa**, I want small faculties (FKB, FISIP, etc.) to have shorter slots (30 min) since they have fewer students, so that the schedule is efficient.
30. As a **superadmin**, I want to configure faculty slot durations based on student count, so that the WAR schedule is fair.
31. As a **mahasiswa**, I want small faculties scheduled first as a warm-up, so that the system stabilizes before peak load.

## Implementation Decisions

### D1: Unified Join Validation (ADR-0001)

- Add `checkCanJoin(KelompokKkn, PesertaKkn, ?KelompokKuota, bool $checkProdi): bool` to `WarRuleService`
- Internally loads members from `$kelompok->pesertaKkn` (eager-loaded)
- Loads `$kuota` from `$kelompok->kuotaFakultas` if not provided
- Calls same 4 rule methods as `checkAllRules()`
- Also checks `$kelompok->status !== 'penuh'`
- `WarAllocationService` continues using `checkAllRules()` for violation strings inside transaction
- Display controllers use `checkCanJoin()` for boolean result
- `PendaftaranKknController` passes `$checkProdi = false` to match current behavior

### D2: ExportService (ADR-0002)

- Location: `app/Services/ExportService.php`
- Methods: `exportKelompokXlsx($kelompoks)`, `exportTugasXlsx($tugas)`, `exportMahasiswaXlsx($mahasiswa)`
- Private helpers: `applyHeaderStyle()`, `applyRowStyle()`, `getFacultyColor()`
- Replaces 93 lines of duplicated export logic in 3 controllers
- Uses `Maatwebsite\Excel` (already in composer.json)

### D3: ScoreService (ADR-0002)

- Location: `app/Services/ScoreService.php`
- Methods: `calculateDplScore($penilaian)`, `calculateLppmScore($penilaian)`, `calculateTotal($dplScore, $lppmScore)`
- Fixed weights: DPL 60% (profil_desa + pengamatan + laporan), LPPM 40% (laporan + presentasi + publikasi)
- Delete dead `calcScore()` from `KelompokKknModel`

### D4: KelompokService (ADR-0002)

- Location: `app/Services/KelompokService.php`
- Methods: `seedTugasTemplates(KelompokKkn)`, `removeAnggota(KelompokKkn, PesertaKkn)`, `checkAndMarkFull(KelompokKkn)`
- `seedTugasTemplates()` replaces hardcoded 7 templates in `store()`
- `removeAnggota()` wraps transaction + ketua reassignment (currently inline in controller)
- `checkAndMarkFull()` checks capacity and updates status to 'penuh'

### D5: WAR Performance (ADR-0003)

- Fix N+1 accessor: `$this->pesertaKkn->count()` instead of `$this->pesertaKkn()->count()`
- Populate read cache: `Cache::remember("war:kelompok:{$session->id}", 2, fn() => ...)` with 2-3s TTL
- Invalidate cache on join/leave in `WarAllocationService`
- Throttle `kelompokList()` and `status()` routes: `throttle:30,1`
- Switch cache backend to Redis: `CACHE_STORE=redis` in `.env`

### D7: Redis Installation (ADR-0003)

- Install Redis on Ubuntu VPS: `apt install redis-server && systemctl enable redis-server`
- Configure Laravel: `CACHE_STORE=redis` in `.env`
- Redis connection already defined in `config/database.php` (lines 157-183)
- Cache operations (locks + read cache) move from MySQL to Redis
- Session driver can stay on database (low impact)

### D8: WAR Session Scheduling

- Faculty slot duration proportional to student count
- Recommended order: small faculties first (30 min each), then medium (45 min), then FEB (1 hour), then FKIP (1-1.5 hours)
- FKIP gets longest slot because it has 500+ students (largest faculty)
- Small faculties (FKB, FISIP, FIKES, FK, FEB Gol II) get 30 min slots
- Medium faculties (FST, FISIP Gol I) get 45 min slots
- Configuration stored in `war_sessions` and `war_faculties` tables
- No code changes needed — schedule is data-driven via admin panel

### D6: Testing Strategy

- Unit tests for: `WarRuleService::checkCanJoin()`, `ExportService`, `ScoreService`, `KelompokService`
- Feature tests preserved for: all controllers (existing 24 tests + new ones)
- No model factories for FK-heavy models — use `DB::table()->insert()` (existing pattern)
- PHPUnit with array cache/mail/session, sync queue (existing config)

## Testing Decisions

- **Unit tests**: Test services in isolation with mocked dependencies where needed
- **Feature tests**: Test controllers through HTTP layer, verify response codes and database state
- **Existing test patterns**: Use `RefreshDatabase` trait, `DB::table()->insert()` for test data, `actingAs()` for auth
- **Test location**: `tests/Unit/Services/` for services, `tests/Feature/Controllers/` for controllers
- **Coverage target**: All public methods of new services, all controller endpoints touched by changes

## Out of Scope

- WebSocket/SSE implementation (future enhancement, throttle is sufficient now)
- Factory creation for all models (existing pattern uses DB::table insert)
- Email verification UI changes (already implemented in verify-email branch)
- Migration of existing WAR controllers to use new services (incremental, not batch)

## Further Notes

- The `verify-email` branch (`pr/verify-email`) is ready for manual PR creation on GitHub
- The register route fix is in development branch, not yet merged to main
- WAR session IDs in database: gelombang 2 (KKN XIX PERIODE 1), gelombang 3 (KKN XXII PERIODE 2)
- Superadmin credentials: `admin@kknubt.ac.id` / `password`
- DPL seeder: `0005099305@ubt.ac.id` / `kknubt2026`
- Brand colors: `--ubt-navy: #2c3e6e`, `--ubt-navy-dark: #2a2d36`, `--ubt-green: #2e7d32`
- Test mahasiswa: `testwar@wartest.local` / `password`, npm `99999999999`
