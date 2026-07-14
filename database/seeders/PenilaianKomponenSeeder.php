<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\PenilaianKomponen;

class PenilaianKomponenSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            ['nama_komponen'=>'Logbook', 'deskripsi'=>'Nilai dari kualitas dan kelengkapan Logbook harian KKN', 'kategori'=>'dpl', 'bobot'=>50, 'urutan'=>1],
            ['nama_komponen'=>'Nilai Pelaksanaan KKN UBT', 'deskripsi'=>'Nilai Pelaksanaan KKN UBT', 'kategori'=>'dpl', 'bobot'=>30, 'urutan'=>2],
            ['nama_komponen'=>'Pembekalan KKN UBT', 'deskripsi'=>'Nilai dari partisipasi dan evaluasi pembekalan KKN UBT', 'kategori'=>'lppm', 'bobot'=>10, 'urutan'=>3],
            ['nama_komponen'=>'Seminar Hasil', 'deskripsi'=>'Nilai dari evaluasi laporan dan luaran oleh LPPM', 'kategori'=>'lppm', 'bobot'=>10, 'urutan'=>4],
        ];

        foreach ($components as $c) {
            PenilaianKomponen::firstOrCreate(['nama_komponen'=>$c['nama_komponen'], 'kategori'=>$c['kategori']], $c);
        }

        $this->command?->info('PenilaianKomponenSeeder selesai.');
    }
}
