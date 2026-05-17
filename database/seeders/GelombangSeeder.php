<?php

namespace Database\Seeders;

use App\Models\Gelombang;
use Illuminate\Database\Seeder;

class GelombangSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->info('GelombangSeeder: skipped in production. Gunakan admin UI untuk membuat gelombang.');

            return;
        }

        Gelombang::firstOrCreate(
            [
                'nama_gelombang' => 'KKN XIX PERIODE 1',
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
