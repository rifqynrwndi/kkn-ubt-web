# Spec: Biodata Tempat/Tanggal Lahir + Telegram Group Banner

## Problem Statement

Mahasiswa KKN UBT needs two additional requirements for biodata completeness: **Tempat Lahir** and **Tanggal Lahir**. These are needed for official KKN documentation but were not required in previous years. Old KKN users who already completed biodata (`is_biodata_complete = true`) never filled these fields, so they currently bypass the requirement. Additionally, after registration mahasiswa need to join a Telegram group, but there's no visible prompt on the dashboard — they rely on word of mouth.

## Solution

1. Add `birth_place` (string) and `birth_date` (date) fields to the `mahasiswa` table. Include them in the biodata completeness check so ALL users (old and new) must fill them. Force-reset `is_biodata_complete = false` for existing users via migration so they see "Biodata Belum Lengkap" on next login and are prompted to complete.

2. Add `telegram_group_url` (string, nullable) to the `gelombangs` table. Display a persistent banner on the mahasiswa dashboard when the active gelombang has a Telegram group link. The banner opens the link in a new tab. No tracking — banner shows every login as long as the link exists.

## User Stories

1. As a **mahasiswa**, I want to fill my Tempat Lahir in the biodata form, so that my personal data is complete for KKN administration.
2. As a **mahasiswa**, I want to fill my Tanggal Lahir (date picker) in the biodata form, so that my personal data is complete for KKN administration.
3. As a **mahasiswa**, I want to see "Biodata Belum Lengkap" when I log in if I haven't filled birth_place/birth_date, so that I know I need to complete my biodata.
4. As a **mahasiswa**, I want the biodata form to show my birth_place and birth_date fields pre-filled (if previously entered), so that I can edit them if needed.
5. As a **mahasiswa**, I want birth_place and birth_date to be required — the form should not save without them.
6. As a **mahasiswa**, I want to see a Telegram group banner on my dashboard when my active gelombang has a group link, so that I can join the communication channel.
7. As a **mahasiswa**, I want the Telegram banner to have a clear "Join Grup Telegram" button that opens the link in a new tab.
8. As a **mahasiswa**, I want the Telegram banner to appear every login (no dismiss), so that I can always find the link even if I was kicked from the group or lost my Telegram account.
9. As a **mahasiswa**, I want the Telegram banner to only show when there's an active gelombang with a link — not when there's no link set.
10. As a **superadmin**, I want to set the Telegram group URL when creating or editing a gelombang, so that mahasiswa can join the correct group.
11. As a **superadmin**, I want the Telegram URL field to be optional (nullable) — not all gelombangs may have a Telegram group.
12. As a **superadmin**, I want to edit the Telegram group URL on an existing gelombang, so that I can update it if the group changes.
13. As an **old KKN user** (previously completed biodata), I want to see "Biodata Belum Lengkap" on my next login after the new fields are deployed, so that I'm prompted to fill birth_place and birth_date.
14. As an **old KKN user**, I want to fill just 2 fields (Tempat Lahir + Tanggal Lahir) to re-complete my biodata — minimal friction.
15. As a **developer**, I want the migration to force-reset `is_biodata_complete = false` for all existing users, so that the completeness check is consistent across old and new users.
16. As a **developer**, I want the `is_biodata_complete` logic to check all 10 fields (8 existing + 2 new) plus foto not being default avatar, so that completeness is comprehensive.
17. As a **developer**, I want `birth_place` and `birth_date` columns to be nullable in the database, so that the migration doesn't fail on existing rows.
18. As a **developer**, I want the Telegram URL validated as a valid URL format when saved by admin.

## Implementation Decisions

### Database Schema Changes

**mahasiswa table — new columns:**
- `birth_place` — string, nullable, after `no_hp`
- `birth_date` — date, nullable, after `birth_place`

**gelombangs table — new column:**
- `telegram_group_url` — string, nullable, after existing fields

**Migration side effect:** After adding columns, run `DB::table('mahasiswa')->update(['is_biodata_complete' => false])` to force all existing users to re-complete biodata with the new fields.

### Biodata Completeness Logic

The `is_biodata_complete` flag in `BiodataController::update()` will be set to `true` only when ALL of these conditions pass:
1. `npm` not null
2. `jenis_kelamin` not null
3. `no_hp` not null
4. `prodi_id` not null
5. `nama_ortu` not null
6. `no_hp_ortu` not null
7. `alamat_ortu` not null
8. `foto` not null AND not the default avatar (`avatar/avatar-1.png`)
9. `birth_place` not null (NEW)
10. `birth_date` not null (NEW)

The existing `$hasFoto` check in `BiodataController` will be extended to also require `birth_place` and `birth_date`.

### Biodata Form Fields

- **Tempat Lahir** — text input, placeholder "Contoh: Tarakan", required
- **Tanggal Lahir** — date input (HTML5 `<input type="date">`), required, max = today minus 15 years (reasonable age for KKN)

Both fields placed between No HP and Nama Orang Tua sections in the form layout.

### Telegram Group Banner

- Displayed in `home-user.blade.php` (mahasiswa dashboard)
- Shown only when: user has role `mahasiswa` AND active gelombang exists AND `telegram_group_url` is not null
- Banner content: info alert with Telegram icon, text "Gabung Grup Telegram KKN", button "Join Sekarang" that opens `telegram_group_url` in new tab
- No dismiss/acknowledge mechanism — always visible
- No tracking column needed — purely driven by `gelombangs.telegram_group_url` existence

### Gelombang Admin Form

- Add `telegram_group_url` field (text input, placeholder "https://t.me/...") to gelombang create and edit forms
- Field is optional (nullable)
- Validated as `nullable|url` in GelombangController

### Model Updates

- `Mahasiswa` model: add `birth_place`, `birth_date` to `$fillable`; add `'birth_date' => 'date'` to `$casts`
- `Gelombang` model: add `telegram_group_url` to `$fillable`

## Testing Decisions

- Test that `BiodataController::update()` sets `is_biodata_complete = false` when `birth_place` is missing
- Test that `BiodataController::update()` sets `is_biodata_complete = false` when `birth_date` is missing
- Test that `BiodataController::update()` sets `is_biodata_complete = true` when all fields including birth_place + birth_date are present
- Test that `BiodataController::update()` sets `is_biodata_complete = false` when foto is default avatar (existing behavior preserved)
- Test that the Telegram banner renders when `telegram_group_url` is present on active gelombang
- Test that the Telegram banner does NOT render when `telegram_group_url` is null
- Test that `GelombangController` validates `telegram_group_url` as optional URL
- Prior art: existing tests in `tests/Feature/BiodataTest.php` and `tests/Feature/GelombangTest.php`

## Out of Scope

- Tracking whether mahasiswa actually joined the Telegram group (no `joined_telegram` boolean)
- Telegram bot integration (just a URL link, not a bot)
- Multiple Telegram groups per gelombang (one group per gelombang only)
- Making birth_place/birth_date editable after initial completion (user can re-edit via biodata form anytime)
- SMS/WhatsApp notifications about Telegram group (just dashboard banner)

## Further Notes

- The `is_biodata_complete` force-reset will affect ALL existing mahasiswa users — they will see "Biodata Belum Lengkap" on next login. This is intentional and expected (ADR-001).
- The Telegram banner should use the existing Stisla alert component styling (`.alert.alert-info` or custom with Telegram blue `#0088cc`).
- If a gelombang has `telegram_group_url = null`, no banner is shown — not even a "no group" message.
- `birth_date` HTML5 date input will show a calendar picker natively in all modern browsers.
