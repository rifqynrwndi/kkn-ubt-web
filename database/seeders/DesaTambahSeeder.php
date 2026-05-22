<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Gelombang;
use App\Models\DesaGelombang;
use App\Models\KelompokKkn;
use App\Models\KelompokKuota;
use App\Models\Fakultas;

class DesaTambahSeeder extends Seeder
{
    public function run(): void
    {
        $gelombang = Gelombang::latest('id')->first();

        if (! $gelombang) {
            $gelombang = Gelombang::firstOrCreate(
                ['nama_gelombang' => 'KKN XXII PERIODE 2', 'tahun' => 2025],
                ['tgl_mulai' => '2026-04-24', 'tgl_akhir' => '2026-07-23', 'status' => 'pendaftaran']
            );
            $this->command?->info('Gelombang dibuat: ' . $gelombang->nama_gelombang);
        }

        $fakultasList = Fakultas::all();

        $data = [
            // ── KABUPATEN NUNUKAN ──────────────────────
            'Kabupaten Nunukan' => [
                'Sebatik' => [
                    'Tanjung Karang', 'Balansiku', 'Sungai Manurung', 'Padaidi',
                ],
                'Sebatik Utara' => [
                    'Seberang', 'Sungai Pancang', 'Lapri',
                ],
                'Sebatik Timur' => [
                    'Sungai Nyamuk', 'Tanjung Harapan', 'Bukit Aru Indah', 'Tanjung Aru',
                ],
                'Sebatik Tengah' => [
                    'Sungai Limau', 'Maspul', 'Aji Kuning', 'Bukit Harapan',
                ],
                'Sebatik Barat' => [
                    'Setabu', 'Bambangan', 'Binalawan', 'Liang Bunyu', 'Desa Persiapan Tembaring',
                ],
                'Lumbis Ogong' => [
                    'Payang', 'Suyadon', 'Tukulon', 'Samunti', 'Semata',
                    'Sungoi', 'Salan', 'Sinampila I', 'Tambalang Hilir', 'Tadungus', 'Long Bulu',
                ],
                'Lumbis' => [
                    'Dabulon', 'Libang', 'Tanjung Hilir', 'Mansalong', 'Kalampising', 'Sasibu',
                ],
            ],

            // ── KOTA TARAKAN ──────────────────────────
            'Kota Tarakan' => [
                'Tarakan Barat' => [
                    'Karang Anyar', 'Karang Balik', 'Karang Harapan',
                ],
                'Tarakan Tengah' => [
                    'Kampung 1 SKIP','Pamusian', 'Sebengkok',
                ],
                'Tarakan Utara' => [
                    'Juata Kerikil', 'Juata Laut', 'Juata Permai',
                ],
                'Tarakan Timur' => [
                    'Pantai Amal', 'Kampung 4', 'Kampung 6',
                    'Mamburungan', 'Gunung Lingkas', 'Lingkas Ujung',
                ],
            ],
        ];

        $totalDesa = 0;
        $skipDesa = 0;
        $totalKec = 0;

        foreach ($data as $kabupaten => $kecamatans) {
            foreach ($kecamatans as $namaKecamatan => $desas) {
                $kecamatan = Kecamatan::firstOrCreate([
                    'nama_kecamatan' => $namaKecamatan,
                    'kabupaten'      => $kabupaten,
                ]);
                $totalKec++;

                foreach ($desas as $namaDesa) {
                    $desa = Desa::firstOrCreate([
                        'kecamatan_id' => $kecamatan->id,
                        'nama_desa'    => $namaDesa,
                    ], ['aktif' => 1]);

                    $totalDesa++;

                    $desaGelombang = DesaGelombang::firstOrCreate([
                        'gelombang_id' => $gelombang->id,
                        'desa_id'      => $desa->id,
                    ], [
                        'kuota_total' => 12,
                        'status'      => 'dibuka',
                    ]);

                    $namaKelompok = $desa->nama_desa . ' - ' . $gelombang->nama_gelombang;

                    KelompokKkn::firstOrCreate([
                        'desa_gelombang_id' => $desaGelombang->id,
                    ], [
                        'dosen_pembimbing_lapangan_id' => null,
                        'nama_kelompok' => $namaKelompok,
                        'kuota'         => 12,
                        'status'        => 'dibuka',
                    ]);

                    if ($desaGelombang->wasRecentlyCreated) {
                        foreach ($fakultasList as $fakultas) {
                            $kuota = match (true) {
                                str_contains($fakultas->nama_fakultas, 'Keguruan') => 5,
                                str_contains($fakultas->nama_fakultas, 'Ekonomi') => 3,
                                default => 2,
                            };

                            KelompokKuota::firstOrCreate([
                                'kelompok_kkn_id' => $desaGelombang->kelompokKkn->first()?->id,
                                'fakultas_id'     => $fakultas->id,
                            ], [
                                'kuota'           => $kuota,
                                'kuota_laki'      => $kuota >= 5 ? 2 : 1,
                                'kuota_perempuan' => $kuota >= 5 ? 3 : ($kuota === 3 ? 2 : 1),
                            ]);
                        }
                    }
                }
            }
        }

        $this->command?->info("Seeder Selesai!");
        $this->command?->info("Kecamatan: {$totalKec}");
        $this->command?->info("Desa: {$totalDesa} (skip: {$skipDesa})");
        $this->command?->info("Total: " . ($totalKec + $totalDesa) . " records");
    }
}
