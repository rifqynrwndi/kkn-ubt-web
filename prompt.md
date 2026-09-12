# KKN UBT - Group Management System Feature Specification

## Project Overview

This document outlines the complete specification for building a post-WAR group management system for **KKN Universitas Borneo Tarakan (UBT)**. After students are assigned to groups through the WAR (real-time group allocation) process, they need a comprehensive platform to manage their KKN activities from proposal submission to final assessment.

---

## Tech Stack

- **Backend**: Laravel
- **Frontend**: Blade + Bootstrap/Stisla Admin Template
- **Database**: MySQL
- **Authentication**: Laravel Sanctum/Fortify (existing)
- **File Storage**: Laravel Storage (local/S3)

---

## User Roles

| Role | Description | Permissions |
|------|-------------|-------------|
| `mahasiswa` | Regular KKN participant | View group info, submit tasks, create log book |
| `ketua_kelompok` | Group leader | All mahasiswa permissions + upload group photo, manage proposal |
| `dosen_pembimbing_lapangan` (DPL) | Field supervisor | Review proposals, validate submissions, assess group |
| `admin_lppm` | LPPM administrator | Manage status, configure tasks, input LPPM assessments |
| `admin_prodi` | Study program admin | Approve/reject participants |

---

## Existing Database Tables

The system builds upon these existing tables:

- `mahasiswa` - Student master data
- `peserta_kkn` - Registered KKN participants
- `kelompok_kkn` - Group data
- `desa_gelombang` - Villages in specific periods
- `gelombang` - KKN periods
- `dosen_pembimbing_lapangan` - DPL data
- `users` - Authentication
- `fakultas` - Faculties
- `program_studi` - Study programs

---

## Module 1: Group Header & Dashboard

### Visual Layout

```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│  [Group Photo]     [Badge: Gelombang KKN XIX Periode 1]    │
│  150x150px         [Badge: PAPAYA202501]                   │
│                    [Badge: Status: Berjalan]               │
│                                                             │
│                    Kelompok Gunungpati                     │
│                    📍 Kelurahan Gunungpati, Kota Semarang  │
│                    📅 29 September - 19 Desember 2025      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Features

- **Group Photo Upload** (Ketua only)
  - Image validation: JPG/PNG, max 2MB
  - Crop/resize preview before upload
  - Modal interface with dropzone
  
- **Dynamic Badges**
  - Gelombang name from `gelombang.nama_gelombang`
  - Group code from `kelompok_kkn.kode_kelompok`
  - Status badge with color coding based on `kelompok_kkn.status_tahap`

- **Group Information**
  - Group name: `kelompok_kkn.nama_kelompok`
  - Location: `desa_gelombang.desa.nama_desa + kecamatan`
  - Period: `gelombang.tgl_mulai` to `gelombang.tgl_akhir`

### Navigation Tabs

Horizontal sticky navigation:
1. Proposal
2. Status
3. Peserta & Pembimbing
4. Pengumpulan Tugas
5. Log Book
6. Penilaian

---

## Module 2: Proposal

### Concept

Form-based proposal creation (NOT file upload). Ketua fills structured sections directly in the system. All members can view but not edit.

### Database Schema

```sql
CREATE TABLE kelompok_proposal (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    kelompok_kkn_id BIGINT UNSIGNED NOT NULL,
    
    -- Proposal Content
    pendahuluan TEXT NOT NULL,
    tujuan TEXT NOT NULL,
    manfaat TEXT NOT NULL,
    hasil_observasi TEXT NULL COMMENT 'Optional, can be filled after KKN execution',
    rancangan_program TEXT NOT NULL,
    solusi_ide TEXT NOT NULL,
    
    -- Status & Review
    status ENUM('draft', 'diajukan', 'disetujui', 'ditolak') DEFAULT 'draft',
    submitted_by BIGINT UNSIGNED COMMENT 'peserta_kkn_id of ketua',
    submitted_at TIMESTAMP NULL,
    
    komentar_dpl TEXT NULL,
    reviewed_by BIGINT UNSIGNED NULL COMMENT 'dpl user_id',
    reviewed_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (kelompok_kkn_id) REFERENCES kelompok_kkn(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES peserta_kkn(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);
```

### Form Sections

1. **Pendahuluan** (required)
   - Introduction to KKN program
   - Background of the location
   - Min 200 characters

2. **Tujuan** (required)
   - Main objectives
   - Expected outcomes
   - Min 100 characters

3. **Manfaat** (required)
   - Benefits for students
   - Benefits for community
   - Benefits for university
   - Min 150 characters

4. **Hasil Observasi** (optional)
   - Can be empty during initial submission
   - Must be filled before status can progress to "Penyelesaian"
   - Field observations and findings
   - Min 200 characters when filled

5. **Rancangan Program** (required)
   - Mandatory group programs
   - Individual programs per member
   - Timeline and schedule
   - Min 300 characters

6. **Solusi/Ide** (required)
   - Solutions to identified problems
   - Innovation ideas
   - Implementation strategies
   - Min 200 characters

### UI/UX Features

**For Ketua (Create/Edit Mode):**
- Rich text editor (TinyMCE/Quill) for each section
- Auto-save to draft every 30 seconds
- Character counter per section
- Section validation indicators
- Two main buttons:
  - "Simpan Draft" - saves without submission
  - "Ajukan Proposal" - submits for DPL review
- Confirmation modal before submission
- Preview mode before submission

**For Members (View Mode):**
- Formatted read-only view
- Status badge prominently displayed
- DPL comments section (if rejected)
- Revision history timeline
- Export to PDF button

**For DPL (Review Mode):**
- Read-only formatted view
- Print-friendly layout
- Action buttons:
  - "Setujui Proposal" (green)
  - "Tolak Proposal" (red)
- Review modal with:
  - Textarea for komentar (required if rejected)
  - Email notification toggle
  - Submit confirmation
- Activity log of all reviews

### Business Logic

```php
ProposalService:
- createDraft()          // Ketua creates new draft
- updateDraft()          // Ketua edits draft
- submitProposal()       // Ketua submits for review
- approveProposal()      // DPL approves
- rejectProposal()       // DPL rejects with comment
- autoSaveDraft()        // Background auto-save
- validateSections()     // Pre-submission validation
- generatePDF()          // Export proposal to PDF
```

### Validation Rules

```php
'pendahuluan'       => 'required|string|min:200',
'tujuan'            => 'required|string|min:100',
'manfaat'           => 'required|string|min:150',
'hasil_observasi'   => 'nullable|string|min:200',
'rancangan_program' => 'required|string|min:300',
'solusi_ide'        => 'required|string|min:200',
```

---

## Module 3: Status Management

### Concept

7-stage workflow tracking system. Each group progresses through predefined stages. Only authorized users (DPL/Admin LPPM) can change status. Full audit trail of all changes.

### Database Schema

```sql
-- Add to kelompok_kkn table
ALTER TABLE kelompok_kkn 
ADD COLUMN status_tahap TINYINT UNSIGNED DEFAULT 0 COMMENT '0-7 status stages';

CREATE TABLE kelompok_status_history (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    kelompok_kkn_id BIGINT UNSIGNED NOT NULL,
    status_lama TINYINT UNSIGNED NOT NULL,
    status_baru TINYINT UNSIGNED NOT NULL,
    keterangan TEXT,
    changed_by BIGINT UNSIGNED NOT NULL COMMENT 'user_id',
    changed_by_role VARCHAR(50) NOT NULL COMMENT 'dpl or admin_lppm',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (kelompok_kkn_id) REFERENCES kelompok_kkn(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id),
    
    INDEX idx_kelompok (kelompok_kkn_id),
    INDEX idx_created (created_at)
);
```

### Status Stages

```php
0 => [
    'nama' => 'Tahap Persiapan',
    'color' => 'secondary',
    'deskripsi' => 'Peserta mempersiapkan anggota lain jika ada dan persyaratan yang diperlukan (di tab Pengumpulan Tugas) sebelum diajukan ke proses "Menunggu Persetujuan Prodi". Perubahan ke proses selanjutnya hanya dapat dilakukan oleh Ketua Kelompok setelah proposal disetujui.',
    'can_progress_to' => [1],
    'required_conditions' => ['proposal_approved'],
],

1 => [
    'nama' => 'Menunggu Persetujuan Prodi',
    'color' => 'warning',
    'deskripsi' => 'Operator Program Studi dapat menyetujui setiap peserta pada tab "Peserta & Pembimbing". Setelah seluruh peserta disetujui oleh prodinya masing-masing maka status dapat dilanjutkan oleh Admin LPPM. Jika ada peserta yang ditolak maka ketua kelompok dapat menghapus peserta tersebut terlebih dahulu sebelum dilanjutkan.',
    'can_progress_to' => [2, 0],
    'required_conditions' => ['all_members_approved'],
],

2 => [
    'nama' => 'Seleksi',
    'color' => 'info',
    'deskripsi' => 'Proses seleksi dilakukan oleh Admin LPPM. Hal-hal yang dapat dipertimbangkan adalah Identitas Mahasiswa, Data Akademik, dan Persyaratan yang telah dikumpulkan peserta pada tab "Pengumpulan Tugas".',
    'can_progress_to' => [3, 1],
    'required_conditions' => [],
],

3 => [
    'nama' => 'Pembekalan',
    'color' => 'primary',
    'deskripsi' => 'Pada proses ini Admin LPPM telah memasukkan Dosen Pembimbing Lapangan. Mahasiswa dapat mulai memasukkan Log Book. Proses pembekalan dapat dilakukan secara khusus oleh LPPM UBT. Untuk melanjutkan ke proses "Berjalan" dapat dilakukan oleh Dosen Pembimbing Lapangan yang telah ditugaskan.',
    'can_progress_to' => [4, 2],
    'required_conditions' => ['dpl_assigned'],
],

4 => [
    'nama' => 'Berjalan',
    'color' => 'success',
    'deskripsi' => 'Mahasiswa melakukan aktivitas KKN sesuai pembekalan yang telah diberikan. Pada proses ini mahasiswa dapat mengisi Log Book, mengumpulkan Tugas (Laporan, dll.). Pembimbing Lapangan dapat melakukan monitoring pada pengisian Log Book dan meresepon pengumpulan tugas. Setelah aktivitas terlaksana maka proses dapat dilanjutkan oleh Dosen Pembimbing Lapangan ke "Penyelesaian Tugas & Penilaian".',
    'can_progress_to' => [5, 3],
    'required_conditions' => ['hasil_observasi_filled', 'min_logbook_entries'],
],

5 => [
    'nama' => 'Penyelesaian Tugas & Penilaian',
    'color' => 'warning',
    'deskripsi' => 'Jika ada penugasan yang belum selesai maka mahasiswa masih dapat segera mengumpulkannya dan Log Book juga harus segera dilengkapi sesuai batas minimum. Setelah Penugasan dan Log Book terselesaikan (Seluruh respon akhir pada Pengumpulan Tugas harus berstatus "Diterima") maka DPL dapat melakukan Penilaian. Jika Proses Penilaian telah selesai maka Dosen Pembimbing Lapangan dapat mengakhiri pelaksanaan aktivitas KKN dengan cara melanjutkan proses ke "Rekognisi".',
    'can_progress_to' => [6, 4],
    'required_conditions' => ['all_tasks_accepted', 'logbook_complete', 'dpl_assessment_done'],
],

6 => [
    'nama' => 'Rekognisi',
    'color' => 'info',
    'deskripsi' => 'LPPM melakukan rekognisi akhir kepada pelaksanaan kegiatan KKN mahasiswa. Hal-hal yang dapat dipertimbangkan adalah Identitas Mahasiswa, Data Akademik, Log Book, Nilai Kegiatan dan Persyaratan yang telah dikumpulkan peserta. Jika seluruh penilaian telah selesai maka Admin LPPM dapat mengubah ke status "Selesai" untuk mengunci Rekognisi.',
    'can_progress_to' => [7, 5],
    'required_conditions' => ['lppm_assessment_done'],
],

7 => [
    'nama' => 'Selesai',
    'color' => 'dark',
    'deskripsi' => 'Aktivitas KKN telah berakhir dan rekognisi bersifat final (tidak dapat menambah/menghapus/mengubah nilai rekognisi).',
    'can_progress_to' => [],
    'required_conditions' => [],
    'is_final' => true,
],
```

### UI Components

**Progress Bar:**
```html
<!-- Horizontal stepper showing 0-7 with current position highlighted -->
<div class="status-stepper">
    <div class="step completed">0. Persiapan</div>
    <div class="step completed">1. Persetujuan</div>
    <div class="step current">2. Seleksi</div>
    <div class="step">3. Pembekalan</div>
    <!-- ... etc -->
</div>
```

**Current Status Card:**
- Large badge with status name
- Color-coded by stage
- Full description (panduan proses)
- List of required conditions
- Progress checklist showing what's completed

**Status History Timeline:**
```
[Icon] 2 bulan yang lalu
       Oleh Admin LPPM (Budi Santoso)
       Status berubah dari "Berjalan" ke "Penyelesaian Tugas & Penilaian"
       Keterangan: Semua log book telah tervalidasi
```

**Change Status Modal (DPL/Admin only):**
- Dropdown showing allowed next statuses
- Validation that checks required conditions
- Textarea for keterangan (required)
- Confirmation step

### Business Logic

```php
StatusService:
- getCurrentStatus()           // Get current stage
- canProgressTo($newStatus)    // Check if transition allowed
- checkConditions()            // Validate required conditions
- changeStatus()               // Execute status change
- getHistory()                 // Get change history
- getAvailableStatuses()       // Get allowed next statuses
- notifyStatusChange()         // Send notifications
```

---

## Module 4: Peserta & Pembimbing

### Concept

Directory of all group members with approval workflow. Shows member details, prodi approval status, and DPL information.

### Database Schema

```sql
CREATE TABLE peserta_approval (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    peserta_kkn_id BIGINT UNSIGNED NOT NULL,
    
    approved_by BIGINT UNSIGNED NULL COMMENT 'user_id of prodi admin',
    status ENUM('menunggu', 'disetujui', 'ditolak') DEFAULT 'menunggu',
    keterangan TEXT NULL,
    approved_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (peserta_kkn_id) REFERENCES peserta_kkn(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id),
    
    UNIQUE KEY unique_peserta (peserta_kkn_id),
    INDEX idx_status (status)
);
```

### UI Layout

**Member Table:**

| # | Foto | Identitas Mahasiswa | Unit Asal | Status Peserta | Aksi |
|---|------|---------------------|-----------|----------------|------|
| 1 | [img] **Ketua** | 3211422053<br>Afif Fadlilah<br>afif@students.ubt.ac.id | FIS<br>S1 Geografi | ✅ Disetujui Prodi<br>By Amidi<br>2025-09-22 | [Detail] |

**DPL Table:**
```

```

### Features

**For All Users:**
- View all member details
- See approval status
- View DPL information
- Export member list to Excel

**For Prodi Admin:**
- Bulk approval interface
- Filter by status (menunggu/disetujui/ditolak)
- Approval modal with keterangan
- Email notification on approval/rejection

**For Ketua:**
- Cannot remove members after approval
- Can request removal via admin

### Business Logic

```php
PesertaService:
- getMembers()              // Get all group members
- getMemberDetails()        // Get single member info
- approveMembers()          // Prodi admin approves
- rejectMember()            // Prodi admin rejects
- getAllApprovalStatus()    // Check if all approved
- notifyApproval()          // Send notification
- exportMemberList()        // Export to Excel
```

---

## Module 5: Pengumpulan Tugas

### Concept

Task submission and review system. Admin/DPL creates task templates. All members can submit. DPL reviews and provides feedback.

### Database Schema

```sql
CREATE TABLE tugas_kelompok (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    kelompok_kkn_id BIGINT UNSIGNED NOT NULL,
    
    kategori ENUM('tugas_kelompok', 'luaran_wajib', 'luaran_lain', 'laporan'),
    nama_tugas VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    
    periode_mulai DATE NOT NULL,
    periode_akhir DATE NOT NULL,
    
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED COMMENT 'admin_lppm or dpl user_id',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (kelompok_kkn_id) REFERENCES kelompok_kkn(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    
    INDEX idx_kategori (kategori),
    INDEX idx_periode (periode_mulai, periode_akhir)
);

CREATE TABLE tugas_submission (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tugas_kelompok_id BIGINT UNSIGNED NOT NULL,
    peserta_kkn_id BIGINT UNSIGNED NOT NULL,
    
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT NULL,
    
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED COMMENT 'in bytes',
    
    status ENUM('menunggu', 'diterima', 'ditolak', 'revisi') DEFAULT 'menunggu',
    komentar_dpl TEXT NULL,
    
    reviewed_by BIGINT UNSIGNED NULL COMMENT 'dpl user_id',
    reviewed_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tugas_kelompok_id) REFERENCES tugas_kelompok(id) ON DELETE CASCADE,
    FOREIGN KEY (peserta_kkn_id) REFERENCES peserta_kkn(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    
    INDEX idx_status (status),
    INDEX idx_peserta (peserta_kkn_id)
);
```

### Task Categories & Templates

**1. Tugas Kelompok**
- Program Kerja

**2. Luaran Wajib**
- Poster
- Video Dokumentasi Pelaksanaan KKN UBT
- Video Profil Desa / Video Profil UMKM
- 2 Publikasi kegiatan di media massa online
- Buku Profil Desa
- Artikel pengabdian

**3. Luaran Lain**
- Hak Cipta
- Buku/modul/panduan praktikum/naskah tutorial
- Poster atau gambar produk
- Infografis Capaian Program Kerja
- Surat Pernyataan Penggunaan Produk
- PPT Seminar Hasil
- Surat Keterangan Bebas Tanggungan

**4. Laporan**
- Laporan Program KKN UBT

### UI Structure

**Accordion Layout:**
```
▼ Tugas Kelompok (1/1 Diterima)
  ▼ Program Kerja
    [Table of submissions]
    
▼ Luaran Wajib (3/6 Diterima)
  ▶ Poster
  ▼ Video Dokumentasi
    [Table of submissions]
  ▶ Video Profil
  ...
  
▶ Luaran Lain (0/8 Diterima)
▶ Laporan (0/1 Diterima)
```

**Submission Table Inside Accordion:**

| Judul | Deskripsi | Berkas | Status | Komentar | Info | Aksi |
|-------|-----------|--------|--------|----------|------|------|
| Laporan KKN 13 | Dokumentasi pelaksanaan | [📥 Unduh] | ✅ Diterima | Disetujui<br>by DPL Budi | Dibuat 5 bulan lalu<br>oleh Aisyah | [Edit] [Hapus] |

**Upload Modal (Mahasiswa):**
- Input: Judul tugas
- Textarea: Deskripsi (optional)
- File upload with drag-drop zone
- File validation: PDF/DOC/DOCX/ZIP, max 10MB
- Upload progress bar
- Preview before submit

**Review Modal (DPL):**
- Display file info and download link
- Radio buttons:
  - ✅ Terima
  - ❌ Tolak
  - 🔄 Minta Revisi
- Textarea: Komentar (required if tolak/revisi)
- Submit button

### Business Logic

```php
TugasService:
- createTaskTemplate()         // Admin creates task
- getTasks()                   // Get by category
- submitTask()                 // Mahasiswa uploads
- updateSubmission()           // Mahasiswa edits
- deleteSubmission()           // Mahasiswa deletes (if not reviewed)
- reviewSubmission()           // DPL reviews
- getSubmissionStats()         // Count accepted/total
- checkAllTasksComplete()      // For status progression
- notifySubmission()           // Notify DPL
- notifyReview()               // Notify submitter
```

### Validation Rules

```php
'judul'      => 'required|string|max:255',
'deskripsi'  => 'nullable|string|max:1000',
'file'       => 'required|file|mimes:pdf,doc,docx,zip|max:10240',
```

---

## Module 6: Log Book

### Concept

Personal daily activity journal for each student. DPL validates entries. Minimum entries required for status progression.

### Database Schema

```sql
CREATE TABLE log_book (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    peserta_kkn_id BIGINT UNSIGNED NOT NULL,
    kelompok_kkn_id BIGINT UNSIGNED NOT NULL,
    
    tanggal DATE NOT NULL,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    
    file_path VARCHAR(255) NULL,
    file_name VARCHAR(255) NULL,
    
    is_validated BOOLEAN DEFAULT FALSE,
    validated_by BIGINT UNSIGNED NULL COMMENT 'dpl user_id',
    validated_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (peserta_kkn_id) REFERENCES peserta_kkn(id) ON DELETE CASCADE,
    FOREIGN KEY (kelompok_kkn_id) REFERENCES kelompok_kkn(id) ON DELETE CASCADE,
    FOREIGN KEY (validated_by) REFERENCES users(id),
    
    INDEX idx_peserta (peserta_kkn_id),
    INDEX idx_tanggal (tanggal),
    INDEX idx_validated (is_validated)
);
```

### Configuration

```php
// config/kkn.php
'logbook' => [
    'min_entries' => 20,  // Minimum required entries
    'max_entries' => 100, // Maximum allowed entries
    'min_description_length' => 50,
    'file_max_size' => 5120, // 5MB in KB
],
```

### UI Features

**Filter & Search Bar:**
- Dropdown: Select member (default: logged in user)
- Search input: By judul
- Date range picker
- Sort: Tanggal (ASC/DESC)
- Button: "Tambah Log Book" (mahasiswa only)

**Progress Indicator:**
```
📊 Progress Log Book: 18/20 terisi (90%)
[████████████████░░] 
```

**Log Book Table:**

| # | Tanggal | Judul | Deskripsi | Berkas | Status | Aksi |
|---|---------|-------|-----------|--------|--------|------|
| 1 | 29 Sep 2025 | Pelepasan Mahasiswa KKN 13 | Seluruh mahasiswa melaksanakan... | - | ✅ Tervalidasi | [Detail] [Edit] |
| 2 | 30 Sep 2025 | Pembekalan | Pembekalan di Kampus UBT... | [📥 Unduh] | ⏳ Belum Divalidasi | [Detail] [Hapus] |

**Add/Edit Modal:**
- Date picker: Tanggal kegiatan
- Input: Judul (max 255 chars)
- Textarea: Deskripsi (min 50 chars, with counter)
- Optional file upload (image/PDF proof)
- Save button

**Bulk Validation (DPL):**
- Checkbox to select multiple entries
- Button: "Validasi Terpilih"
- Confirmation modal

### Business Logic

```php
LogBookService:
- createEntry()                // Mahasiswa creates log
- updateEntry()                // Mahasiswa edits (if not validated)
- deleteEntry()                // Mahasiswa deletes (if not validated)
- getEntries()                 // Filter by member/date
- validateEntry()              // DPL validates single
- bulkValidate()               // DPL validates multiple
- getProgress()                // Count validated/total
- checkMinimumEntries()        // For status progression
- exportToPDF()                // Export personal log book
- notifyValidation()           // Notify mahasiswa
```

### Validation Rules

```php
'tanggal'    => 'required|date|before_or_equal:today',
'judul'      => 'required|string|max:255',
'deskripsi'  => 'required|string|min:50|max:2000',
'file'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
```

---

## Module 7: Penilaian

### Concept

Two-part assessment system. DPL assesses field performance. LPPM assesses academic components. Scores are weighted and calculated automatically.

### Database Schema

```sql
CREATE TABLE penilaian_komponen (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nama_komponen VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    kategori ENUM('dpl', 'lppm') NOT NULL,
    bobot INT UNSIGNED NOT NULL COMMENT 'Weight percentage',
    urutan INT UNSIGNED NOT NULL COMMENT 'Display order',
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_kategori (kategori),
    INDEX idx_urutan (urutan)
);

CREATE TABLE penilaian_kelompok (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    kelompok_kkn_id BIGINT UNSIGNED NOT NULL,
    komponen_id BIGINT UNSIGNED NOT NULL,
    
    nilai DECIMAL(5,2) NULL COMMENT '0.00 to 100.00',
    
    input_by BIGINT UNSIGNED NULL COMMENT 'user_id of assessor',
    input_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (kelompok_kkn_id) REFERENCES kelompok_kkn(id) ON DELETE CASCADE,
    FOREIGN KEY (komponen_id) REFERENCES penilaian_komponen(id),
    FOREIGN KEY (input_by) REFERENCES users(id),
    
    UNIQUE KEY unique_kelompok_komponen (kelompok_kkn_id, komponen_id)
);
```

### Assessment Components (Seeded)

```php
// DPL Components (Total: 60%)
[
    'nama_komponen' => 'Laporan KKN UBT',
    'deskripsi' => 'Nilai dari kualitas Laporan dan Luaran KKN UBT',
    'kategori' => 'dpl',
    'bobot' => 30,
    'urutan' => 1,
],
[
    'nama_komponen' => 'Nilai Pelaksanaan KKN UBT',
    'deskripsi' => 'Nilai dari evaluasi Kepala Desa/Lurah atau DPL terkait pelaksanaan KKN',
    'kategori' => 'dpl',
    'bobot' => 30,
    'urutan' => 2,
],

// LPPM Components (Total: 40%)
[
    'nama_komponen' => 'Pembekalan KKN UBT',
    'deskripsi' => 'Nilai dari partisipasi dan evaluasi pembekalan KKN UBT',
    'kategori' => 'lppm',
    'bobot' => 20,
    'urutan' => 3,
],
[
    'nama_komponen' => 'Seminar Hasil',
    'deskripsi' => 'Nilai dari evaluasi laporan dan luaran oleh LPPM',
    'kategori' => 'lppm',
    'bobot' => 20,
    'urutan' => 4,
],
```

### Score Calculation Formula

```php
// DPL Final Score
dpl_final = (komponen1_bobot * komponen1_nilai + komponen2_bobot * komponen2_nilai) / total_bobot_dpl

// LPPM Final Score
lppm_final = (komponen3_bobot * komponen3_nilai + komponen4_bobot * komponen4_nilai) / total_bobot_lppm

// Overall Final Score
final_score = (dpl_final * total_bobot_dpl + lppm_final * total_bobot_lppm) / 100
```

### UI Layout

**Assessment Table (All Users):**

| # | Komponen Penilaian | Bobot | Nilai |
|---|-------------------|-------|-------|
| **Dosen Pembimbing Lapangan** | | | |
| 1 | Laporan KKN UBT<br><small>Nilai dari kualitas Laporan dan Luaran</small> | 30 | 90.00 |
| 2 | Nilai Pelaksanaan KKN UBT<br><small>Nilai dari evaluasi pelaksanaan</small> | 30 | 88.50 |
| | **Nilai Akhir dari DPL** | | **89.25** |
| **LPPM** | | | |
| 3 | Pembekalan KKN UBT<br><small>Nilai dari partisipasi pembekalan</small> | 20 | 92.00 |
| 4 | Seminar Hasil<br><small>Nilai evaluasi laporan dan luaran</small> | 20 | 90.00 |
| | **Nilai Akhir dari LPPM** | | **91.00** |
| | **NILAI AKHIR TOTAL** | | **89.85** |

**Color Coding:**
- < 60: Red (text-danger)
- 60-75: Yellow (text-warning)
- ≥ 75: Green (text-success)

**Input Form (DPL):**
- Same table structure
- Input fields (number, 0-100, step 0.01) for DPL components
- LPPM components are read-only (disabled)
- Live calculation preview
- Button "Simpan Penilaian" at bottom
- Confirmation modal with final score preview

**Input Form (Admin LPPM):**
- Same table structure
- Input fields for LPPM components only
- DPL components are read-only
- Can view all groups (dropdown selector)
- Bulk input via Excel upload option

### Business Logic

```php
PenilaianService:
- getComponents()              // Get assessment components
- getKelompokNilai()          // Get group scores
- inputNilai()                // Input single component score
- calculateDplFinal()          // Calculate DPL subtotal
- calculateLppmFinal()         // Calculate LPPM subtotal
- calculateFinalScore()        // Calculate overall score
- validateNilai()             // Validate score range
- checkAssessmentComplete()    // Check if all scored
- exportAssessmentPDF()        // Export to PDF
- notifyScoreInput()          // Notify group
```

### Validation Rules

```php
'nilai' => 'required|numeric|min:0|max:100',
```

---

## Authorization Matrix

| Action | Mahasiswa | Ketua | DPL | Admin LPPM | Admin Prodi |
|--------|-----------|-------|-----|------------|-------------|
| View group header | ✅ | ✅ | ✅ | ✅ | ✅ |
| Upload group photo | ❌ | ✅ | ❌ | ❌ | ❌ |
| Create/edit proposal | ❌ | ✅ | ❌ | ❌ | ❌ |
| View proposal | ✅ | ✅ | ✅ | ✅ | ✅ |
| Review proposal | ❌ | ❌ | ✅ | ❌ | ❌ |
| Change status | ❌ | ❌ | ✅ | ✅ | ❌ |
| View members | ✅ | ✅ | ✅ | ✅ | ✅ |
| Approve members | ❌ | ❌ | ❌ | ❌ | ✅ |
| Submit tugas | ✅ | ✅ | ❌ | ❌ | ❌ |
| Review tugas | ❌ | ❌ | ✅ | ❌ | ❌ |
| Create log book | ✅ | ✅ | ❌ | ❌ | ❌ |
| Validate log book | ❌ | ❌ | ✅ | ✅  | ✅ |
| View penilaian | ✅ | ✅ | ✅ | ✅ | ✅ |
| Input DPL score | ❌ | ❌ | ✅ | ❌ | ❌ |
| Input LPPM score | ❌ | ❌ | ❌ | ✅ | ❌ |

---

## Email Notifications

### Trigger Events & Recipients

| Event | Recipient | Subject | Content |
|-------|-----------|---------|---------|
| Proposal submitted | DPL | Proposal Baru Memerlukan Review | Kelompok {nama} telah mengajukan proposal |
| Proposal approved | Ketua + Members | Proposal Disetujui | Proposal kelompok Anda telah disetujui oleh DPL |
| Proposal rejected | Ketua + Members | Proposal Perlu Diperbaiki | Proposal ditolak dengan komentar: {komentar} |
| Status changed | All Members | Perubahan Status KKN | Status berubah menjadi {status_baru} |
| Tugas submitted | DPL | Tugas Baru Perlu Review | {nama_mahasiswa} mengumpulkan tugas {nama_tugas} |
| Tugas reviewed | Submitter | Status Tugas Diperbarui | Tugas Anda "{judul}" berstatus {status} |
| Log book validated | Owner | Log Book Tervalidasi | DPL telah memvalidasi {jumlah} log book Anda |
| Penilaian completed | All Members | Penilaian Selesai | Nilai akhir kelompok: {nilai} |
| Member approved | Member | Persetujuan Prodi | Anda telah disetujui oleh Program Studi |
| Member rejected | Member | Status Pendaftaran | Pendaftaran Anda ditolak: {keterangan} |

---

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Kelompok/
│   │   │   ├── KelompokController.php
│   │   │   ├── ProposalController.php
│   │   │   ├── StatusController.php
│   │   │   ├── PesertaController.php
│   │   │   ├── TugasController.php
│   │   │   ├── LogBookController.php
│   │   │   └── PenilaianController.php
│   ├── Requests/
│   │   ├── Kelompok/
│   │   │   ├── ProposalRequest.php
│   │   │   ├── TugasSubmissionRequest.php
│   │   │   ├── LogBookRequest.php
│   │   │   └── PenilaianRequest.php
│   ├── Middleware/
│   │   └── CheckKelompokAccess.php
├── Services/
│   ├── Kelompok/
│   │   ├── ProposalService.php
│   │   ├── StatusService.php
│   │   ├── PesertaService.php
│   │   ├── TugasService.php
│   │   ├── LogBookService.php
│   │   └── PenilaianService.php
├── Models/
│   ├── KelompokProposal.php
│   ├── KelompokStatusHistory.php
│   ├── PesertaApproval.php
│   ├── TugasKelompok.php
│   ├── TugasSubmission.php
│   ├── LogBook.php
│   ├── PenilaianKomponen.php
│   └── PenilaianKelompok.php
├── Policies/
│   ├── KelompokPolicy.php
│   ├── ProposalPolicy.php
│   ├── TugasPolicy.php
│   ├── LogBookPolicy.php
│   └── PenilaianPolicy.php
├── Mail/
│   ├── ProposalSubmitted.php
│   ├── ProposalReviewed.php
│   ├── StatusChanged.php
│   ├── TugasSubmitted.php
│   ├── TugasReviewed.php
│   └── PenilaianCompleted.php
├── Jobs/
│   ├── SendBulkNotification.php
│   └── GeneratePenilaianPDF.php

database/
├── migrations/
│   ├── 2025_01_XX_create_kelompok_proposal_table.php
│   ├── 2025_01_XX_create_kelompok_status_history_table.php
│   ├── 2025_01_XX_create_peserta_approval_table.php
│   ├── 2025_01_XX_create_tugas_kelompok_table.php
│   ├── 2025_01_XX_create_tugas_submission_table.php
│   ├── 2025_01_XX_create_log_book_table.php
│   ├── 2025_01_XX_create_penilaian_komponen_table.php
│   └── 2025_01_XX_create_penilaian_kelompok_table.php
├── seeders/
│   ├── StatusStageSeeder.php
│   ├── PenilaianKomponenSeeder.php
│   └── TugasTemplateSeeder.php

resources/
├── views/
│   ├── kelompok/
│   │   ├── index.blade.php (dashboard with header)
│   │   ├── proposal/
│   │   │   ├── form.blade.php
│   │   │   ├── show.blade.php
│   │   │   └── review.blade.php
│   │   ├── status/
│   │   │   ├── index.blade.php
│   │   │   └── change-modal.blade.php
│   │   ├── peserta/
│   │   │   ├── index.blade.php
│   │   │   └── approval-modal.blade.php
│   │   ├── tugas/
│   │   │   ├── index.blade.php
│   │   │   ├── upload-modal.blade.php
│   │   │   └── review-modal.blade.php
│   │   ├── logbook/
│   │   │   ├── index.blade.php
│   │   │   └── form-modal.blade.php
│   │   └── penilaian/
│   │       ├── index.blade.php
│   │       └── input-form.blade.php
│   ├── components/
│   │   ├── kelompok-header.blade.php
│   │   ├── kelompok-nav.blade.php
│   │   ├── status-badge.blade.php
│   │   └── score-display.blade.php

routes/
└── web.php (kelompok routes)

config/
└── kkn.php (module configurations)
```

---

## Routes Structure

```php
Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::prefix('kelompok')->name('kelompok.')->group(function () {
        
        // Dashboard
        Route::get('/', [KelompokController::class, 'index'])->name('index');
        Route::post('/upload-photo', [KelompokController::class, 'uploadPhoto'])->name('upload-photo');
        
        // Proposal
        Route::prefix('proposal')->name('proposal.')->group(function () {
            Route::get('/', [ProposalController::class, 'index'])->name('index');
            Route::get('/create', [ProposalController::class, 'create'])->name('create');
            Route::post('/', [ProposalController::class, 'store'])->name('store');
            Route::get('/{proposal}', [ProposalController::class, 'show'])->name('show');
            Route::get('/{proposal}/edit', [ProposalController::class, 'edit'])->name('edit');
            Route::put('/{proposal}', [ProposalController::class, 'update'])->name('update');
            Route::post('/{proposal}/submit', [ProposalController::class, 'submit'])->name('submit');
            Route::post('/{proposal}/review', [ProposalController::class, 'review'])->name('review');
            Route::get('/{proposal}/pdf', [ProposalController::class, 'exportPDF'])->name('pdf');
        });
        
        // Status
        Route::prefix('status')->name('status.')->group(function () {
            Route::get('/', [StatusController::class, 'index'])->name('index');
            Route::post('/change', [StatusController::class, 'change'])->name('change');
            Route::get('/history', [StatusController::class, 'history'])->name('history');
        });
        
        // Peserta & Pembimbing
        Route::prefix('peserta')->name('peserta.')->group(function () {
            Route::get('/', [PesertaController::class, 'index'])->name('index');
            Route::post('/{peserta}/approve', [PesertaController::class, 'approve'])->name('approve');
            Route::post('/{peserta}/reject', [PesertaController::class, 'reject'])->name('reject');
            Route::post('/bulk-approve', [PesertaController::class, 'bulkApprove'])->name('bulk-approve');
            Route::get('/export', [PesertaController::class, 'export'])->name('export');
        });
        
        // Pengumpulan Tugas
        Route::prefix('tugas')->name('tugas.')->group(function () {
            Route::get('/', [TugasController::class, 'index'])->name('index');
            Route::post('/{tugas}/submit', [TugasController::class, 'submit'])->name('submit');
            Route::put('/submission/{submission}', [TugasController::class, 'update'])->name('update');
            Route::delete('/submission/{submission}', [TugasController::class, 'destroy'])->name('destroy');
            Route::post('/submission/{submission}/review', [TugasController::class, 'review'])->name('review');
            Route::get('/submission/{submission}/download', [TugasController::class, 'download'])->name('download');
        });
        
        // Log Book
        Route::prefix('logbook')->name('logbook.')->group(function () {
            Route::get('/', [LogBookController::class, 'index'])->name('index');
            Route::post('/', [LogBookController::class, 'store'])->name('store');
            Route::put('/{logbook}', [LogBookController::class, 'update'])->name('update');
            Route::delete('/{logbook}', [LogBookController::class, 'destroy'])->name('destroy');
            Route::post('/{logbook}/validate', [LogBookController::class, 'validate'])->name('validate');
            Route::post('/bulk-validate', [LogBookController::class, 'bulkValidate'])->name('bulk-validate');
            Route::get('/export', [LogBookController::class, 'export'])->name('export');
        });
        
        // Penilaian
        Route::prefix('penilaian')->name('penilaian.')->group(function () {
            Route::get('/', [PenilaianController::class, 'index'])->name('index');
            Route::post('/', [PenilaianController::class, 'input'])->name('input');
            Route::get('/export', [PenilaianController::class, 'export'])->name('export');
        });
        
    });
    
});
```

---

## Configuration File

```php
// config/kkn.php

return [
    
    'proposal' => [
        'min_lengths' => [
            'pendahuluan'       => 200,
            'tujuan'            => 100,
            'manfaat'           => 150,
            'hasil_observasi'   => 200,
            'rancangan_program' => 300,
            'solusi_ide'        => 200,
        ],
        'autosave_interval' => 30, // seconds
    ],
    
    'status' => [
        'stages' => [
            // Stage definitions...
        ],
    ],
    
    'tugas' => [
        'max_file_size' => 10240, // KB
        'allowed_extensions' => ['pdf', 'doc', 'docx', 'zip', 'jpg', 'png'],
        'categories' => [
            'tugas_kelompok' => 'Tugas Kelompok',
            'luaran_wajib'   => 'Luaran Wajib',
            'luaran_lain'    => 'Luaran Lain',
            'laporan'        => 'Laporan',
        ],
    ],
    
    'logbook' => [
        'min_entries'            => 20,
        'max_entries'            => 100,
        'min_description_length' => 50,
        'max_file_size'          => 5120, // KB
        'allowed_file_types'     => ['jpg', 'jpeg', 'png', 'pdf'],
    ],
    
    'penilaian' => [
        'score_range' => [
            'min' => 0,
            'max' => 100,
        ],
        'grade_colors' => [
            'danger'  => 60,  // < 60
            'warning' => 75,  // 60-75
            'success' => 100, // >= 75
        ],
    ],
    
];
```

---

## Expected Deliverables

Please generate a complete, production-ready implementation with:

1. ✅ All migration files
2. ✅ All Eloquent models with relationships
3. ✅ All controllers with complete CRUD methods
4. ✅ All service classes with business logic
5. ✅ All FormRequest validation classes
6. ✅ All policy classes for authorization
7. ✅ All Blade views with responsive design
8. ✅ All routes properly named and organized
9. ✅ All seeders for default data
10. ✅ Email notification classes
11. ✅ Helper functions if needed
12. ✅ Configuration file

### Code Quality Requirements

- ✅ **Clean code**: No comments unless absolutely necessary
- ✅ **Service pattern**: Business logic in services, not controllers
- ✅ **Thin controllers**: Controllers only handle HTTP, delegate to services
- ✅ **Policy-based auth**: All authorization via policies
- ✅ **Form requests**: All validation in dedicated request classes
- ✅ **Reusable components**: Blade components for repeated UI elements
- ✅ **Consistent naming**: Follow Laravel conventions
- ✅ **Type hints**: Use PHP type declarations
- ✅ **Error handling**: Proper exception handling
- ✅ **Database transactions**: Use for multi-step operations
- ✅ **Eager loading**: Avoid N+1 queries
- ✅ **Responsive UI**: Mobile-first design
- ✅ **Accessibility**: Semantic HTML and ARIA labels

---

## Example Usage Flow

### Student Creates Proposal

```
1. Ketua navigates to Proposal tab
2. Clicks "Buat Proposal"
3. Fills all sections in WYSIWYG editor
4. System auto-saves every 30 seconds to draft
5. Clicks "Preview" to review
6. Clicks "Ajukan Proposal"
7. System validates all sections
8. Status changes to "diajukan"
9. Email sent to DPL
10. DPL receives notification
```

### DPL Reviews Proposal

```
1. DPL receives email notification
2. Logs in and navigates to group
3. Opens Proposal tab
4. Reviews all sections
5. Clicks "Setujui" or "Tolak"
6. If reject: enters komentar
7. Submits review
8. Status updates in database
9. Email sent to ketua and members
10. Status progression unlocked
```

### Mahasiswa Submits Log Book

```
1. Mahasiswa navigates to Log Book tab
2. Clicks "Tambah Log Book"
3. Modal opens with form
4. Fills: tanggal, judul, deskripsi
5. Optionally uploads file
6. Clicks "Simpan"
7. System validates (min 50 chars, etc)
8. Entry saved with status "belum tervalidasi"
9. Progress counter updates: 18/20
10. DPL can validate later
```

---

## Notes

- All dates use Indonesian format (d M Y)
- All money amounts use Indonesian format (Rp 1.000.000)
- All file uploads stored in `storage/app/kelompok/`
- All PDFs generated with DomPDF or similar
- All exports use Laravel Excel
- All emails queued for async sending
- All images optimized with Intervention Image
- All forms have CSRF protection
- All API responses follow consistent structure

---

**END OF SPECIFICATION**

This document serves as the complete blueprint for implementing the KKN UBT Group Management System. Follow Laravel best practices and the established coding style from the WAR system implementation.
