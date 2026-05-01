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
                'nama_gelombang' => 'Gelombang 1',
                'tahun' => now()->year,
            ],
            [
                'tgl_mulai' => now()->startOfMonth(),
                'tgl_akhir' => now()->addMonths(2),
                'status' => 'pendaftaran',
            ]
        );
    }
}
