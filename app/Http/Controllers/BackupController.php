<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Models\Setting;
use App\Services\BackupService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Check if current user is Super Admin.
     */
    protected function authorizeSuperAdmin()
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, __('Access denied. Only Super Admin is authorized to access Backup Management.'));
        }
    }

    /**
     * Display the Backup & Recovery dashboard.
     */
    public function index(Request $request)
    {
        $this->authorizeSuperAdmin();

        // Auto-discover any untracked zip files on Drive D
        $this->backupService->syncExistingBackupsOnDisk();

        // Check and trigger missed auto-backup if today's backup has not run yet
        $this->backupService->checkAndRunMissedAutoBackup();

        $driveStats = $this->backupService->getDriveStats();

        $query = Backup::query()->latest('id');

        if ($request->filled('type') && in_array($request->type, ['manual', 'auto'])) {
            $query->where('backup_type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('filename', 'like', '%' . $request->search . '%');
        }

        $backups = $query->paginate(15)->withQueryString();

        $settings = [
            'disk_path' => Setting::get('backup_disk_path', 'D:\E-Library-Backups'),
            'auto_enabled' => Setting::get('backup_auto_enabled', '1') == '1',
            'auto_time' => Setting::get('backup_auto_time', '00:00'),
            'include_files' => Setting::get('backup_include_files', '1') == '1',
            'retention_days' => Setting::get('backup_retention_days', '30'),
            'last_run_at' => Setting::get('backup_last_run_at', ''),
            'last_status' => Setting::get('backup_last_status', ''),
            'last_type' => Setting::get('backup_last_type', ''),
            'last_filename' => Setting::get('backup_last_filename', ''),
        ];

        $totalBackupsCount = Backup::count();
        $successfulCount = Backup::successful()->count();
        $totalStorageBytes = Backup::successful()->sum('file_size');
        $totalStorageFormatted = $this->backupService->formatBytes($totalStorageBytes);

        return view('backups.index', compact(
            'driveStats',
            'backups',
            'settings',
            'totalBackupsCount',
            'successfulCount',
            'totalStorageFormatted'
        ));
    }

    /**
     * Trigger an instant manual backup to Drive D.
     */
    public function run(Request $request)
    {
        $this->authorizeSuperAdmin();

        try {
            $user = Auth::user();
            $backup = $this->backupService->createBackup('manual', $user);

            $message = __('Data successfully backed up to Server Drive D! (File: :file, Size: :size)', [
                'file' => $backup->filename,
                'size' => $backup->formatted_size,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'backup' => [
                        'id' => $backup->id,
                        'filename' => $backup->filename,
                        'size' => $backup->formatted_size,
                        'type' => $backup->backup_type,
                        'created_at' => $backup->created_at->format('d/m/Y H:i:s'),
                    ],
                ]);
            }

            return redirect()->route('backups.index')->with('success', $message);
        } catch (Exception $e) {
            $errorMessage = __('Backup failed: :error', ['error' => $e->getMessage()]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 500);
            }

            return redirect()->route('backups.index')->with('error', $errorMessage);
        }
    }

    /**
     * Download a backup file from Drive D.
     */
    public function download(int $id)
    {
        $this->authorizeSuperAdmin();

        $backup = Backup::findOrFail($id);

        if (!file_exists($backup->disk_path)) {
            return redirect()->route('backups.index')->with('error', __('Backup file not found on Server Drive D.'));
        }

        return response()->download($backup->disk_path, $backup->filename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Restore database from backup.
     */
    public function restore(Request $request, int $id)
    {
        $this->authorizeSuperAdmin();

        try {
            $user = Auth::user();
            $this->backupService->restoreBackup($id, $user);

            return redirect()->route('backups.index')->with('success', __('Database successfully restored from backup! All data has been reverted.'));
        } catch (Exception $e) {
            return redirect()->route('backups.index')->with('error', __('Failed to restore database: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Delete a backup file from Drive D and database record.
     */
    public function destroy(int $id)
    {
        $this->authorizeSuperAdmin();

        try {
            $user = Auth::user();
            $this->backupService->deleteBackup($id, $user);

            return redirect()->route('backups.index')->with('success', __('Backup file successfully deleted from Server Drive D.'));
        } catch (Exception $e) {
            return redirect()->route('backups.index')->with('error', __('Could not delete backup file: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Update backup configuration settings.
     */
    public function updateSettings(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'backup_disk_path' => ['required', 'string'],
            'backup_auto_enabled' => ['nullable', 'boolean'],
            'backup_include_files' => ['nullable', 'boolean'],
            'backup_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        Setting::set('backup_disk_path', $validated['backup_disk_path'], 'backup');
        Setting::set('backup_auto_enabled', $request->has('backup_auto_enabled') ? '1' : '0', 'backup');
        Setting::set('backup_include_files', $request->has('backup_include_files') ? '1' : '0', 'backup');
        Setting::set('backup_retention_days', (string) $validated['backup_retention_days'], 'backup');

        return redirect()->route('backups.index')->with('success', __('Backup configuration saved successfully!'));
    }
}
