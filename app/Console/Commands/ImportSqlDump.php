<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportSqlDump extends Command
{
    protected $signature = 'import:sql-dump
                            {file : Path to the .sql dump file}
                            {--connection=old_mysql : Target database connection}
                            {--force : Skip confirmation}';

    protected $description = 'Import SQL dump file into old_mysql database before running import:old-kkn';

    public function handle()
    {
        $file = $this->argument('file');
        $connection = $this->option('connection');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $size = round(filesize($file) / 1024 / 1024, 2);
        $this->info("SQL File: {$file} ({$size} MB)");
        $this->info("Target: {$connection} database");

        if (! $this->option('force') && ! $this->confirm('Import this SQL dump into ' . $connection . ' database? This may take a while.')) {
            return 0;
        }

        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port");
        $db   = config("database.connections.{$connection}.database");
        $user = config("database.connections.{$connection}.username");
        $pass = config("database.connections.{$connection}.password");

        $this->info("Importing into {$db} at {$host}:{$port}...");

        $command = sprintf(
            'mysql -h %s -P %s -u %s %s %s < %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            $pass ? '-p' . escapeshellarg($pass) : '',
            escapeshellarg($db),
            escapeshellarg($file)
        );

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $this->info('SQL dump imported successfully!');
            $this->info('Now run: php artisan import:old-kkn --gelombang=1');
        } else {
            $this->error('Import failed! Error output:');
            $this->line(implode("\n", $output));
            return 1;
        }

        return 0;
    }
}
