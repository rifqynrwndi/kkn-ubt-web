# ADR 0002: Extract Service Layer from KelompokKknController

## Status

Accepted

## Context

`KelompokKknController` is 684 lines with 17 methods. It handles:

- CRUD operations
- Score calculation (dead `calcScore()` with DB-driven weights vs working `show()` with fixed 40/30/30)
- Excel export (93 lines, duplicated in `TugasAdminController` and `MahasiswaManagementController`)
- Tugas template seeding (hardcoded 7 templates in `store()`)
- Membership management (`hapusAnggota` with transaction + ketua reassignment)

## Decision

Extract 3 services in order of priority:

### 1. ExportService (`app/Services/ExportService.php`)

Methods:
- `exportKelompokXlsx($kelompoks)` — multi-sheet by kabupaten
- `exportTugasXlsx($tugas)` — single-sheet with student details
- `exportMahasiswaXlsx($mahasiswa)` — single-sheet student list

Private helpers: `applyHeaderStyle()`, `applyRowStyle()`, `getFacultyColor()`.

### 2. ScoreService (`app/Services/ScoreService.php`)

Methods:
- `calculateDplScore($penilaian)` — 3 components (profil_desa, pengamatan, laporan)
- `calculateLppmScore($penilaian)` — 3 components
- `calculateTotal($dplScore, $lppmScore)` — weighted: DPL 60% + LPPM 40%

Delete dead `calcScore()` from `KelompokKknModel`.

### 3. KelompokService (`app/Services/KelompokService.php`)

Methods:
- `seedTugasTemplates(KelompokKkn $kelompok)` — 7 templates
- `removeAnggota(KelompokKkn $kelompok, PesertaKkn $peserta)` — transaction + ketua reassignment
- `checkAndMarkFull(KelompokKkn $kelompok)` — update status if at capacity

## Consequences

- `KelompokKknController` drops from ~684 to ~350 lines
- Export logic reusable across 3 controllers
- Score calculation testable in isolation
- Membership operations consistent (single code path)
