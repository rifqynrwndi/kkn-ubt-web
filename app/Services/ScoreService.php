<?php

namespace App\Services;

use App\Models\PenilaianKelompok;
use App\Models\PenilaianKomponen;

class ScoreService
{
    public function calculateDplScore(int $kelompokId): ?float
    {
        $komponen = PenilaianKomponen::where('nama_komponen', 'Nilai DPL')->first();
        if (! $komponen) {
            return null;
        }

        $scores = PenilaianKelompok::where('kelompok_kkn_id', $kelompokId)
            ->where('komponen_id', $komponen->id)
            ->pluck('nilai')
            ->filter();

        return $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
    }

    public function calculateDesaScore(int $kelompokId): ?float
    {
        $komponen = PenilaianKomponen::where('nama_komponen', 'Nilai Desa')->first();
        if (! $komponen) {
            return null;
        }

        $scores = PenilaianKelompok::where('kelompok_kkn_id', $kelompokId)
            ->where('komponen_id', $komponen->id)
            ->pluck('nilai')
            ->filter();

        return $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
    }

    public function calculateLppmScore(int $kelompokId): ?float
    {
        $komponen = PenilaianKomponen::where('nama_komponen', 'Nilai LPPM')->first();
        if (! $komponen) {
            return null;
        }

        $penilaian = PenilaianKelompok::where('kelompok_kkn_id', $kelompokId)
            ->where('komponen_id', $komponen->id)
            ->first();

        return $penilaian?->nilai;
    }

    public function calculateTotal(?float $dplScore, ?float $desaScore, ?float $lppmScore): ?float
    {
        if (is_null($dplScore) || is_null($desaScore) || is_null($lppmScore)) {
            return null;
        }

        return round($dplScore * 0.40 + $desaScore * 0.30 + $lppmScore * 0.30, 2);
    }
}
