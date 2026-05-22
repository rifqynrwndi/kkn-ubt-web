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

        foreach ($kelompoks as $kelompok) {
            foreach ($fakultasList as $fakultas) {
                $kuota = $this->resolveKuota($fakultas->nama_fakultas);

                KelompokKuota::updateOrCreate(
                    [
                        'kelompok_kkn_id' => $kelompok->id,
                        'fakultas_id'     => $fakultas->id,
                    ],
                    [
                        'kuota'           => $kuota,
                        'kuota_laki'      => $kuota >= 5 ? 2 : 1,
                        'kuota_perempuan' => $kuota >= 5 ? 3 : ($kuota === 3 ? 2 : 1),
                    ]
                );
            }
        }

        $total = KelompokKuota::count();
        $this->command->info("KelompokKuota selesai: {$total} rows.");
    }

    private function resolveKuota(string $namaFakultas): int
    {
        if (str_contains($namaFakultas, 'Keguruan')) {
            return 5;
        }

        if (str_contains($namaFakultas, 'Ekonomi')) {
            return 3;
        }

        return 2;
    }
}
