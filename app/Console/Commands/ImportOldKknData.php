<?php

namespace App\Console\Commands;

use App\Models\Mahasiswa;
use App\Models\PesertaKkn;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportOldKknData extends Command
{
    protected $signature = 'import:old-kkn
                            {--old-gelombang= : Filter by old gelombang_id (e.g. 16=KKN XXII PERIODE 2)}
                            {--new-gelombang= : New gelombang_id for PesertaKkn records}
                            {--skip-peserta : Skip creating PesertaKkn records}
                            {--chunk=500 : Chunk size for processing}';

    protected $description = 'Import peserta KKN dari database lama (peminatan table) ke database baru';

    private array $prodiMap = [
        1  => 15, 2  => 17, 3  => 18, 4  => 16,
        15 => 13, 16 => 14,
        5  => 10, 6  => 11, 7  => 12,
        8  => 19,
        9  => 6,  10 => 7,  11 => 8,  12 => 9,
        13 => 4,  14 => 5,
        17 => 2,  18 => 1,  19 => 3,
        20 => 21, 21 => 20, 23 => 20,
    ];

    public function handle()
    {
        if (! $this->canConnect()) {
            $this->error('Tidak dapat terhubung ke database lama. Periksa OLD_DB_* di .env');
            return 1;
        }

        $oldGelombangId = $this->option('old-gelombang');
        $newGelombangId = $this->option('new-gelombang');
        $skipPeserta    = $this->option('skip-peserta');
        $chunk          = (int) $this->option('chunk');

        // Build query from peminatan joined with mahasiswa + users
        $query = DB::connection('old_mysql')
            ->table('peminatan as p')
            ->join('mahasiswa as m', 'p.mahasiswa_id', '=', 'm.id')
            ->join('users as u', 'm.user_id', '=', 'u.id')
            ->where('p.status', '1')
            ->whereNotNull('u.email')
            ->select(
                'p.id as peminatan_id',
                'p.gelombang_id as old_gelombang_id',
                'u.id as old_user_id',
                'u.name',
                'u.role',
                'u.username as npm',
                'u.email',
                'u.password as old_password',
                'u.email_verified_at',
                'u.remember_token',
                'm.id as old_mahasiswa_id',
                'm.hp',
                'm.jenis_kelamin',
                'm.fakultas as old_fakultas_id',
                'm.prodi as old_prodi_id',
                'm.nama_ortu',
                'm.hp_ortu',
                'm.alamat_ortu',
                'm.foto'
            );

        if ($oldGelombangId) {
            $query->where('p.gelombang_id', $oldGelombangId);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->warn('Tidak ada data yang ditemukan di database lama.');
            return 0;
        }

        $this->info("Peserta ditemukan di DB lama: {$total}");
        if ($oldGelombangId) $this->info("Old Gelombang ID: {$oldGelombangId}");
        if ($newGelombangId)  $this->info("New Gelombang ID: {$newGelombangId}");
        $this->info("Chunk size: {$chunk}");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $createdUser      = 0;
        $createdAdminUser = 0;
        $createdMahasiswa = 0;
        $createdPeserta   = 0;
        $skippedExists    = 0;
        $skippedSuperAdmin = 0;

        $prodiCache = ProgramStudi::all()->keyBy('id');

        $query->orderBy('p.id')
            ->chunk($chunk, function ($rows) use (
                &$createdUser, &$createdAdminUser, &$createdMahasiswa, &$createdPeserta,
                &$skippedExists, &$skippedSuperAdmin,
                $newGelombangId, $skipPeserta, $prodiCache, $bar
            ) {
                $emails = [];
                $dataByEmail = [];
                foreach ($rows as $row) {
                    if (empty($row->email)) continue;
                    $emails[] = $row->email;
                    $dataByEmail[$row->email] = $row;
                }

                $existingEmails = User::whereIn('email', $emails)->pluck('id', 'email');

                foreach ($dataByEmail as $email => $row) {
                    $bar->advance();

                    if (isset($existingEmails[$email])) {
                        $skippedExists++;
                        continue;
                    }

                    $isAdmin = in_array((string) $row->role, ['4', 4]);

                    // Skip old superadmin
                    if ($email === 'ubt.tarakan@gmail.com') {
                        $skippedSuperAdmin++;
                        continue;
                    }

                    // Create User
                    $user = User::create([
                        'name'              => $row->name,
                        'email'             => $email,
                        'password'          => $row->old_password ?? Hash::make(Str::random(16)),
                        'email_verified_at' => $row->email_verified_at ?? now(),
                        'remember_token'    => $row->remember_token ?? Str::random(10),
                    ]);

                    if ($isAdmin) {
                        // LPPM admin: no role, no Mahasiswa, no PesertaKkn
                        $createdAdminUser++;
                        continue;
                    }

                    $user->assignRole('mahasiswa');
                    $createdUser++;

                    // Map gender
                    $gender = match ((string) $row->jenis_kelamin) {
                        '1' => 'L', '2' => 'P', default => null,
                    };

                    // Map prodi
                    $oldProdi = (int) ($row->old_prodi_id ?? 0);
                    $prodiId  = null;
                    if ($oldProdi && isset($this->prodiMap[$oldProdi])) {
                        $mapped = $this->prodiMap[$oldProdi];
                        $prodiId = isset($prodiCache[$mapped]) ? $mapped : null;
                    }

                    // Create Mahasiswa
                    Mahasiswa::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'npm'                  => $row->npm,
                            'jenis_kelamin'        => $gender,
                            'prodi_id'             => $prodiId,
                            'foto'                 => $row->foto ?? null,
                            'no_hp'                => $row->hp ?? null,
                            'nama_ortu'            => $row->nama_ortu ?? null,
                            'no_hp_ortu'           => $row->hp_ortu ?? null,
                            'alamat_ortu'          => $row->alamat_ortu ?? null,
                            'is_biodata_complete'  => ! empty($gender),
                        ]
                    );
                    $createdMahasiswa++;

                    // Create PesertaKkn
                    if ($skipPeserta || ! $newGelombangId) continue;

                    PesertaKkn::firstOrCreate(
                        ['mahasiswa_id' => $user->id, 'gelombang_id' => $newGelombangId],
                        ['status_pendaftaran' => 'approved', 'submitted_at' => now()]
                    );
                    $createdPeserta++;
                }
            });

        $bar->finish();
        $this->newLine(2);
        $this->info('Import selesai!');
        $this->info("Mahasiswa: {$createdUser} created");
        $this->info("Admin (no Mahasiswa): {$createdAdminUser} created");
        $this->info("Peserta:   {$createdPeserta} created");
        $this->info("Skipped (exists): {$skippedExists}");
        $this->info("Skipped (superadmin lama): {$skippedSuperAdmin}");
    }

    private function canConnect(): bool
    {
        try {
            DB::connection('old_mysql')->getPdo();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
