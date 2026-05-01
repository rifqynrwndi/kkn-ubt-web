<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fakultas;
use App\Models\ProgramStudi;

class FakultasProdiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Perikanan dan Ilmu Kelautan' => [
                'Akuakultur',
                'Manajemen Sumber Daya Perairan',
                'Teknologi Hasil Perikanan',
            ],

            'Pertanian' => [
                'Agribisnis',
                'Agroteknologi',
            ],

            'Teknik' => [
                'Teknik Elektro',
                'Teknik Mesin',
                'Teknik Sipil',
                'Teknik Komputer',
            ],

            'Ekonomi' => [
                'Ekonomi Pembangunan',
                'Manajemen',
                'Akuntansi',
            ],

            'Keguruan dan Ilmu Pendidikan' => [
                'Pendidikan Matematika',
                'Pendidikan Biologi',
                'Bimbingan dan Konseling',
                'Pendidikan Guru Sekolah Dasar',
                'Pendidikan Bahasa Indonesia',
                'Pendidikan Bahasa Inggris',
            ],

            'Hukum' => [
                'Hukum',
            ],

            'Ilmu Kesehatan' => [
                'Keperawatan',
                'Kebidanan',
            ],
        ];

        foreach ($data as $namaFakultas => $prodis) {
            $fakultas = Fakultas::updateOrCreate(
                ['nama_fakultas' => $namaFakultas]
            );

            foreach ($prodis as $namaProdi) {
                ProgramStudi::updateOrCreate([
                    'fakultas_id' => $fakultas->id,
                    'nama_prodi' => $namaProdi,
                ]);
            }
        }
    }
}
