<?php
namespace App\Services;

use App\Models\KelompokKkn;
use App\Models\KelompokStatusHistory;

class StatusService
{
    public const STAGES = [
        0 => ['nama' => 'Pembekelan', 'color' => 'info', 'desc' => 'Tahap persiapan dan pembekalan sebelum mahasiswa turun ke lapangan. DPL dapat mulai melakukan monitoring dan mahasiswa mulai mengisi Log Book.'],
        1 => ['nama' => 'Berjalan', 'color' => 'success', 'desc' => 'Mahasiswa melaksanakan KKN di desa tujuan. Pada tahap ini mahasiswa mengisi Log Book, mengumpulkan tugas, dan laporan. DPL melakukan monitoring dan evaluasi harian.'],
        2 => ['nama' => 'Penyelesaian Tugas', 'color' => 'warning', 'desc' => 'Seluruh tugas dan laporan harus diselesaikan dan dikumpulkan. DPL melakukan review akhir terhadap semua pengumpulan tugas sebelum melanjutkan ke tahap penilaian.'],
        3 => ['nama' => 'Selesai', 'color' => 'dark', 'desc' => 'Seluruh rangkaian KKN telah selesai dilaksanakan. Nilai akhir telah ditetapkan dan tidak dapat diubah lagi.'],
    ];

    public function changeStatus(KelompokKkn $kelompok, int $newStage, string $keterangan = null, string $role = 'superadmin'): void
    {
        $oldStage = $kelompok->status_tahap;

        if ($newStage === $oldStage) return;

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
}
