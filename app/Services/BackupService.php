<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PDO;
use ZipArchive;

class BackupService
{
    /**
     * Get target backup directory on Drive D.
     */
    public function getDestinationPath(): string
    {
        $path = Setting::get('backup_disk_path', 'D:\E-Library-Backups');
        if (!File::exists($path)) {
            try {
                File::makeDirectory($path, 0755, true, true);
            } catch (Exception $e) {
                Log::warning("Could not create directory at {$path}: " . $e->getMessage());
            }
        }
        return $path;
    }

    /**
     * Get storage drive statistics for Drive D.
     */
    public function getDriveStats(): array
    {
        $backupPath = $this->getDestinationPath();
        $driveLetter = strtoupper(substr($backupPath, 0, 2)); // e.g. "D:"
        
        $totalBytes = 0;
        $freeBytes = 0;

        try {
            $totalBytes = @disk_total_space($driveLetter . '\\') ?: 0;
            $freeBytes = @disk_free_space($driveLetter . '\\') ?: 0;
        } catch (Exception $e) {
            Log::warning("Disk stats error on {$driveLetter}: " . $e->getMessage());
        }

        $usedBytes = max(0, $totalBytes - $freeBytes);
        $usedPercentage = $totalBytes > 0 ? round(($usedBytes / $totalBytes) * 100, 1) : 0;
        $freePercentage = $totalBytes > 0 ? round(($freeBytes / $totalBytes) * 100, 1) : 0;

        // Calculate size of our backup folder
        $backupsFolderBytes = 0;
        if (File::exists($backupPath)) {
            foreach (File::allFiles($backupPath) as $file) {
                $backupsFolderBytes += $file->getSize();
            }
        }

        return [
            'drive_letter' => $driveLetter,
            'destination_path' => $backupPath,
            'total_bytes' => $totalBytes,
            'free_bytes' => $freeBytes,
            'used_bytes' => $usedBytes,
            'backups_bytes' => $backupsFolderBytes,
            'total_formatted' => $this->formatBytes($totalBytes),
            'free_formatted' => $this->formatBytes($freeBytes),
            'used_formatted' => $this->formatBytes($usedBytes),
            'backups_formatted' => $this->formatBytes($backupsFolderBytes),
            'used_percentage' => $usedPercentage,
            'free_percentage' => $freePercentage,
            'is_drive_d' => ($driveLetter === 'D:'),
            'is_accessible' => File::isWritable($backupPath) || File::exists($backupPath),
        ];
    }

    /**
     * Execute full backup: DB SQL dump + Public uploads, compressed into ZIP on Drive D.
     */
    public function createBackup(string $type = 'manual', ?User $user = null): Backup
    {
        $destPath = $this->getDestinationPath();
        if (!File::exists($destPath)) {
            File::makeDirectory($destPath, 0755, true, true);
        }

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $zipFilename = "elibrary_backup_{$timestamp}_{$type}.zip";
        $sqlFilename = "elibrary_backup_{$timestamp}_{$type}.sql";
        $fullZipPath = $destPath . DIRECTORY_SEPARATOR . $zipFilename;
        $tempSqlPath = $destPath . DIRECTORY_SEPARATOR . $sqlFilename;

        // 1. Initial backup entry
        $backup = Backup::create([
            'filename' => $zipFilename,
            'disk_path' => $fullZipPath,
            'file_size' => 0,
            'backup_type' => $type,
            'status' => 'running',
            'tables_count' => 0,
            'files_count' => 0,
            'created_by' => $user?->id,
        ]);

        try {
            // 2. Dump MySQL database to SQL file
            $dumpResult = $this->dumpDatabase($tempSqlPath);
            $tablesCount = $dumpResult['tables_count'];

            // 3. Count public storage upload files
            $uploadsPath = storage_path('app/public/uploads');
            $filesCount = 0;
            if (File::exists($uploadsPath)) {
                $filesCount = count(File::allFiles($uploadsPath));
            }

            // 4. Create ZIP archive
            $zip = new ZipArchive();
            if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception("Failed to create ZIP archive at {$fullZipPath}");
            }

            // Add the SQL database dump
            if (File::exists($tempSqlPath)) {
                $zip->addFile($tempSqlPath, 'database/' . $sqlFilename);
            }

            // Add public media files if enabled
            $includeFiles = Setting::get('backup_include_files', '1') == '1';
            if ($includeFiles && File::exists($uploadsPath)) {
                $this->addDirectoryToZip($zip, $uploadsPath, 'uploads');
            }

            // Add Manifest JSON
            $manifest = [
                'app_name' => config('app.name', 'E-Library'),
                'backup_type' => $type,
                'created_at' => Carbon::now()->toIso8601String(),
                'created_by' => $user ? ['id' => $user->id, 'name' => $user->name, 'email' => $user->email] : 'System Scheduler',
                'database_name' => config('database.connections.mysql.database', 'eLibrary'),
                'tables_backed_up' => $tablesCount,
                'files_backed_up' => $filesCount,
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_drive' => $destPath,
            ];
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $zip->close();

            // We can optionally keep the standalone .sql dump next to the zip for rapid restore,
            // or clean it up if desired. Keeping it makes database-only restore instant.

            $zipFileSize = file_exists($fullZipPath) ? filesize($fullZipPath) : 0;

            // 5. Update backup record
            $backup->update([
                'file_size' => $zipFileSize,
                'status' => 'success',
                'tables_count' => $tablesCount,
                'files_count' => $filesCount,
                'completed_at' => Carbon::now(),
            ]);

            // 6. Update global settings
            Setting::set('backup_last_run_at', Carbon::now()->format('Y-m-d H:i:s'), 'backup');
            Setting::set('backup_last_status', 'success', 'backup');
            Setting::set('backup_last_type', $type, 'backup');
            Setting::set('backup_last_filename', $zipFilename, 'backup');

            // 7. Audit activity log
            if ($user) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'backup_created',
                    'description' => __('Data successfully backed up to Server Drive D! (File: :file, Size: :size)', [
                        'file' => $zipFilename,
                        'size' => $this->formatBytes($zipFileSize),
                    ]),
                ]);
            }

            // 8. Retention cleanup
            $this->pruneOldBackups();

            return $backup;
        } catch (Exception $e) {
            Log::error("Backup failed: " . $e->getMessage(), ['exception' => $e]);

            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => Carbon::now(),
            ]);

            Setting::set('backup_last_status', 'failed', 'backup');

            throw $e;
        }
    }

    /**
     * Dump the MySQL database to an SQL file.
     * Tries mysqldump first; if unavailable, uses robust pure PHP PDO dumper.
     */
    public function dumpDatabase(string $outputSqlPath): array
    {
        $dbName = config('database.connections.mysql.database', 'eLibrary');
        $dbUser = config('database.connections.mysql.username', 'root');
        $dbPass = config('database.connections.mysql.password', '');
        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');

        $driver = DB::connection()->getDriverName();
        $mysqldumpPath = ($driver === 'mysql') ? $this->findMysqldumpBinary() : null;

        if ($mysqldumpPath) {
            try {
                $passwordArg = !empty($dbPass) ? "--password=" . escapeshellarg($dbPass) : "";
                $cmd = sprintf(
                    '"%s" --host=%s --port=%s --user=%s %s --default-character-set=utf8mb4 --single-transaction --quick --routines --triggers %s > "%s"',
                    $mysqldumpPath,
                    escapeshellarg($dbHost),
                    escapeshellarg($dbPort),
                    escapeshellarg($dbUser),
                    $passwordArg,
                    escapeshellarg($dbName),
                    $outputSqlPath
                );

                exec($cmd, $output, $returnVar);

                if ($returnVar === 0 && file_exists($outputSqlPath) && filesize($outputSqlPath) > 500) {
                    $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
                    return ['method' => 'mysqldump', 'tables_count' => count($tables)];
                }
            } catch (Exception $e) {
                Log::warning("mysqldump failed, falling back to PDO dumper: " . $e->getMessage());
            }
        }

        // Guaranteed Pure PHP PDO Dumper Fallback
        return $this->dumpDatabaseViaPdo($outputSqlPath);
    }

    /**
     * Pure PHP PDO SQL Dumper - Guaranteed to work in all PHP/Windows environments.
     */
    protected function dumpDatabaseViaPdo(string $outputSqlPath): array
    {
        $pdo = DB::connection()->getPdo();
        $driver = DB::connection()->getDriverName();
        $dbName = config('database.connections.mysql.database', 'eLibrary');

        $handle = fopen($outputSqlPath, 'w+');
        if (!$handle) {
            throw new Exception("Unable to open file for SQL dump: {$outputSqlPath}");
        }

        // Header comments and session configurations
        fwrite($handle, "-- ==========================================================\n");
        fwrite($handle, "-- E-Library Smart Management System - Complete Database Dump\n");
        fwrite($handle, "-- Database: `{$dbName}` (Driver: {$driver})\n");
        fwrite($handle, "-- Date: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- ==========================================================\n\n");

        if ($driver === 'sqlite') {
            $tableRecords = DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $tablesCount = count($tableRecords);

            foreach ($tableRecords as $tableObj) {
                $tableName = $tableObj->name;
                fwrite($handle, "\nDROP TABLE IF EXISTS \"{$tableName}\";\n");
                if (!empty($tableObj->sql)) {
                    fwrite($handle, $tableObj->sql . ";\n\n");
                }

                $stmt = $pdo->prepare("SELECT * FROM \"{$tableName}\"");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $values = [];
                    foreach ($row as $val) {
                        if (is_null($val)) {
                            $values[] = 'NULL';
                        } elseif (is_numeric($val) && !is_string($val)) {
                            $values[] = $val;
                        } else {
                            $values[] = $pdo->quote($val);
                        }
                    }
                    fwrite($handle, "INSERT INTO \"{$tableName}\" VALUES (" . implode(', ', $values) . ");\n");
                }
            }

            fclose($handle);
            return ['method' => 'pdo_sqlite', 'tables_count' => $tablesCount];
        }

        fwrite($handle, "SET NAMES utf8mb4;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($handle, "SET time_zone = '+00:00';\n\n");

        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        $keyName = "Tables_in_{$dbName}";

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$keyName ?? (array_values((array)$tableObj)[0] ?? null);
            if (!$tableName) {
                continue;
            }

            // Exclude temporary session/cache tables data if desired, but we keep full schema
            fwrite($handle, "\n-- --------------------------------------------------------\n");
            fwrite($handle, "-- Table structure for table `{$tableName}`\n");
            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

            // Create table statement
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createStatement = $createTable[0]->{'Create Table'} ?? null;
            if ($createStatement) {
                fwrite($handle, $createStatement . ";\n\n");
            }

            // Table rows / data
            fwrite($handle, "-- Dumping data for table `{$tableName}`\n");
            $stmt = $pdo->prepare("SELECT * FROM `{$tableName}`");
            $stmt->execute();

            $batchSize = 200;
            $rows = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $values = [];
                foreach ($row as $val) {
                    if (is_null($val)) {
                        $values[] = 'NULL';
                    } elseif (is_numeric($val) && !is_string($val)) {
                        $values[] = $val;
                    } else {
                        $values[] = $pdo->quote($val);
                    }
                }
                $rows[] = '(' . implode(', ', $values) . ')';

                if (count($rows) >= $batchSize) {
                    $insertSql = "INSERT INTO `{$tableName}` VALUES \n" . implode(",\n", $rows) . ";\n";
                    fwrite($handle, $insertSql);
                    $rows = [];
                }
            }

            if (!empty($rows)) {
                $insertSql = "INSERT INTO `{$tableName}` VALUES \n" . implode(",\n", $rows) . ";\n";
                fwrite($handle, $insertSql);
            }
        }

        fwrite($handle, "\nSET FOREIGN_KEY_CHECKS = 1;\n");
        fwrite($handle, "-- Dump completed on " . date('Y-m-d H:i:s') . "\n");

        fclose($handle);

        return ['method' => 'pdo', 'tables_count' => count($tables)];
    }

    /**
     * Restore database from a given Backup ID or path.
     */
    public function restoreBackup(int $backupId, ?User $user = null): bool
    {
        $backup = Backup::findOrFail($backupId);
        $destPath = $this->getDestinationPath();

        // 1. Locate SQL file: check if standalone sql exists or extract from zip
        $sqlPath = null;
        $tempExtractDir = null;

        // Check if standalone .sql exists with matching name
        $baseName = pathinfo($backup->filename, PATHINFO_FILENAME);
        $candidateSql = $destPath . DIRECTORY_SEPARATOR . $baseName . '.sql';

        if (file_exists($candidateSql)) {
            $sqlPath = $candidateSql;
        } elseif (file_exists($backup->disk_path)) {
            // Extract from ZIP
            $zip = new ZipArchive();
            if ($zip->open($backup->disk_path) === true) {
                $tempExtractDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'elibrary_restore_' . time();
                File::makeDirectory($tempExtractDir, 0755, true, true);
                $zip->extractTo($tempExtractDir);
                $zip->close();

                // Find extracted sql file
                $sqlFiles = File::glob($tempExtractDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . '*.sql');
                if (!empty($sqlFiles)) {
                    $sqlPath = $sqlFiles[0];
                }
            }
        }

        if (!$sqlPath || !file_exists($sqlPath)) {
            throw new Exception(__('Backup file not found on Server Drive D.'));
        }

        try {
            // Restore database using raw SQL execution
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $sqlContent = file_get_contents($sqlPath);
            DB::unprepared($sqlContent);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            if ($user) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'backup_restored',
                    'description' => __('Database successfully restored from backup! All data has been reverted.') . " ({$backup->filename})",
                ]);
            }

            return true;
        } finally {
            if ($tempExtractDir && File::exists($tempExtractDir)) {
                File::deleteDirectory($tempExtractDir);
            }
        }
    }

    /**
     * Delete a backup file and its record.
     */
    public function deleteBackup(int $backupId, ?User $user = null): bool
    {
        $backup = Backup::findOrFail($backupId);

        // Delete physical ZIP
        if (File::exists($backup->disk_path)) {
            File::delete($backup->disk_path);
        }

        // Delete accompanying standalone SQL if exists
        $baseName = pathinfo($backup->filename, PATHINFO_FILENAME);
        $sqlFile = dirname($backup->disk_path) . DIRECTORY_SEPARATOR . $baseName . '.sql';
        if (File::exists($sqlFile)) {
            File::delete($sqlFile);
        }

        $filename = $backup->filename;
        $backup->delete();

        if ($user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'backup_deleted',
                'description' => __('Backup file successfully deleted from Server Drive D.') . " ({$filename})",
            ]);
        }

        return true;
    }

    /**
     * Delete backups older than configured retention days.
     */
    public function pruneOldBackups(): int
    {
        $retentionDays = (int) Setting::get('backup_retention_days', 30);
        if ($retentionDays <= 0) {
            return 0;
        }

        $cutoffDate = Carbon::now()->subDays($retentionDays);
        $oldBackups = Backup::where('created_at', '<', $cutoffDate)->get();
        $prunedCount = 0;

        foreach ($oldBackups as $backup) {
            try {
                if (File::exists($backup->disk_path)) {
                    File::delete($backup->disk_path);
                }
                $baseName = pathinfo($backup->filename, PATHINFO_FILENAME);
                $sqlFile = dirname($backup->disk_path) . DIRECTORY_SEPARATOR . $baseName . '.sql';
                if (File::exists($sqlFile)) {
                    File::delete($sqlFile);
                }
                $backup->delete();
                $prunedCount++;
            } catch (Exception $e) {
                Log::warning("Could not prune backup {$backup->id}: " . $e->getMessage());
            }
        }

        return $prunedCount;
    }

    /**
     * Synchronize and discover existing backup zip files on Drive D not yet in the database.
     */
    public function syncExistingBackupsOnDisk(): void
    {
        $destPath = $this->getDestinationPath();
        if (!File::exists($destPath)) {
            return;
        }

        $zipFiles = File::glob($destPath . DIRECTORY_SEPARATOR . '*.zip');
        foreach ($zipFiles as $filePath) {
            $filename = basename($filePath);
            $exists = Backup::where('filename', $filename)->exists();
            if (!$exists) {
                $type = str_contains($filename, 'auto') ? 'auto' : 'manual';
                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                $fileTime = filemtime($filePath);

                Backup::create([
                    'filename' => $filename,
                    'disk_path' => $filePath,
                    'file_size' => $fileSize,
                    'backup_type' => $type,
                    'status' => 'success',
                    'tables_count' => 0,
                    'files_count' => 0,
                    'completed_at' => Carbon::createFromTimestamp($fileTime),
                    'created_at' => Carbon::createFromTimestamp($fileTime),
                ]);
            }
        }
    }

    /**
     * Check if today's auto-backup was missed and execute it safely.
     * Guarantees daily automatic backups even if Windows Task Scheduler is not running.
     */
    public function checkAndRunMissedAutoBackup(): ?Backup
    {
        $autoEnabled = Setting::get('backup_auto_enabled', '1');
        if ($autoEnabled != '1') {
            return null;
        }

        $lastRunAt = Setting::get('backup_last_run_at');
        $today = Carbon::now()->format('Y-m-d');

        // Check if an automatic backup already completed today
        if ($lastRunAt && str_starts_with($lastRunAt, $today)) {
            $lastStatus = Setting::get('backup_last_status');
            $lastType = Setting::get('backup_last_type');
            if ($lastStatus === 'success' && $lastType === 'auto') {
                return null;
            }
        }

        // Trigger automatic backup
        try {
            return $this->createBackup('auto');
        } catch (Exception $e) {
            Log::error("Automatic fallback backup error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Recursively add a folder to ZipArchive.
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $dirPath, string $zipSubDir): void
    {
        $files = File::allFiles($dirPath);
        foreach ($files as $file) {
            $relativePath = $zipSubDir . '/' . $file->getRelativePathname();
            $zip->addFile($file->getRealPath(), $relativePath);
        }
    }

    /**
     * Locate mysqldump executable in common system/WAMP/XAMPP paths.
     */
    protected function findMysqldumpBinary(): ?string
    {
        $candidates = [
            'C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe',
            'C:\xampp\mysql\bin\mysqldump.exe',
        ];

        // Search dynamic wamp versions
        $wampMysql = glob('C:\\wamp64\\bin\\mysql\\*\\bin\\mysqldump.exe');
        if (!empty($wampMysql)) {
            $candidates = array_merge($candidates, $wampMysql);
        }

        $wampMariadb = glob('C:\\wamp64\\bin\\mariadb\\*\\bin\\mysqldump.exe');
        if (!empty($wampMariadb)) {
            $candidates = array_merge($candidates, $wampMariadb);
        }

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Helper to format bytes into readable units.
     */
    public function formatBytes(int|float $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
