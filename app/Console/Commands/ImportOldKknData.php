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
                            {--skip-mahasiswa : Only import users, skip mahasiswa profiles}
                            {--skip-peserta : Skip creating PesertaKkn records}
                            {--gelombang= : Gelombang ID for PesertaKkn records}
                            {--limit=0 : Limit how many users to import (0 = all)}
                            {--chunk=500 : Chunk size for processing}';

    protected $description = 'Import data KKN lama dari database old_mysql ke database baru';

    private array $fakultasMap = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7];

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
        $this->info('Starting optimized import...');
        $this->info('Memory limit: ' . ini_get('memory_limit'));

        if (! $this->canConnect()) {
            $this->error('Tidak dapat terhubung ke database old_mysql. Periksa konfigurasi OLD_DB_* di .env');
            return 1;
        }

        $skipMahasiswa = $this->option('skip-mahasiswa');
        $skipPeserta   = $this->option('skip-peserta');
        $gelombangId   = $this->option('gelombang');
        $limit         = (int) $this->option('limit');
        $chunk         = (int) $this->option('chunk');

        $total = DB::connection('old_mysql')->table('users')->count();
        $total = $limit > 0 ? min($limit, $total) : $total;

        $this->info("Total users in old DB: {$total}");
        $this->info("Chunk size: {$chunk}");
        if ($skipMahasiswa) $this->warn('Skipping mahasiswa profiles');
        if ($skipPeserta)   $this->warn('Skipping PesertaKkn records');
        if ($gelombangId)   $this->info("Gelombang ID: {$gelombangId}");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $createdUser = 0;
        $createdMahasiswa = 0;
        $createdPeserta = 0;
        $skippedNoEmail = 0;
        $skippedAdmin = 0;
        $skippedExists = 0;
        $skippedNoMhs = 0;

        $prodiCache = ProgramStudi::all()->keyBy('id');

        DB::connection('old_mysql')
            ->table('users')
            ->when($limit > 0, fn($q) => $q->limit($limit))
            ->orderBy('id')
            ->chunk($chunk, function ($oldUsers) use (
                &$createdUser, &$createdMahasiswa, &$createdPeserta,
                &$skippedNoEmail, &$skippedAdmin, &$skippedExists, &$skippedNoMhs,
                $skipMahasiswa, $skipPeserta, $gelombangId, $prodiCache, $bar
            ) {
                $oldUserIds = $oldUsers->pluck('id');

                $oldMahasiswas = DB::connection('old_mysql')
                    ->table('mahasiswa')
                    ->whereIn('user_id', $oldUserIds)
                    ->get()
                    ->keyBy('user_id');

                $existingEmails = User::whereIn('email', $oldUsers->pluck('email')->filter())
                    ->pluck('id', 'email');

                foreach ($oldUsers as $old) {
                    $bar->advance();

                    $email = $old->email ?? null;
                    if (! $email) { $skippedNoEmail++; continue; }

                    if (($old->role ?? null) == 4) { $skippedAdmin++; continue; }

                    if (isset($existingEmails[$email])) { $skippedExists++; continue; }

                    $user = User::create([
                        'name'              => $old->nama ?? $old->name ?? 'Unknown',
                        'email'             => $email,
                        'password'          => $old->password ?? Hash::make(Str::random(16)),
                        'email_verified_at' => $old->email_verified_at ?? now(),
                        'remember_token'    => $old->remember_token ?? Str::random(10),
                    ]);
                    $user->assignRole('mahasiswa');
                    $createdUser++;

                    if ($skipMahasiswa) continue;

                    $oldMhs = $oldMahasiswas[$old->id] ?? null;
                    if (! $oldMhs) { $skippedNoMhs++; continue; }

                    $npm    = $oldMhs->npm ?? $old->username ?? null;
                    $gender = match ((int) ($oldMhs->jenis_kelamin ?? 0)) {
                        1 => 'L', 2 => 'P', default => null,
                    };
                    $prodiId = null;
                    $oldProdi = $oldMhs->prodi ?? null;
                    if ($oldProdi && isset($this->prodiMap[$oldProdi])) {
                        $mapped = $this->prodiMap[$oldProdi];
                        $prodiId = isset($prodiCache[$mapped]) ? $mapped : null;
                    }

                    Mahasiswa::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'npm'                  => $npm,
                            'jenis_kelamin'        => $gender,
                            'prodi_id'             => $prodiId,
                            'foto'                 => $oldMhs->foto ?? null,
                            'no_hp'                => $oldMhs->hp ?? null,
                            'nama_ortu'            => $oldMhs->nama_ortu ?? null,
                            'no_hp_ortu'           => $oldMhs->hp_ortu ?? null,
                            'alamat_ortu'          => $oldMhs->alamat_ortu ?? null,
                            'is_biodata_complete'  => ! empty($gender),
                        ]
                    );
                    $createdMahasiswa++;

                    if ($skipPeserta || ! $gelombangId) continue;

                    PesertaKkn::firstOrCreate(
                        [
                            'mahasiswa_id' => $user->id,
                            'gelombang_id' => $gelombangId,
                        ],
                        [
                            'status_pendaftaran' => 'approved',
                            'submitted_at'       => now(),
                        ]
                    );
                    $createdPeserta++;
                }

                // Free memory
                unset($oldUsers, $oldMahasiswas, $existingEmails);
            });

        $bar->finish();
        $this->newLine(2);

        $this->info('Import selesai!');
        $this->info("User:      {$createdUser} created");
        $this->info("Mahasiswa: {$createdMahasiswa} created");
        $this->info("Peserta:   {$createdPeserta} created");
        $this->warn("Skipped:   " . ($skippedNoEmail + $skippedAdmin + $skippedExists + $skippedNoMhs) . " total");
        $this->line("  - No email: {$skippedNoEmail}");
        $this->line("  - Admin: {$skippedAdmin}");
        $this->line("  - Already exists: {$skippedExists}");
        $this->line("  - No mahasiswa data: {$skippedNoMhs}");
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
