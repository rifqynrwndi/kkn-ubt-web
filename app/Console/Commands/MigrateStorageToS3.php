<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToS3 extends Command
{
    protected $signature = 'storage:migrate-to-s3';
    protected $description = 'Migrasi semua file dari local/public storage ke S3 (IDCloudHost)';

    public function handle(): int
    {
        $this->info('Memulai migrasi file ke S3...');

        if (env('FILESYSTEM_DISK') !== 's3' && !$this->confirm('FILESYSTEM_DISK belum di-set ke s3. Tetap lanjutkan?')) {
            return self::FAILURE;
        }

        $sourceDisk = 'public';
        $targetDisk = 's3';

        $this->testConnection($targetDisk);

        $allFiles = Storage::disk($sourceDisk)->allFiles();
        $count = count($allFiles);

        if ($count === 0) {
            $this->warn('Tidak ada file di storage public. Lewati.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$count} file. Mulai upload...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $errors = [];
        $migrated = 0;

        foreach ($allFiles as $filePath) {
            if ($filePath === '.gitignore') {
                $bar->advance();
                continue;
            }

            try {
                $contents = Storage::disk($sourceDisk)->get($filePath);
                $mimeType = Storage::disk($sourceDisk)->mimeType($filePath);
                Storage::disk($targetDisk)->put($filePath, $contents, [
                    'visibility' => 'public',
                    'ContentType' => $mimeType,
                ]);
                $migrated++;
            } catch (\Throwable $e) {
                $errors[] = "{$filePath}: {$e->getMessage()}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Migrasi selesai.");
        $this->info("  Berhasil: {$migrated}");
        $this->info("  Gagal: " . count($errors));

        if (!empty($errors)) {
            $this->error('Gagal:');
            foreach ($errors as $e) {
                $this->line("  - {$e}");
            }
        }

        return empty($errors) ? self::SUCCESS : self::FAILURE;
    }

    private function testConnection(string $disk): void
    {
        $this->info("Testing koneksi ke disk '{$disk}'...");
        try {
            Storage::disk($disk)->put('migration-test.txt', 'test-' . now());
            Storage::disk($disk)->delete('migration-test.txt');
            $this->info('  OK - Koneksi berhasil.');
        } catch (\Throwable $e) {
            $this->error("  GAGAL: {$e->getMessage()}");
            $this->error('  Pastikan credential S3 di .env sudah benar.');
            exit(1);
        }
    }
}
