# Context: kkn-ubt-web

## Domain Glossary

### KKN (Kuliah Kerja Nyata)
Community service program. Students are organized into **kelompok** (groups) and deployed to **desa** (villages) under the supervision of a **DPL** (Dosen Pembimbing Lapangan / field supervisor).

### Kelompok KKN
A group of students assigned to a village. Has a **ketua** (leader), **kuota** (capacity), **status** (dibuka/penuh/ditutup/draft), and **status_tahap** (KKN progress stage 0–4).

### WAR (Work Assignment & Reporting)
Concurrent group allocation system. Students join kelompok during timed sessions with faculty-based scheduling. Uses row locking + deadlock retry for race-condition safety.

### Peserta KKN
A student's enrollment record linking them to a **gelombang** (enrollment wave) and optionally a **kelompok**.

### Join Validation
Rules that determine whether a student can join a kelompok. Four rules: kelompok full, faculty quota, gender limit, prodi limit. Implemented in `WarRuleService`.

### Status Tahap
5-stage KKN progress lifecycle: Belum Mulai (0) → Proposal Diajukan (1) → Disetujui DPL (2) → Aktif KKN (3) → Selesai (4). Managed by `StatusService`.

### Penilaian (Grading)
Scored by DPL (60%) and LPPM (40%). Components stored in `penilaian_komponen`, scores in `penilaian_kelompok` and `penilaian_individu`.

### Export
Excel export for kelompok, tugas, and mahasiswa data. Three duplicated implementations being unified into `ExportService`.

### Tugas Templates
7 predefined tasks seeded when a kelompok is created. Stored in `tugas_kelompok` table.

### DPL (Dosen Pembimbing Lapangan)
Field supervisor assigned to kelompok. Rates students on 3 components: profil_desa, pengamatan, laporan.

### LPPM (Lembaga Penelitian dan Pengabdian kepada Masyarakat)
University body that rates kelompok on 3 components: laporan, presentasi, publikasi.

## Design Decisions

- **Join validation**: Unified through `WarRuleService::checkCanJoin()` returning bool. Configurable prodi limit flag.
- **Service extraction**: ExportService → ScoreService → KelompokService (priority order).
- **WAR caching**: Redis-backed, 2-3s TTL, invalidated on join/leave.
- **Testing**: Unit tests for new services. Feature tests for controllers.
