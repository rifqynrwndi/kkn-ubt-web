<?php

namespace Database\Seeders;

use App\Models\Gelombang;
use Illuminate\Database\Seeder;

class GelombangSeeder extends Seeder
{
    public function run(): void
    {
        Gelombang::firstOrCreate(
            [
                'nama_gelombang' => 'KKN XXII PERIODE 2',
                'tahun' => 2025,
            ],
            [
                'tgl_mulai' => '2026-04-24',
                'tgl_akhir' => '2026-07-23',
                'status' => 'pendaftaran',
            ]
        );
    }
}
