<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\PenilaianKomponen;
use App\Models\PenilaianKelompok;
use App\Models\PenilaianIndividu;

class PenilaianKomponenSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        PenilaianIndividu::truncate();
        PenilaianKelompok::truncate();
        PenilaianKomponen::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $components = [
            ['nama_komponen'=>'Nilai DPL', 'deskripsi'=>'Nilai dari bimbingan DPL (Logbook dan Program Kerja)', 'kategori'=>'dpl', 'bobot'=>40, 'urutan'=>1],
            ['nama_komponen'=>'Nilai Desa', 'deskripsi'=>'Nilai Pelaksanaan KKN UBT yang diinput oleh LPPM', 'kategori'=>'lppm', 'bobot'=>30, 'urutan'=>2],
            ['nama_komponen'=>'Nilai LPPM', 'deskripsi'=>'Nilai Luaran Video dan Artikel', 'kategori'=>'lppm', 'bobot'=>30, 'urutan'=>3],
        ];

        foreach ($components as $c) {
            PenilaianKomponen::create($c);
        }

        $this->command?->info('PenilaianKomponenSeeder selesai.');
    }
}
