<?php

namespace App\Services;

use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\TugasKelompok;
use App\Models\WarParticipant;
use Illuminate\Support\Facades\DB;

class KelompokService
{
    private const TUGAS_TEMPLATES = [
        ['kategori' => 'tugas_kelompok', 'nama_tugas' => 'Program Kerja'],
        ['kategori' => 'luaran_wajib', 'nama_tugas' => 'Video Profil Desa', 'is_wajib' => true],
        ['kategori' => 'luaran_wajib', 'nama_tugas' => 'Draft Artikel', 'is_wajib' => true],
        ['kategori' => 'luaran_lain', 'nama_tugas' => 'Poster'],
        ['kategori' => 'luaran_lain', 'nama_tugas' => 'Video Dokumentasi Pelaksanaan KKN'],
        ['kategori' => 'luaran_lain', 'nama_tugas' => 'Materi Presentasi Akhir'],
        ['kategori' => 'laporan', 'nama_tugas' => 'Laporan Program KKN'],
    ];

    public function seedTugasTemplates(KelompokKkn $kelompok): void
    {
        foreach (self::TUGAS_TEMPLATES as $t) {
            TugasKelompok::create([
                'kelompok_kkn_id' => $kelompok->id,
                'kategori' => $t['kategori'],
                'nama_tugas' => $t['nama_tugas'],
                'is_wajib' => $t['is_wajib'] ?? false,
            ]);
        }
    }

    public function removeAnggota(KelompokKkn $kelompok, PesertaKkn $peserta): void
    {
        DB::transaction(function () use ($kelompok, $peserta) {
            $peserta->update(['kelompok_kkn_id' => null]);

            if ($kelompok->ketua_peserta_id === $peserta->id) {
                $kelompok->updateQuietly(['ketua_peserta_id' => null]);
            }

            WarParticipant::where('peserta_kkn_id', $peserta->id)->delete();
        });

        $kelompok->refresh();
        $this->checkAndMarkFull($kelompok);
    }

    public function checkAndMarkFull(KelompokKkn $kelompok): void
    {
        if ($kelompok->status === 'penuh' && $kelompok->terisi < $kelompok->kuota) {
            $kelompok->updateQuietly(['status' => 'dibuka']);
        }
    }
}
