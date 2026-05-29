<?php

namespace App\Services\War;

use App\Models\KelompokKkn;
use App\Models\KelompokKuota;
use App\Models\PesertaKkn;

class WarRuleService
{
    public const MAX_KELOMPOK_SIZE = 12;
    public const MAX_LAKI          = 4;
    public const MAX_PEREMPUAN     = 10;
    public const MAX_SAME_PRODI    = 3;

    public function checkAllRules(
        KelompokKkn   $kelompok,
        PesertaKkn    $peserta,
        array         $currentMembers,
        KelompokKuota $kelompokKuota,
    ): array {
        return array_values(array_filter([
            $this->checkKelompokFull($currentMembers),
            $this->checkGenderQuota($peserta, $currentMembers),
            $this->checkFakultasPerKelompok($peserta, $currentMembers, $kelompokKuota),
            $this->checkProdiQuota($peserta, $currentMembers),
        ]));
    }

    public function checkKelompokFull(array $members): ?string
    {
        if (count($members) >= self::MAX_KELOMPOK_SIZE) {
            return 'Kelompok sudah penuh (maks ' . self::MAX_KELOMPOK_SIZE . ' orang).';
        }

        return null;
    }

    public function checkGenderQuota(PesertaKkn $peserta, array $members): ?string
    {
        $gender = $peserta->mahasiswa->jenis_kelamin;
        $max    = $gender === 'L' ? self::MAX_LAKI : self::MAX_PEREMPUAN;
        $count  = collect($members)->where('mahasiswa.jenis_kelamin', $gender)->count();

        if ($count >= $max) {
            $label = $gender === 'L' ? 'laki-laki' : 'perempuan';
            return "Kuota {$label} di kelompok ini sudah penuh (maks {$max} orang).";
        }

        return null;
    }

    public function checkFakultasPerKelompok(
        PesertaKkn    $peserta,
        array         $members,
        KelompokKuota $kelompokKuota,
    ): ?string {
        $fakultasId = $peserta->mahasiswa->prodi->fakultas_id;
        $count      = collect($members)
            ->where('mahasiswa.prodi.fakultas_id', $fakultasId)
            ->count();

        if ($count >= $kelompokKuota->kuota) {
            $nama = $kelompokKuota->fakultas->nama_fakultas ?? 'Fakultas kamu';
            return "{$nama} sudah penuh di kelompok ini (maks {$kelompokKuota->kuota} orang).";
        }

        return null;
    }

    public function checkProdiQuota(PesertaKkn $peserta, array $members): ?string
    {
        $fakultas = $peserta->mahasiswa->prodi->fakultas;

        if ($fakultas && $fakultas->prodi()->count() <= 1) {
            return null;
        }

        $prodiId        = $peserta->mahasiswa->prodi_id;
        $countSameProdi = collect($members)->where('mahasiswa.prodi_id', $prodiId)->count();

        if ($countSameProdi >= self::MAX_SAME_PRODI) {
            $nama = $peserta->mahasiswa->prodi->nama_prodi ?? 'Program studi kamu';
            return "{$nama} sudah ada di kelompok ini (maks " . self::MAX_SAME_PRODI . " orang per prodi).";
        }

        return null;
    }
}
