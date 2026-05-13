<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\KelompokKkn;
use App\Models\KelompokKuota;
use Illuminate\Database\Seeder;

class KelompokKuotaSeeder extends Seeder
{
    public function run(): void
    {
        $kelompoks    = KelompokKkn::all();
        $fakultasList = Fakultas::all();

        if ($kelompoks->isEmpty()) {
            $this->command->warn('Tidak ada kelompok KKN. Jalankan KelompokKknSeeder dulu.');
            return;
        }

        KelompokKuota::truncate();

        $rows = [];

        foreach ($kelompoks as $kelompok) {
            foreach ($fakultasList as $fakultas) {
                $kuota = $this->resolveKuota($fakultas->nama_fakultas);

                $rows[] = [
                    'kelompok_kkn_id' => $kelompok->id,
                    'fakultas_id'     => $fakultas->id,
                    'kuota'           => $kuota,
                    'kuota_laki'      => $kuota === 3 ? 1 : 1,
                    'kuota_perempuan' => $kuota === 3 ? 2 : 1,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            KelompokKuota::insert($chunk);
        }

        $total = count($rows);
        $this->command->info("KelompokKuota selesai: {$total} rows.");
    }

    private function resolveKuota(string $namaFakultas): int
    {
        if (str_contains($namaFakultas, 'Keguruan')) {
            return 3;
        }

        return 2;
    }
}
