<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\BackupService;
use Exception;
use Illuminate\Console\Command;

class RunBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run {--type=manual : Type of backup (manual or auto)} {--force : Force backup even if disabled in settings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform complete E-Library database and file backup to Server Drive D';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService): int
    {
        $type = $this->option('type') ?: 'manual';
        $force = $this->option('force');

        $this->info("=================================================");
        $this->info("  E-Library Data Backup Engine (Server Drive D)  ");
        $this->info("=================================================");

        // Check if auto-backup is disabled
        if ($type === 'auto' && !$force) {
            $autoEnabled = Setting::get('backup_auto_enabled', '1');
            if ($autoEnabled != '1') {
                $this->warn("Automatic backup is disabled in System Settings. Skipping execution.");
                return Command::SUCCESS;
            }
        }

        $this->comment("Target Directory: " . $backupService->getDestinationPath());
        $this->comment("Backup Mode: " . strtoupper($type));
        $this->info("Initiating backup process...");

        try {
            $startTime = microtime(true);
            $backup = $backupService->createBackup($type);
            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info(" Backup successfully created!");
            $this->table(
                ['Attribute', 'Value'],
                [
                    ['File Name', $backup->filename],
                    ['Full Path', $backup->disk_path],
                    ['File Size', $backup->formatted_size],
                    ['DB Tables Count', $backup->tables_count],
                    ['Media Files Count', $backup->files_count],
                    ['Execution Time', "{$duration} seconds"],
                    ['Backup Type', $backup->backup_type],
                    ['Status', 'SUCCESS (ជោគជ័យ)'],
                ]
            );

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->newLine();
            $this->error(" Backup failed with error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
