<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PlatformBackupCommand extends Command
{
    protected $signature = 'platform:backup {--retention=30 : Number of days to retain backups}';
    protected $description = 'Create an encrypted, compressed platform database backup with SHA-256 checksum';

    public function handle(): int
    {
        $this->info('Starting platform backup process...');

        $backupDir = storage_path('app/backups');
        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d-His');
        $encFileName = "backup-{$timestamp}.enc";
        $encFilePath = $backupDir . DIRECTORY_SEPARATOR . $encFileName;
        $checksumFilePath = $encFilePath . '.sha256';

        try {
            $dbContent = $this->dumpDatabase();
            if (empty($dbContent)) {
                $this->error('Database dump resulted in empty payload.');
                return self::FAILURE;
            }

            // Gzip compress if available
            $compressed = function_exists('gzencode') ? gzencode($dbContent, 9) : $dbContent;

            // Encrypt using Laravel application key (AES-256-CBC)
            $encrypted = Crypt::encrypt($compressed);

            File::put($encFilePath, $encrypted);

            // Generate SHA-256 checksum
            $sha256 = hash_file('sha256', $encFilePath);
            File::put($checksumFilePath, $sha256);

            $sizeKb = round(filesize($encFilePath) / 1024, 2);
            $this->info("Backup created successfully: {$encFileName} ({$sizeKb} KB)");
            $this->line("SHA-256: {$sha256}");

            // Cleanup old backups
            $retentionDays = (int) $this->option('retention');
            $this->cleanupOldBackups($backupDir, $retentionDays);

            Log::info('platform_backup_success', [
                'filename' => $encFileName,
                'size_kb'  => $sizeKb,
                'sha256'   => $sha256,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Platform backup failed: ' . $e->getMessage());
            Log::critical('platform_backup_failed', [
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }
    }

    private function dumpDatabase(): string
    {
        $connection = config('database.default');

        if ($connection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if ($dbPath && $dbPath !== ':memory:' && File::exists($dbPath)) {
                return File::get($dbPath);
            }
        }

        // Generic dump for MySQL/PostgreSQL/In-memory SQLite
        $tables = \Illuminate\Support\Facades\Schema::getTableListing();
        $dump = "-- BaskıYeri Database Dump {$connection}\n-- Generated: " . now()->toIso8601String() . "\n\n";

        foreach ($tables as $table) {
            $cleanTable = str_contains($table, '.') ? explode('.', $table)[1] : $table;
            try {
                $rows = DB::table($cleanTable)->get();
                $dump .= "-- Table: {$cleanTable} (" . $rows->count() . " rows)\n";
                $dump .= json_encode([
                    'table' => $cleanTable,
                    'rows'  => $rows->toArray(),
                ], JSON_UNESCAPED_UNICODE) . "\n\n";
            } catch (\Throwable $e) {
                // Skip inaccessible or virtual tables
            }
        }

        return $dump;
    }

    private function cleanupOldBackups(string $dir, int $days): void
    {
        $cutoff = now()->subDays($days)->getTimestamp();
        $files = File::files($dir);
        $deletedCount = 0;

        foreach ($files as $file) {
            if ($file->getMTime() < $cutoff) {
                File::delete($file->getPathname());
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->info("Cleaned up {$deletedCount} expired backup file(s).");
        }
    }
}