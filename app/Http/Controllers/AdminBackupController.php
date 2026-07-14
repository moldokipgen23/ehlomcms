<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\TenantBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use ZipArchive;

class AdminBackupController extends Controller
{
    public function index(): View
    {
        $backupDir = storage_path('app/db-backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backups = [];

        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/*.sql.gz');
            rsort($files);
            foreach ($files as $f) {
                $backups[] = [
                    'filename' => basename($f),
                    'size' => filesize($f),
                    'date' => filemtime($f),
                    'path' => $f,
                ];
            }
        }

        $tenantDir = storage_path('app/public');

        $tenants = Tenant::pluck('name', 'id')->toArray();

        $assetDirs = [];
        if (is_dir($tenantDir)) {
            foreach (glob($tenantDir . '/*', GLOB_ONLYDIR) as $dir) {
                $size = 0;
                foreach (glob($dir . '/**/*', GLOB_NOSORT) as $f) {
                    $size += is_file($f) ? filesize($f) : 0;
                }
                $assetDirs[] = [
                    'tenant_id' => basename($dir),
                    'tenant_name' => $tenants[basename($dir)] ?? 'Unknown',
                    'size' => $size,
                    'files' => iterator_count(new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS)),
                ];
            }
        }

        $tenantBackups = TenantBackup::with('tenant')->orderByDesc('created_at')->paginate(50);

        return view('backups.index', compact('backups', 'assetDirs', 'tenants', 'tenantBackups'));
    }

    public function run(): RedirectResponse
    {
        Artisan::call('backup:database');
        $output = Artisan::output();

        AuditLog::log('backup_run', 'Manual database backup triggered', 'system', null, ['output' => $output]);

        return back()->with('success', 'Database backup completed.');
    }

    public function download(string $filename): Response
    {
        $path = storage_path('app/db-backups/' . basename($filename));

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    public function restore(Request $request): RedirectResponse
    {
        $request->validate(['backup_file' => 'required|string']);

        $path = storage_path('app/db-backups/' . basename($request->backup_file));

        if (!file_exists($path)) {
            return back()->with('error', 'Backup file not found.');
        }

        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $command = 'gunzip -c ' . escapeshellarg($path)
            . ' | mysql -h ' . escapeshellarg($host)
            . ' -u ' . escapeshellarg($user)
            . ' -p' . escapeshellarg($pass)
            . ' ' . escapeshellarg($db) . ' 2>&1';

        exec($command, $output, $exitCode);

        AuditLog::log('backup_restored', 'Database restored from ' . basename($path), 'system', null, ['exit_code' => $exitCode]);

        if ($exitCode === 0) {
            return back()->with('success', 'Database restored successfully.');
        }

        return back()->with('error', 'Restore failed: ' . implode("\n", $output));
    }

    public function backupAssets(Tenant $tenant): RedirectResponse
    {
        $sourceDir = storage_path("app/public/{$tenant->id}");

        if (!is_dir($sourceDir)) {
            return back()->with('error', 'No assets directory for this tenant.');
        }

        $backupDir = storage_path('app/tenant-backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = "tenant_{$tenant->id}_assets_" . now()->format('Ymd_His') . '.zip';
        $zipPath = "{$backupDir}/{$filename}";

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return back()->with('error', 'Could not create zip archive.');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $totalSize = 0;
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $localPath = substr($file->getRealPath(), strlen($sourceDir) + 1);
                $zip->addFile($file->getRealPath(), $localPath);
                $totalSize += $file->getSize();
            }
        }

        $zip->close();

        TenantBackup::create([
            'tenant_id' => $tenant->id,
            'type' => 'assets',
            'filename' => $filename,
            'size' => $totalSize,
            'status' => 'completed',
        ]);

        AuditLog::log('tenant_assets_backed_up', "Assets backed up for {$tenant->name}", 'tenant', $tenant->id);

        return back()->with('success', "Assets backed up for {$tenant->name}.");
    }

    public function downloadTenantBackup(TenantBackup $tenantBackup): Response
    {
        $path = storage_path("app/tenant-backups/{$tenantBackup->filename}");

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $tenantBackup->filename);
    }

    public function restoreAssets(Tenant $tenant, TenantBackup $tenantBackup): RedirectResponse
    {
        $zipPath = storage_path("app/tenant-backups/{$tenantBackup->filename}");

        if (!file_exists($zipPath)) {
            return back()->with('error', 'Backup file not found.');
        }

        $targetDir = storage_path("app/public/{$tenant->id}");
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            return back()->with('error', 'Could not open zip archive.');
        }

        $zip->extractTo($targetDir);
        $zip->close();

        AuditLog::log('tenant_assets_restored', "Assets restored for {$tenant->name}", 'tenant', $tenant->id);

        return back()->with('success', "Assets restored for {$tenant->name}.");
    }

    public function tenantBackups(Tenant $tenant): View
    {
        return view('backups.tenant', compact('tenant'));
    }

    public function destroyTenantBackup(TenantBackup $tenantBackup): RedirectResponse
    {
        $path = storage_path("app/tenant-backups/{$tenantBackup->filename}");
        if (file_exists($path)) {
            unlink($path);
        }

        $tenantBackup->delete();

        return back()->with('success', 'Backup deleted.');
    }
}
