<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToS3 extends Command
{
    protected $signature = 'storage:migrate-to-s3';
    protected $description = 'Migrate all local storage files to S3';

    public function handle(): int
    {
        $localDisk = Storage::disk('local');
        $s3Disk = Storage::disk('s3');
        $directories = $localDisk->directories();
        $totalFiles = 0;
        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($directories as $dir) {
            $files = $localDisk->files($dir);
            $totalFiles += count($files);

            foreach ($files as $file) {
                if ($s3Disk->exists($file)) {
                    $skipped++;
                    continue;
                }

                try {
                    $contents = $localDisk->get($file);
                    $s3Disk->put($file, $contents);
                    $migrated++;
                    $this->line("  Migrated: {$file}");
                } catch (\Throwable $e) {
                    $failed++;
                    $this->error("  Failed: {$file} - {$e->getMessage()}");
                }
            }
        }

        $this->info("Done. Total: {$totalFiles}, Migrated: {$migrated}, Skipped: {$skipped}, Failed: {$failed}");
        return 0;
    }
}
