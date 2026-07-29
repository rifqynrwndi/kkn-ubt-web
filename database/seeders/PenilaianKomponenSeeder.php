<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\PenilaianKomponen;

class PenilaianKomponenSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            ['nama_komponen'=>'Nilai DPL', 'deskripsi'=>'Nilai dari bimbingan DPL (Logbook dan Program Kerja)', 'kategori'=>'dpl', 'bobot'=>40, 'urutan'=>1],
            ['nama_komponen'=>'Nilai Desa', 'deskripsi'=>'Nilai Pelaksanaan KKN UBT yang diinput oleh LPPM', 'kategori'=>'lppm', 'bobot'=>30, 'urutan'=>2],
            ['nama_komponen'=>'Nilai LPPM', 'deskripsi'=>'Nilai Luaran Video dan Artikel', 'kategori'=>'lppm', 'bobot'=>30, 'urutan'=>3],
        ];

        foreach ($components as $c) {
            PenilaianKomponen::firstOrCreate(['nama_komponen'=>$c['nama_komponen'], 'kategori'=>$c['kategori']], $c);
        }

        $this->command?->info('PenilaianKomponenSeeder selesai.');
    }
}
