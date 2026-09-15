<?php

namespace App\Services;

use App\Models\KelompokKkn;
use App\Models\KelompokStatusHistory;
use App\Models\LogBook;
use App\Models\PenilaianKelompok;
use App\Models\TugasKelompok;
use App\Models\TugasSubmission;

class StatusService
{
    public const STAGES = [
        0 => ['nama' => 'Belum Mulai',       'color' => 'secondary', 'desc' => 'Kelompok telah terbentuk melalui WAR. Menunggu proposal diajukan oleh ketua kelompok.'],
        1 => ['nama' => 'Proposal Diajukan', 'color' => 'warning',   'desc' => 'Proposal telah diajukan oleh ketua. Menunggu review dan persetujuan dari Dosen Pembimbing Lapangan.'],
        2 => ['nama' => 'Disetujui DPL',     'color' => 'info',      'desc' => 'Proposal telah disetujui oleh DPL. Kelompok menunggu penugasan DPL untuk memulai aktivitas KKN.'],
        3 => ['nama' => 'Aktif KKN',         'color' => 'success',   'desc' => 'DPL telah ditugaskan. Mahasiswa melakukan aktivitas KKN: mengisi log book, mengumpulkan tugas, dan menyelesaikan program kerja.'],
        4 => ['nama' => 'Selesai',           'color' => 'dark',      'desc' => 'Seluruh rangkaian KKN telah selesai. Log book minimal 40 entri tervalidasi, semua tugas terkumpul, dan penilaian telah selesai.'],
    ];

    public function changeStatus(KelompokKkn $kelompok, int $newStage, ?string $keterangan = null, string $role = 'system'): void
    {
        $oldStage = $kelompok->status_tahap;

        if ($newStage === $oldStage) {
            return;
        }

        $kelompok->update(['status_tahap' => $newStage]);

        KelompokStatusHistory::create([
            'kelompok_kkn_id' => $kelompok->id,
            'status_lama' => $oldStage,
            'status_baru' => $newStage,
            'keterangan' => $keterangan,
            'changed_by' => auth()->id(),
            'changed_by_role' => $role,
        ]);
    }

    public function getCurrentStage(KelompokKkn $kelompok): array
    {
        return self::STAGES[$kelompok->status_tahap] ?? self::STAGES[0];
    }

    public function getHistory(KelompokKkn $kelompok)
    {
        return KelompokStatusHistory::where('kelompok_kkn_id', $kelompok->id)
            ->with('changedBy')
            ->latest()
            ->get();
    }

    public function onProposalSubmitted(KelompokKkn $kelompok): void
    {
        if ($kelompok->status_tahap === 0) {
            $this->changeStatus($kelompok, 1, 'Otomatis: proposal diajukan oleh ketua');
        }
    }

    public function onProposalApproved(KelompokKkn $kelompok): void
    {
        if ($kelompok->status_tahap === 1) {
            $this->changeStatus($kelompok, 2, 'Otomatis: proposal disetujui oleh DPL');
        }
    }

    public function onDplAssigned(KelompokKkn $kelompok): void
    {
        if ($kelompok->status_tahap === 2) {
            $this->changeStatus($kelompok, 3, 'Otomatis: DPL ditugaskan ke kelompok');
        }
    }

    public function checkAutoAdvance(KelompokKkn $kelompok): void
    {
        if ($kelompok->status_tahap !== 3) {
            return;
        }

        $logbookCount = LogBook::where('kelompok_kkn_id', $kelompok->id)
            ->where('is_validated', true)
            ->count();

        $tugasKelompokIds = TugasKelompok::where('kelompok_kkn_id', $kelompok->id)
            ->pluck('id');

        $totalTugas = $tugasKelompokIds->count();

        $submittedTugas = TugasSubmission::whereIn('tugas_kelompok_id', $tugasKelompokIds)
            ->where('status', '!=', 'menunggu')
            ->distinct('tugas_kelompok_id')
            ->count('tugas_kelompok_id');

        $scoredComponents = PenilaianKelompok::where('kelompok_kkn_id', $kelompok->id)
            ->whereNotNull('nilai')
            ->count();

        if ($logbookCount >= 40 && $totalTugas > 0 && $submittedTugas >= $totalTugas && $scoredComponents >= 4) {
            $this->changeStatus($kelompok, 4, 'Otomatis: log book ≥ 40, semua tugas terkumpul, penilaian selesai');
        }
    }
}
