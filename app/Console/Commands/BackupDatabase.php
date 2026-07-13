<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump the production database to a gzipped file and prune backups older than 14 days';

    /**
     * Kept outside the web root and outside git (storage/app is already
     * gitignored) so a compromised deploy or a `git clean` can never touch
     * backups, and so this works unchanged on any server this app runs on.
     */
    public function handle(): int
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $dir = storage_path('app/db-backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        $filename = $database . '_' . now()->format('Ymd_His') . '.sql.gz';
        $path = $dir . '/' . $filename;

        $dumpCommand = sprintf(
            'mysqldump -h %s -u %s %s %s | gzip > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            $password !== '' ? '-p' . escapeshellarg($password) : '',
            escapeshellarg($database),
            escapeshellarg($path)
        );

        $process = Process::fromShellCommandline($dumpCommand);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful() || !file_exists($path) || filesize($path) === 0) {
            $this->error('Backup failed.');
            Log::error('[backup:database] mysqldump failed: ' . $process->getErrorOutput());

            if (file_exists($path)) {
                unlink($path);
            }

            return self::FAILURE;
        }

        $sizeKb = round(filesize($path) / 1024, 1);
        $this->info("Backup created: {$filename} ({$sizeKb} KB)");
        Log::info("[backup:database] Backup created: {$filename} ({$sizeKb} KB)");

        $this->pruneOldBackups($dir);

        return self::SUCCESS;
    }

    private function pruneOldBackups(string $dir): void
    {
        $cutoff = now()->subDays(14)->timestamp;
        $removed = 0;

        foreach (glob($dir . '/*.sql.gz') as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $removed++;
            }
        }

        if ($removed > 0) {
            $this->info("Pruned {$removed} backup(s) older than 14 days.");
            Log::info("[backup:database] Pruned {$removed} backup(s) older than 14 days.");
        }
    }
}
