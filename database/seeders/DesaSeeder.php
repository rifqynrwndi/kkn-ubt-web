<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Gelombang;
use App\Models\DesaGelombang;

class DesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil data gelombang yang paling terbaru (berdasarkan ID terakhir)
        $gelombangTerbaru = Gelombang::latest('id')->first();

        if (! $gelombangTerbaru) {
            $gelombangTerbaru = Gelombang::firstOrCreate(
                [
                    'nama_gelombang' => 'KKN Default Periode ' . now()->year,
                    'tahun'          => now()->year,
                ],
                [
                    'tgl_mulai'      => now(),
                    'tgl_akhir'      => now()->addMonths(3),
                    'status'         => 'persiapan',
                ]
            );
            $this->command->info('Gelombang default dibuat: ' . $gelombangTerbaru->nama_gelombang);
        }

        // Struktur Data: ['Nama Kabupaten' => ['Nama Kecamatan' => ['Desa 1', 'Desa 2']]]
        $dataLokus = [
            'Kabupaten Malinau' => [
                'Malinau Kota' => [
                    'Batu Lidung',
                    'Malinau Kota',
                    'Pelita Kanaan',
                    'Malinau Hulu',
                    'Malinau Hilir',
                    'Tanjung Keranjang'
                ],
                'Malinau Utara' => [
                    'Kaliamok',
                    'Luso',
                    'Malinau Seberang',
                    'Putat',
                    'Salap',
                    'Seruyung',
                    'Respen Tubu',
                    'Belayan',
                    'Sembuak Warod',
                    'Lubak Manis',
                    'Kelapis',
                    'Semenggaris'
                ],
                'Malinau Barat' => [
                    'Long Bila',
                    'Long Kenipe',
                    'Sesua',
                    'Sentaban',
                    'Tanjung Lapang',
                    'Taras',
                    'Kuala Lapang',
                    'Sempayang'
                ],
                'Mentarang' => [
                    'Long Bisai',
                    'Pulau Sapi',
                    'Lidung Kemenci',
                    'Mentarang Baru'
                ],
            ],

            'Kabupaten Tana Tidung' => [
                'Sesayap' => [
                    'Tideng Pale',
                    'Limbu Sedulun',
                    'Sebawang'
                ],
                'Sesayap Hilir' => [
                    'Bebatu',
                    'Sengkong',
                    'Manjelutung',
                    'Bandan Bikis'
                ],
                'Betayau' => [
                    'Mendupo',
                    'Kujau',
                    'Maning',
                    'Buong Baru',
                    'Periuk'
                ],
                'Muruk Rian' => [
                    'Rian Rayo',
                    'Balayan Ari',
                    'Seputuk'
                ],
                'Tana Lia' => [
                    'Tengkudacing'
                ],
            ],

            'Kabupaten Bulungan' => [
                'Tanjung Selor' => [
                    'Jelarai Selor',
                    'Tengkapak',
                    'Gunung Sari',
                    'Apung',
                    'Bumi Rahayu',
                    'Gunung Seriang',
                ],
                'Bunyu' => [
                    'Bunyu Selatan',
                    'Bunyu Timur',
                    'Bunyu Barat'
                ],
                'Tanjung Palas' => [
                    'Gunung Putih',
                    'Pejalin',
                    'Antutan'
                ],
                'Tanjung Palas Barat' => [
                    'Long Sam'
                ],
                'Tanjung Palas Tengah' => [
                    'Salimbatu',
                    'Silva Rahayu'
                ],
                'Tanjung Palas Utara' => [
                    'Pimping',
                    'Karang Agung',
                    'Panca Agung',
                    'Ruhui Rahayu',
                    'Ardimulyo',
                    'Kelubir'

                ],
                'Tanjung Palas Timur' => [
                    'Mangkupadi',
                    'Tanah Kuning',
                    'Binai',
                    'Tanjung Agung'
                ],
            ],
        ];

        foreach ($dataLokus as $namaKabupaten => $kecamatans) {
            foreach ($kecamatans as $namaKecamatan => $desas) {

                // Buat atau cari Kecamatan
                $kecamatan = Kecamatan::firstOrCreate([
                    'nama_kecamatan' => $namaKecamatan,
                    'kabupaten'      => $namaKabupaten,
                ]);

                foreach ($desas as $namaDesa) {

                    // Buat atau cari Desa
                    $desa = Desa::firstOrCreate([
                        'kecamatan_id' => $kecamatan->id,
                        'nama_desa'    => $namaDesa,
                    ], [
                        'aktif' => 1
                    ]);

                    DesaGelombang::firstOrCreate([
                        'gelombang_id' => $gelombangTerbaru->id,
                        'desa_id'      => $desa->id,
                    ], [
                        'kuota_total'                  => 12,
                        'status'                       => 'dibuka',
                        'dosen_pembimbing_lapangan_id' => null,
                    ]);
                }
            }
        }

        $this->command->info('Seeder Kecamatan, Desa, dan Desa Gelombang berhasil dijalankan!');
    }
}
