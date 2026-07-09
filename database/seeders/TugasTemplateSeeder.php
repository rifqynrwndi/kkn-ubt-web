<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KelompokKkn;
use App\Models\TugasKelompok;

class TugasTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['kategori' => 'tugas_kelompok', 'nama_tugas' => 'Program Kerja'],
            ['kategori' => 'luaran_wajib', 'nama_tugas' => 'Video Profil Desa', 'is_wajib' => true],
            ['kategori' => 'luaran_wajib', 'nama_tugas' => 'Draft Artikel', 'is_wajib' => true],
            ['kategori' => 'luaran_lain', 'nama_tugas' => 'Poster'],
            ['kategori' => 'luaran_lain', 'nama_tugas' => 'Video Dokumentasi Pelaksanaan KKN'],
            ['kategori' => 'luaran_lain', 'nama_tugas' => 'Materi Presentasi Akhir'],
            ['kategori' => 'laporan', 'nama_tugas' => 'Laporan Program KKN'],
        ];

        $bar = $this->command?->getOutput()?->createProgressBar(KelompokKkn::count());
        $bar?->start();
        $created = 0;

        KelompokKkn::chunk(100, function ($kelompoks) use ($templates, &$created, $bar) {
            foreach ($kelompoks as $kelompok) {
                foreach ($templates as $t) {
                    TugasKelompok::firstOrCreate([
                        'kelompok_kkn_id' => $kelompok->id,
                        'nama_tugas' => $t['nama_tugas'],
                    ], [
                        'kategori' => $t['kategori'],
                        'is_active' => true,
                        'is_wajib' => $t['is_wajib'] ?? false,
                    ]);
                    $created++;
                }
                $bar?->advance();
            }
        });

        $bar?->finish();
        $this->command?->info("\nTugasTemplateSeeder selesai. {$created} tugas dibuat.");
    }
}
