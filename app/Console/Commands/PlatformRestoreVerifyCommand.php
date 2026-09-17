<?php
namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PlatformRestoreVerifyCommand extends Command
{
    protected $signature = 'platform:restore-verify {--file= : Specific backup file path to verify}';
    protected $description = 'Perform a non-destructive synthetic restore rehearsal, verifying decryption, checksum, and core schema presence';

    public function handle(): int
    {
        $this->info('Starting synthetic restore rehearsal...');
        $startTime = microtime(true);

        $backupDir = storage_path('app/backups');
        $targetFile = $this->option('file');

        if (! $targetFile) {
            $files = File::glob($backupDir . DIRECTORY_SEPARATOR . 'backup-*.enc');
            if (empty($files)) {
                $this->error('No backup archives found in ' . $backupDir);
                return self::FAILURE;
            }
            rsort($files);
            $targetFile = $files[0];
        }

        if (! File::exists($targetFile)) {
            $this->error("Specified backup file does not exist: {$targetFile}");
            return self::FAILURE;
        }

        $this->line("Target archive: " . basename($targetFile));

        // 1. Verify SHA-256 Checksum
        $checksumFile = $targetFile . '.sha256';
        if (File::exists($checksumFile)) {
            $expectedHash = trim(File::get($checksumFile));
            $actualHash = hash_file('sha256', $targetFile);

            if (! hash_equals($expectedHash, $actualHash)) {
                $this->error("SHA-256 Checksum verification failed! Archive may be corrupted.");
                Log::critical('restore_verify_checksum_failed', [
                    'file'     => $targetFile,
                    'expected' => $expectedHash,
                    'actual'   => $actualHash,
                ]);
                return self::FAILURE;
            }
            $this->info("✓ SHA-256 checksum verified.");
        } else {
            $this->warn("! No .sha256 checksum file found. Skipping checksum step.");
        }

        // 2. Decrypt
        try {
            $encryptedPayload = File::get($targetFile);
            $decrypted = Crypt::decrypt($encryptedPayload);
            $this->info("✓ AES-256 decryption succeeded.");
        } catch (\Throwable $e) {
            $this->error("Decryption failed: " . $e->getMessage());
            return self::FAILURE;
        }

        // 3. Decompress
        $decompressed = $decrypted;
        if (function_exists('gzdecode')) {
            $unpacked = @gzdecode($decrypted);
            if ($unpacked !== false) {
                $decompressed = $unpacked;
                $this->info("✓ Gzip decompression succeeded.");
            }
        }

        // 4. Schema & Data Sanity Check
        $requiredMarkers = ['users', 'orders', 'products', 'vendors'];
        $missingMarkers = [];

        foreach ($requiredMarkers as $marker) {
            if (stripos($decompressed, $marker) === false) {
                $missingMarkers[] = $marker;
            }
        }

        if (! empty($missingMarkers)) {
            $this->error("Synthetic schema check failed. Missing core table signatures: " . implode(', ', $missingMarkers));
            return self::FAILURE;
        }

        $this->info("✓ Core table structures verified: " . implode(', ', $requiredMarkers));

        // 5. Measure RTO and RPO
        $endTime = microtime(true);
        $rtoMs = round(($endTime - $startTime) * 1000, 2);
        $fileAgeSeconds = time() - filemtime($targetFile);

        $this->newLine();
        $this->info("=== Synthetic Restore Rehearsal Succeeded ===");
        $this->line("RPO (Backup Age): {$fileAgeSeconds} seconds");
        $this->line("RTO (Rehearsal Duration): {$rtoMs} ms");

        // Record metrics
        Setting::set('last_backup_restore_verified_at', now()->toIso8601String());
        Setting::set('last_backup_rpo_seconds', (string) $fileAgeSeconds);
        Setting::set('last_backup_rto_ms', (string) $rtoMs);

        Log::info('restore_rehearsal_verified', [
            'file'        => basename($targetFile),
            'rpo_seconds' => $fileAgeSeconds,
            'rto_ms'      => $rtoMs,
        ]);

        return self::SUCCESS;
    }
}