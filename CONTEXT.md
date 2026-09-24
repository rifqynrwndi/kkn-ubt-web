# Context: KKN UBT Web

## Glossary

| Term | Definition |
|------|-----------|
| Biodata | Personal data form that mahasiswa must complete before registering for KKN. Fields: NPM, prodi, jenis kelamin, no HP, nama ortu, no HP ortu, alamat ortu, foto, **birth_place**, **birth_date**. |
| is_biodata_complete | Boolean on `mahasiswa` table. `true` only when ALL required fields (including birth_place, birth_date) AND foto (not default avatar) are filled. Gates access via `EnsureBiodataComplete` middleware. |
| DHS (Daftar Hasil Studi) | Academic transcript PDF uploaded by mahasiswa, verified by superadmin/admin_lppm. |
| Gelombang | Registration period. Has status (`pendaftaran`, `berjalan`, etc.), date range, and **telegram_group_url** (optional). |
| Telegram Group Link | Per-gelombang link stored in `gelombangs.telegram_group_url`. Displayed as persistent banner on mahasiswa dashboard. No tracking — banner shows every login if link exists. |
| Pembimbing / DPL | Dosen Pembimbing Lapangan — supervises kelompok on location. |
| Superadmin | Full system access. Manages gelombang, verifies DHS, manages settings. |
| Admin LPPM | Manages gelombang documents and DHS verification. |
| Admin Prodi | Manages mahasiswa in their study program. |
| Kelompok | Group of mahasiswa assigned to one DPL and one desa. |
| WAR (Work Allocation Rules) | Concurrent group allocation system with lockForUpdate + deadlock retry. |

## Decisions

### ADR-001: Biodata completeness requires birth_place + birth_date
- **Status:** Accepted
- **Context:** Adding tempat & tanggal lahir as required fields for KKN registration.
- **Decision:** All users (old + new) must fill `birth_place` and `birth_date`. Old users with `is_biodata_complete = true` will be force-reset to `false` via migration.
- **Consequence:** Old KKN users will see "Biodata Belum Lengkap" on next login and must fill 2 new fields. Minimal friction — just city + date.

### ADR-002: Telegram group link per-gelombang, persistent banner
- **Status:** Accepted
- **Context:** Mahasiswa need to join Telegram group after registration.
- **Decision:** `telegram_group_url` stored on `gelombangs` table. Banner on mahasiswa dashboard shows if active gelombang has a link. No tracking — banner always visible. Opens in new tab.
- **Consequence:** If user gets kicked or loses Telegram account, link is still accessible from dashboard. No admin overhead for tracking.

## Domain Model Updates

### New fields on `mahasiswa` table
- `birth_place` — string, nullable, city name (e.g., "Tarakan")
- `birth_date` — date, nullable

### New field on `gelombangs` table
- `telegram_group_url` — string, nullable, URL to Telegram group

### Updated completeness check
`is_biodata_complete` requires ALL of:
1. `npm` not null
2. `jenis_kelamin` not null
3. `no_hp` not null
4. `prodi_id` not null
5. `nama_ortu` not null
6. `no_hp_ortu` not null
7. `alamat_ortu` not null
8. `foto` not null AND not default avatar
9. **`birth_place` not null** (NEW)
10. **`birth_date` not null** (NEW)
