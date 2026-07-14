<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\TenantOrder;
use App\Services\ErrorLogReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminSystemHealthController extends Controller
{
    public function index(): View
    {
        // DB connection
        $dbOk = false;
        try {
            DB::select('SELECT 1');
            $dbOk = true;
        } catch (\Exception) {}

        // Cache
        $cacheOk = false;
        try {
            Cache::put('health_check', true, 10);
            $cacheOk = Cache::get('health_check') === true;
        } catch (\Exception) {}

        // Storage writable
        $storageWritable = is_writable(storage_path());

        // Error logs
        $errorLogReader = app(ErrorLogReader::class);
        $recentErrors = $errorLogReader->countSince(now()->subDay());
        $errorLogs = $errorLogReader->recent(50);

        // Queue
        $queueSize = DB::table('jobs')->count();

        // Tenants
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();

        // Pending orders
        $pendingOrders = TenantOrder::where('status', 'pending')->count();

        // SSL certs (check cert expiry for custom domains)
        $sslCerts = [];
        $tenantsWithDomains = Tenant::where('domain_status', 'verified')
            ->whereNotNull('custom_domain')
            ->get();
        foreach ($tenantsWithDomains as $t) {
            $certInfo = $this->checkCert($t->custom_domain);
            if ($certInfo) {
                $sslCerts[] = $certInfo;
            }
        }

        // Disk usage
        $diskFree = disk_free_space(storage_path());
        $diskTotal = disk_total_space(storage_path());
        $diskPercent = $diskTotal > 0 ? round((1 - $diskFree / $diskTotal) * 100) : 0;

        // PHP info
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();

        return view('system-health.index', compact(
            'dbOk', 'cacheOk', 'storageWritable',
            'recentErrors', 'errorLogs',
            'queueSize',
            'totalTenants', 'activeTenants', 'suspendedTenants',
            'pendingOrders', 'sslCerts',
            'diskFree', 'diskTotal', 'diskPercent',
            'phpVersion', 'laravelVersion',
        ));
    }

    public function clearCache(): RedirectResponse
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        AuditLog::log('cache_cleared', 'Application cache cleared', 'system');

        return back()->with('success', 'Cache cleared.');
    }

    public function runMigration(): RedirectResponse
    {
        Artisan::call('migrate', ['--force' => true]);

        AuditLog::log('migration_run', 'Database migrations run', 'system');

        return back()->with('success', 'Migrations completed.');
    }

    private function checkCert(string $domain): ?array
    {
        $cert = @openssl_get_publickey(file_get_contents("https://{$domain}/"));
        if (!$cert) return null;

        $details = @openssl_pkey_get_details($cert);
        if (!$details) return null;

        $context = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);
        $client = @stream_socket_client("ssl://{$domain}:443", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);

        if (!$client) return [
            'domain' => $domain,
            'valid' => false,
            'error' => $errstr ?: 'Connection failed',
        ];

        $params = stream_context_get_params($client);
        $certData = @openssl_x509_parse($params['options']['ssl']['peer_certificate']);
        fclose($client);

        if (!$certData) return [
            'domain' => $domain,
            'valid' => false,
            'error' => 'Could not parse certificate',
        ];

        $validFrom = $certData['validFrom_time_t'] ?? 0;
        $validTo = $certData['validTo_time_t'] ?? 0;
        $daysLeft = (int) (($validTo - time()) / 86400);

        return [
            'domain' => $domain,
            'valid' => $daysLeft > 0,
            'issuer' => $certData['issuer']['O'] ?? 'Unknown',
            'from' => date('M j, Y', $validFrom),
            'to' => date('M j, Y', $validTo),
            'days_left' => $daysLeft,
        ];
    }
}
