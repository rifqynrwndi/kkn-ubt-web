<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Fakultas;
use App\Models\ProgramStudi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportOldKknData extends Command
{
    protected $signature = 'import:old-kkn';

    protected $description = 'Import data KKN lama';

    public function handle()
    {
        $this->info('Starting import...');

        /*
        |--------------------------------------------------------------------------
        | Mapping Fakultas Lama -> ID Fakultas Baru
        |--------------------------------------------------------------------------
        */
        $fakultasMap = [
            1 => 1, // Perikanan dan Ilmu Kelautan
            2 => 2, // Pertanian
            3 => 3, // Teknik
            4 => 4, // Ekonomi
            5 => 5, // FKIP
            6 => 6, // Hukum
            7 => 7, // Ilmu Kesehatan
        ];

        /*
        |--------------------------------------------------------------------------
        | Mapping Prodi Lama -> ID Prodi Baru
        |--------------------------------------------------------------------------
        |
        | SESUAIKAN DENGAN ID DI DATABASE BARU
        |
        */
        $prodiMap = [

            // FKIP
            1  => 15,  // Bimbingan dan Konseling
            2  => 17, // Pendidikan Bahasa Indonesia
            3  => 18, // Pendidikan Bahasa Inggris
            4  => 16, // PGSD
            15 => 13,  // Pendidikan Matematika
            16 => 14,  // Pendidikan Biologi

            // Ekonomi
            5  => 10, // Ekonomi Pembangunan
            6  => 11, // Manajemen
            7  => 12, // Akuntansi

            // Hukum
            8  => 19, // Hukum

            // Teknik
            9  => 6, // Teknik Elektro
            10 => 7, // Teknik Mesin
            11 => 8, // Teknik Sipil
            12 => 9, // Teknik Komputer

            // Pertanian
            13 => 4, // Agribisnis
            14 => 5, // Agroteknologi

            // Perikanan
            17 => 2, // Manajemen Sumber Daya Perairan
            18 => 1,  // Akuakultur
            19 => 3, // Teknologi Hasil Perikanan

            // Kesehatan
            20 => 21, // Kebidanan
            21 => 20, // Keperawatan
            23 => 20, // Keperawatan
        ];

        $oldUsers = DB::connection('old_mysql')
            ->table('users')
            ->get();

        $bar = $this->output->createProgressBar(
            $oldUsers->count()
        );

        $bar->start();

        $createdUser = 0;
        $createdMahasiswa = 0;
        $createdAdmin = 0;

        foreach ($oldUsers as $oldUser) {

            /*
            |--------------------------------------------------------------------------
            | Safe Field
            |--------------------------------------------------------------------------
            */
            $name = $oldUser->nama
                ?? $oldUser->name
                ?? 'Unknown';

            $email = $oldUser->email ?? null;

            $npm = $oldUser->npm
                ?? $oldUser->username
                ?? null;

            $roleId = $oldUser->role ?? null;

            if (! $email) {

                $bar->advance();
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Cek User Exist
            |--------------------------------------------------------------------------
            */
            $user = User::where(
                'email',
                $email
            )->first();

            if (! $user) {

                $user = User::create([
                    'name' => $name,

                    'email' => $email,

                    'password' => $oldUser->password
                        ?? Hash::make('password123'),

                    'email_verified_at' =>
                        $oldUser->email_verified_at
                        ?? now(),

                    'remember_token' =>
                        $oldUser->remember_token
                        ?? Str::random(10),
                ]);

                $createdUser++;
            }

            /*
            |--------------------------------------------------------------------------
            | ROLE HANDLING
            |--------------------------------------------------------------------------
            */

            // SUPERADMIN
            if ($roleId == 4) {

                $user->syncRoles([
                    'superadmin'
                ]);

                $createdAdmin++;

                $bar->advance();
                continue;
            }

            // MAHASISWA
            $user->syncRoles([
                'mahasiswa'
            ]);

            /*
            |--------------------------------------------------------------------------
            | Ambil Data Mahasiswa Lama
            |--------------------------------------------------------------------------
            */
            if ($npm) {

                $oldMahasiswa = DB::connection('old_mysql')
                    ->table('mahasiswa')
                    ->where(
                        'user_id',
                        $oldUser->id
                    )
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | Mapping Jenis Kelamin
                |--------------------------------------------------------------------------
                */
                $jenisKelamin = null;

                if ($oldMahasiswa?->jenis_kelamin == 1) {
                    $jenisKelamin = 'L';
                }

                if ($oldMahasiswa?->jenis_kelamin == 2) {
                    $jenisKelamin = 'P';
                }

                /*
                |--------------------------------------------------------------------------
                | Mapping Fakultas Baru
                |--------------------------------------------------------------------------
                */
                $fakultasId = null;

                if (
                    isset(
                        $fakultasMap[
                            $oldMahasiswa->fakultas ?? null
                        ]
                    )
                ) {

                    $fakultasId = $fakultasMap[
                        $oldMahasiswa->fakultas
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Mapping Prodi Baru
                |--------------------------------------------------------------------------
                */
                $prodiId = null;

                if (
                    isset(
                        $prodiMap[
                            $oldMahasiswa->prodi ?? null
                        ]
                    )
                ) {

                    $prodiId = $prodiMap[
                        $oldMahasiswa->prodi
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Validasi Prodi Exists
                |--------------------------------------------------------------------------
                */
                $prodiExists = ProgramStudi::find($prodiId);

                if (! $prodiExists) {
                    $prodiId = null;
                }

                /*
                |--------------------------------------------------------------------------
                | Simpan Mahasiswa
                |--------------------------------------------------------------------------
                */
                Mahasiswa::updateOrCreate(
                    [
                        'user_id' => $user->id,
                    ],
                    [
                        'npm' => $npm,

                        'jenis_kelamin' =>
                            $jenisKelamin,

                        'foto' =>
                            $oldMahasiswa->foto ?? null,

                        'no_hp' =>
                            $oldMahasiswa->hp ?? null,

                        'prodi_id' =>
                            $prodiId,

                        'nama_ortu' =>
                            $oldMahasiswa->nama_ortu ?? null,

                        'no_hp_ortu' =>
                            $oldMahasiswa->hp_ortu ?? null,

                        'alamat_ortu' =>
                            $oldMahasiswa->alamat_ortu ?? null,

                        'is_biodata_complete' => 1,

                        'created_at' =>
                            $oldMahasiswa->created_at ?? now(),

                        'updated_at' =>
                            $oldMahasiswa->updated_at ?? now(),
                    ]
                );

                $createdMahasiswa++;
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);

        $this->info('Import selesai!');
        $this->info("User created: $createdUser");
        $this->info("Mahasiswa created: $createdMahasiswa");
        $this->info("Admin assigned: $createdAdmin");
    }
}
