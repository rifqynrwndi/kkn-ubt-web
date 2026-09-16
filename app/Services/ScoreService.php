<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ScoreService
{
    public const WEIGHTS = [
        'dpl' => 0.40,
        'desa' => 0.30,
        'lppm' => 0.30,
    ];

    /**
     * Calculate score breakdown for a kelompok.
     *
     * @param  Collection  $penilaianIndividu  PenilaianIndividu models (loaded with kelompok_kkn_id filter)
     * @param  Collection  $penilaianKelompok  PenilaianKelompok models (loaded with kelompok_kkn_id filter, with 'komponen' relation)
     * @param  Collection  $komponenList       PenilaianKomponen models (all components, ordered)
     * @return array{dpl: float|null, desa: float|null, lppm: float|null, total: float|null}
     */
    public function getScoreBreakdown(
        Collection $penilaianIndividu,
        Collection $penilaianKelompok,
        Collection $komponenList,
    ): array {
        $dplScore = $this->calculateComponentFromIndividu($penilaianIndividu, $komponenList, 'Nilai DPL');
        $desaScore = $this->calculateComponentFromIndividu($penilaianIndividu, $komponenList, 'Nilai Desa');
        $lppmScore = $this->calculateComponentFromKelompok($penilaianKelompok, $komponenList, 'Nilai LPPM');

        return [
            'dpl' => $dplScore,
            'desa' => $desaScore,
            'lppm' => $lppmScore,
            'total' => $this->calculateTotal($dplScore, $desaScore, $lppmScore),
        ];
    }

    private function calculateComponentFromIndividu(
        Collection $penilaianIndividu,
        Collection $komponenList,
        string $komponenName,
    ): ?float {
        $komponen = $komponenList->firstWhere('nama_komponen', $komponenName);
        if (! $komponen) {
            return null;
        }

        $scores = $penilaianIndividu
            ->where('komponen_id', $komponen->id)
            ->pluck('nilai')
            ->filter();

        return $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
    }

    private function calculateComponentFromKelompok(
        Collection $penilaianKelompok,
        Collection $komponenList,
        string $komponenName,
    ): ?float {
        $komponen = $komponenList->firstWhere('nama_komponen', $komponenName);
        if (! $komponen) {
            return null;
        }

        $penilaian = $penilaianKelompok->first(fn ($v) => $v->komponen->nama_komponen === $komponenName);

        return $penilaian?->nilai ? round((float) $penilaian->nilai, 2) : null;
    }

    private function calculateTotal(?float $dplScore, ?float $desaScore, ?float $lppmScore): ?float
    {
        if (is_null($dplScore) || is_null($desaScore) || is_null($lppmScore)) {
            return null;
        }

        return round(
            $dplScore * self::WEIGHTS['dpl']
            + $desaScore * self::WEIGHTS['desa']
            + $lppmScore * self::WEIGHTS['lppm'],
            2
        );
    }
}
