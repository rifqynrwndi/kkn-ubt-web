<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DesaGelombang;
use App\Models\KelompokKkn;

class KelompokKknSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mengambil semua data desa yang sudah dimasukkan ke gelombang
        // Kita juga me-load relasi 'desa' dan 'gelombang' untuk penamaan kelompok
        $desaGelombangs = DesaGelombang::with(['desa', 'gelombang'])->get();

        if ($desaGelombangs->isEmpty()) {
            $this->command->warn('Data Desa Gelombang kosong. Silakan jalankan DesaSeeder terlebih dahulu.');
            return;
        }

        foreach ($desaGelombangs as $dg) {

            // Format nama kelompok: Nama Desa - Nama Gelombang
            // Contoh: Apung - KKN XIX PERIODE 1
            $namaKelompok = $dg->desa->nama_desa . ' - ' . $dg->gelombang->nama_gelombang;

            // Kita gunakan firstOrCreate agar jika seeder dijalankan ulang,
            // tidak terjadi duplikasi kelompok di desa yang sama
            KelompokKkn::firstOrCreate([
                'desa_gelombang_id' => $dg->id,
            ], [
                'dosen_pembimbing_lapangan_id' => null, // DPL masih kosong
                'nama_kelompok' => $namaKelompok,
                'kuota'         => 12,                  // Mengikuti kuota total desa_gelombang
                'status'        => 'dibuka',            // Status dibuka agar bisa langsung dipilih mahasiswa
            ]);
        }

        $this->command->info('Seeder Kelompok KKN berhasil: 1 Kelompok per Desa telah dibuat!');
    }
}
